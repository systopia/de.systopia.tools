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
 * Starting queue/runner.
 */
class CRM_Tools_Queue_Runner_GroupContactAddingRunnerStart
{
    /** @var string $title Will be set as title by the runner. */
    public $title;

    public function __construct(int $batchSize)
    {
        $this->title = E::ts('Adding contacts. Batch size is %1.', [1 => $batchSize]);
    }

    public function run(): bool
    {
        return true;
    }
}
