<?php
/*-------------------------------------------------------+
| SYSTOPIA Tools Collection                              |
| Copyright (C) 2019 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Tools_ExtensionUtil as E;

/**
 * Class CRM_Tools_ContactUpdater
 *
 * Implements a queue item for a runner
 *
 * Tools overview page
 */
class CRM_Tools_GreetingsUpdater {

  /**
   * Update sort name and display name  of the given contacts
   * @param $contact_ids array contact_ids
   */
  public static function updateContacts($contact_ids) {
    $contact_id_list = implode(',', $contact_ids);
    if (empty($contact_id_list)) return;

    // get all greetings
    $greeting_options = [];
    foreach (['email_greeting', 'postal_greeting', 'addressee'] as $greeting) {
      $greeting_options[$greeting] = self::getGreetings($greeting);
    }

    // get all tokens
    $tokens = [];
    CRM_Utils_Hook::tokens($tokens);
    $tokenFields = [];
    foreach ($tokens as $catTokens) {
      foreach ($catTokens as $token => $label) {
        $tokenFields[] = $token;
      }
    }

    // load all contacts
    $contacts = civicrm_api3('Contact', 'get', [
        'id'           => ['IN' => $contact_ids],
        'option.limit' => 0,
        'return'       => 'id,communication_style_id,communication_style,display_name,sort_name,email,first_name,last_name,nick_name,individual_suffix,individual_prefix,prefix_id,suffix_id,formal_title,organization_name,houshold_name,contact_type,contact_sub_type',
    ]);

    // load contact greetings (via SQL)
    $contact_greetings = [];
    $greeting_fields = ['email_greeting_id', 'email_greeting_display', 'email_greeting_custom', 'postal_greeting_id', 'postal_greeting_display', 'postal_greeting_custom', 'addressee_id', 'addressee_display', 'addressee_custom'];
    $greeting_field_list = implode(', ', $greeting_fields);
    $greeting_query = CRM_Core_DAO::executeQuery("
        SELECT id AS contact_id, {$greeting_field_list}
        FROM civicrm_contact WHERE id IN ({$contact_id_list});");
    while ($greeting_query->fetch()) {
      $values = [];
      foreach ($greeting_fields as $greeting_field) {
        $values[$greeting_field] = $greeting_query->$greeting_field;
      }
      $contact_greetings[$greeting_query->contact_id] = $values;
    }

    // now: process all contacts
    foreach ($contacts['values'] as $contact) {
      $contact_id = $contact['id'];
      $changes = [];

      foreach (['email_greeting', 'postal_greeting', 'addressee'] as $greeting) {
        // get the current value
        $old_greeting = $contact_greetings[$contact_id]["{$greeting}_display"];

        // get the calculation formula
        $current_formula_id = $contact_greetings[$contact_id]["{$greeting}_id"];
        if (empty($current_formula_id)) {
          // find the default
          foreach ($greeting_options[$greeting][$contact['contact_type']] as $option) {
            if (!empty($option['is_default'])) {
              $current_formula_id = $option['value'];
              break;
            }
          }
          if (empty($current_formula_id)) {
            // nothing set, no default: let's skip this
            continue;
          }
        }
        $current_formula = $greeting_options[$greeting][$contact['contact_type']][$current_formula_id];
        if ($current_formula['name'] == 'Customized') {
          $current_formula = $contact_greetings[$contact_id]["{$greeting}_custom"];
        } else {
          $current_formula = $current_formula['label'];
        }

        // calculate the current value
        CRM_Utils_Token::replaceGreetingTokens($current_formula, $contact, $contact_id, 'CRM_Contact_BAO_Contact', TRUE);
        $new_greeting = CRM_Core_Smarty::singleton()->fetch("string:$current_formula");
        $new_greeting = substr(trim($new_greeting), 0, 255);

        // if changed, add to list:
        if ($new_greeting != $old_greeting) {
          $changes[$greeting] = $new_greeting;
        }
      }

      // if there is changes, update via SQL
      if ($changes) {
        $contact_bao = new CRM_Contact_BAO_Contact();
        $contact_bao->id = $contact_id;
        foreach ($changes as $greeting => $display_value) {
          $attribute = "{$greeting}_display";
          $contact_bao->$attribute = $display_value;
        }
        $contact_bao->save();
        $contact_bao->free();
        //Civi::log()->debug("updated greeting for [{$contact['id']}]");
      }
    }
  }

  /**
   * Get all greeting options from the given group
   *
   * @param $option_group_name
   *
   * @return array all options
   */
  protected static function getGreetings($option_group_name) {
    $options = [];
    $query = civicrm_api3('OptionValue', 'get', [
        'option_group_id' => $option_group_name,
        'option.limit'    => 0,
        // return
    ]);
    foreach ($query['values'] as $option) {
      switch ($option['filter']) {
        default:
        case 0:
          $options['Individual'][$option['value']]   = $option;
          $options['Household'][$option['value']]    = $option;
          $options['Organization'][$option['value']] = $option;
          break;
        case 1:
          $options['Individual'][$option['value']] = $option;
          break;
        case 2:
          $options['Household'][$option['value']] = $option;
          break;
        case 3:
          $options['Organization'][$option['value']] = $option;
          break;
      }
    }
    return $options;
  }

  // job size for runner
  const JOB_SIZE = 250;

  /**
   * Use CRM_Queue_Runner to update every non-deleted contact in the DB
   */
  public static function launchDBRunner() {
    // create a queue
    $queue = CRM_Queue_Service::singleton()->create(array(
        'type'  => 'Sql',
        'name'  => 'systopia_contact_update',
        'reset' => TRUE,
    ));

    // count contacts to do
    $contact_count = CRM_Core_DAO::singleValueQuery("SELECT COUNT(*) FROM civicrm_contact WHERE is_deleted IS NULL OR is_deleted = 0");

    // create runner items
    $offset = 0;
    while ($contact_count > 0) {
      $queue->createItem(new CRM_Tools_GreetingsUpdater($offset, self::JOB_SIZE));
      $contact_count -= self::JOB_SIZE;
      $offset        += self::JOB_SIZE;
    }

    // create a runner and launch it
    $runner = new CRM_Queue_Runner(array(
        'title'     => E::ts("Updating All Contacts"),
        'queue'     => $queue,
        'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
        'onEndUrl'  => CRM_Utils_System::url('civicrm/systopia/tools'),
    ));
    $runner->runAllViaWeb(); // does not return
  }


  /******************************************************************
   **                      Queue Item Instance                     **
  /******************************************************************/

  public $title      = NULL;
  protected $offset  = NULL;
  protected $limit   = NULL;


  protected function __construct($offset, $limit) {
    $this->offset = (int) $offset;
    $this->limit  = (int) $limit;
    $this->title  = E::ts("Updating Contacts %1-%2", [1 => $offset, 2 => ($limit + $offset)]);
  }

  public function run($context) {
    // get contact IDs
    $contact_ids = [];
    $contact_query = CRM_Core_DAO::executeQuery("SELECT id AS contact_id FROM civicrm_contact WHERE is_deleted IS NULL OR is_deleted = 0 LIMIT {$this->limit} OFFSET {$this->offset}");
    while ($contact_query->fetch()) {
      $contact_ids[] = $contact_query->contact_id;
    }
    self::updateContacts($contact_ids);
    return TRUE;
  }

}
