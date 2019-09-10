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
 * Update display and sort name for the given contacts
 * @param $specs array API specs
 */
function _civicrm_api3_contact_update_names_spec(&$specs) {
  $specs['contact_ids'] = array(
      'name'         => 'contact_ids',
      'api.required' => 1,
      'type'         => CRM_Utils_Type::T_STRING,
      'title'        => 'Contact ID list',
      'description'  => 'Single contact ID or comma-separated list',
  );
}

/**
 * Update display and sort name for the given contacts
 *
 * @param $params array API parameters
 * @return array result
 */
function civicrm_api3_contact_update_names($params) {
  $contact_ids = $params['contact_ids'];
  if (is_array($params['contact_ids'])) {
    $contact_ids = $params['contact_ids'];
  } elseif (is_numeric($params['contact_ids'])) {
    $contact_ids = [(int) $params['contact_ids']];
  } else {
    $contact_ids = explode(',', $params['contact_ids']);
  }
  if (!empty($contact_ids)) {
    CRM_Tools_GreetingsUpdater::updateContacts($contact_ids);
    return civicrm_api3_create_success($contact_ids);
  }
}
