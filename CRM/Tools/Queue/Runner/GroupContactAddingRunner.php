<?php

/*-------------------------------------------------------+
| SYSTOPIA Tools Collection                              |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: B. Zschiedrich (zschiedrich@systopia.de)       |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*/

use CRM_Tools_ExtensionUtil as E;

/**
 * The queue/runner.
 */
class CRM_Tools_Queue_Runner_GroupContactAddingRunner
{
    /** @var string $title Will be set as title by the runner. */
    public $title;

    /** @var string[] $contactIds The contacts that shall be added to the group. */
    protected $contactIds;

    /** @var string $groupId The group the contacts shall be added to. */
    protected $groupId;

    /**
     * @param int $offset The contacts offset for this runner instance.
     * @param int $count The number of contacts this runner instance shall work on.
     */
    public function __construct(array $contactIds, string $groupId, int $offset, int $count)
    {
        $this->contactIds = $contactIds;
        $this->groupId = $groupId;

        $this->title = E::ts('Adding contact %1 to %2.', [1 => $offset + 1, 2 => $offset + $count]);
    }

    public function run(): bool
    {
        $result = civicrm_api3(
            'GroupContact',
            'create',
            [
                'group_id' => $this->groupId,
                'contact_id' => $this->contactIds,
            ]
        );

        return $result['is_error'] == 0;
    }
}
