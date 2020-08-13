<?php

/*-------------------------------------------------------+
| SYSTOPIA Tools Collection                              |
| Copyright (C) 2020 SYSTOPIA                            |
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
 * Add a lot of contacts to a group.
 */
class CRM_Tools_Form_Task_GroupAddManyContacts extends CRM_Contact_Form_Task
{
    private const TARGET_GROUP_ELEMENT_NAME = 'target_group';

    public function buildQuickForm()
    {
        parent::buildQuickForm();

        $this->addEntityRef(
            self::TARGET_GROUP_ELEMENT_NAME,
            E::ts('Target group'),
            [
                'entity' => 'Group',
                'api' => [
                    'params' => []
                ]
            ],
            true
        );
    }

    public function postProcess()
    {
        parent::postProcess();

        $contactIds = $this->_contactIds;

        $values = $this->exportValues(null, true);
        $targetGroupId = $values[self::TARGET_GROUP_ELEMENT_NAME];

        // Forward to the group:
        $targetUrl = CRM_Utils_System::url('civicrm/group/search', 'reset=1&force=1&gid=' . $targetGroupId);

        CRM_Tools_Queue_Runner_GroupContactAddingLauncher::launchRunner($contactIds, $targetGroupId, $targetUrl);
    }
}
