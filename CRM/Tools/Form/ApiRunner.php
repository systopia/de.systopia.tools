<?php
/*-------------------------------------------------------+
| SYSTOPIA Tools Collection                              |
| Copyright (C) 2020 SYSTOPIA                            |
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
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Tools_Form_ApiRunner extends CRM_Core_Form
{
    public function buildQuickForm()
    {
        // add API specs
        $this->add(
            'text',
            'api_entity',
            E::ts('Entity'),
            ['class' => 'tiny'],
            true
        );

        $this->add(
            'text',
            'api_action',
            E::ts('Action'),
            ['class' => 'tiny'],
            true
        );

        $this->add(
            'checkbox',
            'api_rollback',
            E::ts('Rollback')
        );

        $this->add(
            'textarea',
            'api_params',
            E::ts('Parameters'),
            ['class' => 'huge40'],
            true
        );

        $this->addButtons(
            [
                [
                    'type'      => 'submit',
                    'name'      => E::ts('Run'),
                    'isDefault' => true,
                ],
            ]
        );

        parent::buildQuickForm();
    }

    /**
     * Run the specified API command
     */
    public function postProcess()
    {
        $values = $this->exportValues();

        // start transaction
        $transaction = new CRM_Core_Transaction();

        // run API
        try {
            $result = civicrm_api3(
                $values['api_entity'],
                $values['api_action'],
                $this->extractParameters($values['api_params'])
            );
            $error  = '';
        } catch (CiviCRM_API3_Exception $ex) {
            $result = '';
            $error  = $ex->getMessage();
            $error  .= '<br/><br/>';
            $error  .= $ex->getTraceAsString();
        }

        $this->assign('api_result', json_encode($result, JSON_PRETTY_PRINT));
        $this->assign('api_error', $error);

        // close transaction
        if (empty($values['api_rollback'])) {
            $transaction->commit();
        } else {
            $transaction->rollback();
        }

        parent::postProcess();
    }

    /**
     * Tries to extract the data from the
     *
     * @param string $data
     *      raw parameter data
     *
     * @return array
     *      extracted parameters
     *
     * @throws Exception
     *      if parameters couldn't be parsed
     */
    protected function extractParameters($data)
    {
        $return_data = null;

        // first: try JSON
        $json_data = json_decode($data, true);
        if ($json_data && is_array($json_data)) {
            $return_data = $json_data;
        }

        // todo: more parsers?

        if ($return_data) {
            // clean some parameters
            unset($return_data['version']);
            unset($return_data['check_permissions']);

            // and return
            return $return_data;

        } else {
            // nothing found
            throw new Exception(E::ts("Couldn't parse parameter data"));
        }
    }
}
