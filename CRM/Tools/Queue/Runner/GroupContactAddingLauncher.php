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
 * The launcher for a queue/runner generating documents.
 */
abstract class CRM_Tools_Queue_Runner_GroupContactAddingLauncher
{
    private const BATCH_SIZE = 50;

    /**
     * Launch the runner.
     * @param string[] $contactIds The contacts that shall be added to the group.
     * @param string $groupId The group the contacts shall be added to.
     * @param string $targetUrl The URL we shall redirect after the runner has been finished.
     */
    public static function launchRunner(array $contactIds, string $groupId, string $targetUrl): void
    {
        $queue = CRM_Queue_Service::singleton()->create(
            [
                'type' => 'Sql',
                'name' => 'tools_group_contact_adding_' . CRM_Core_Session::singleton()->getLoggedInContactID(),
                'reset' => true,
            ]
        );

        $queue->createItem(new CRM_Tools_Queue_Runner_GroupContactAddingRunnerStart(self::BATCH_SIZE));

        $dataCount = count($contactIds);

        for ($offset = 0; $offset < $dataCount; $offset += self::BATCH_SIZE) {
            $batchedContactIds = array_slice($contactIds, $offset, self::BATCH_SIZE);

            $queue->createItem(
                new CRM_Tools_Queue_Runner_GroupContactAddingRunner(
                    $batchedContactIds,
                    $groupId,
                    $offset,
                    self::BATCH_SIZE
                )
            );
        }

        $runner = new CRM_Queue_Runner(
            [
                'title' => E::ts('Adding contacts.'),
                'queue' => $queue,
                'errorMode' => CRM_Queue_Runner::ERROR_ABORT,
                'onEndUrl' => $targetUrl,
            ]
        );

        $runner->runAllViaWeb();
    }
}
