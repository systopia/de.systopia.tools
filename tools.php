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

require_once 'tools.civix.php';
use CRM_Tools_ExtensionUtil as E;

function tools_civicrm_searchTasks($objectType, &$tasks)
{
    if ($objectType == 'contact') {
        $tasks[] = [
            'title' => E::ts('Group - add many contacts'),
            'class' => 'CRM_Tools_Form_Task_GroupAddManyContacts',
            'result' => false
        ];
    }
}

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function tools_civicrm_navigationMenu(&$menu)
{
  _tools_civix_insert_navigation_menu($menu, 'Administer/System Settings', array(
      'label'      => E::ts('SYSTOPIA Tools'),
      'name'       => 'systopia_tools',
      'url'        => 'civicrm/systopia/tools',
      'permission' => 'administer CiviCRM',
      'operator'   => 'OR',
      'separator'  => 0,
  ));
  _tools_civix_navigationMenu($menu);
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function tools_civicrm_config(&$config) {
  _tools_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function tools_civicrm_install() {
  _tools_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function tools_civicrm_enable() {
  _tools_civix_civicrm_enable();
}
