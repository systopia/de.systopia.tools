{*-------------------------------------------------------+
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
+-------------------------------------------------------*}


<div class="crm-section">
  <div class="label">{$form.api_entity.label}</div>
  <div class="content">{$form.api_entity.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.api_action.label}</div>
  <div class="content">{$form.api_action.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.api_rollback.label}</div>
  <div class="content">{$form.api_rollback.html}</div>
  <div class="clear"></div>
</div>

<div class="crm-section">
  <div class="label">{$form.api_params.label}</div>
  <div class="content">{$form.api_params.html}</div>
  <div class="clear"></div>
</div>


<div class="crm-submit-buttons">
{include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

<h2>Output</h2>

{if $api_result}
  <h3>Result</h3>
  <div class="crm-section">
    <pre><code>{$api_result}</code></pre>
  </div>
{/if}
{if $api_error}
  <h3>Error</h3>
  <div class="crm-section">
    <pre><code>{$api_error}</code></pre>
  </div>
{/if}