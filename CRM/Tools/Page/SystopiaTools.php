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
 * Class CRM_Tools_Page_SystopiaTools
 *
 * Tools overview page
 */
class CRM_Tools_Page_SystopiaTools extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('SYSTOPIA TOOLS'));

    // run tools if requested
    $run_tool = CRM_Utils_Request::retrieve('run', 'String');
    if ($run_tool) {
      $this->runTool($run_tool);
    }

    parent::run();
  }

  /**
   * Switch to run the different tools
   */
  protected function runTool($tool_name) {
    switch ($tool_name) {
      case 'contact_updater':
        // TODO
        break;

      default:
        CRM_Core_Session::setStatus(E::ts("Unknown tool '%1'.", [1 => $tool_name]), E::ts("Error"), 'error');
    }
    CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/systopia/tools'));
  }
}
