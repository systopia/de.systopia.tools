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
class CRM_Tools_NameUpdater {

  /**
   * Update sort name and display name  of the given contacts
   * @param $contact_ids array contact_ids
   */
  public static function updateContacts($contact_ids) {
    if (empty($contact_ids)) return;

    // get the formats
    $sort_name_format    = Civi::settings()->get('sort_name_format');
    $display_name_format = Civi::settings()->get('display_name_format');

    // get the tokens
    $tokens = [];
    CRM_Utils_Hook::tokens($tokens);
    $tokenFields = [];
    foreach ($tokens as $catTokens) {
      foreach ($catTokens as $token => $label) {
        $tokenFields[] = $token;
      }
    }

    // load contacts
    $contacts = civicrm_api3('Contact', 'get', [
        'id'           => ['IN' => $contact_ids],
        'option.limit' => 0,
        'return'       => 'id,display_name,sort_name,email,first_name,last_name,nick_name,individual_suffix,individual_prefix,prefix_id,suffix_id,formal_title,organization_name,houshold_name,contact_type,contact_sub_type',
    ]);

    foreach ($contacts['values'] as $contact) {
      $changes = [];

      // calculate sort_name and display_name
      switch ($contact['contact_type']) {
        case 'Household':
          $name = $contact['household_name'];
          if (empty($name) && !empty($contact['email'])) {
            $name = $contact['email'];
          }
          $changes['display_name'] = substr(trim($name), 0, 128);
          $changes['sort_name']    = $changes['display_name'];
          break;

        case 'Organization':
          $name = $contact['organization_name'];
          if (empty($name) && !empty($contact['email'])) {
            $name = $contact['email'];
          }
          $changes['display_name'] = substr(trim($name), 0, 128);
          $changes['sort_name']    = $changes['display_name'];
          break;

        default:
        case 'Individual':
          //build the sort name.
          $sortName = CRM_Utils_Address::format($contact, $sort_name_format,  FALSE, FALSE, $tokenFields);
          if (empty($sortName) && !empty($contact['email'])) {
            $sortName = $contact['email'];
          }
          $changes['sort_name'] = substr(trim($sortName), 0, 128);

          //build the display name.
          $format = Civi::settings()->get('display_name_format');
          $displayName = CRM_Utils_Address::format($contact, $display_name_format, FALSE, FALSE, $tokenFields);
          $displayName = substr(trim($displayName), 0, 128);
          if (empty($displayName) && !empty($contact['email'])) {
            $displayName = $contact['email'];
          }
          $changes['display_name'] = substr(trim($displayName), 0, 128);
          break;
      }

      // see if we need an update here
      foreach ($changes as $attribute => $value) {
        if ($value != $contact[$attribute]) {
          // we need to store the changes
          $contact_bao = new CRM_Contact_BAO_Contact();
          $contact_bao->id = $contact['id'];
          foreach ($changes as $changed_attribute => $changed_value) {
            $contact_bao->$changed_attribute = $changed_value;
          }
          $contact_bao->save();
          $contact_bao->free();
          //Civi::log()->debug("updated {$contact['id']}");
          break;
        }
      }
    }
  }

  // job size for runner
  const JOB_SIZE = 50;

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
      $queue->createItem(new CRM_Tools_NameUpdater($offset, self::JOB_SIZE));
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
