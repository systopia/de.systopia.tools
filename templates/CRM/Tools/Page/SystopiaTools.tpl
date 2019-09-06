{*-------------------------------------------------------+
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
+-------------------------------------------------------*}

<div>{ts domain="de.systopia.tools"}A Collection of useful tools.{/ts}</div>

<h2>{ts domain="de.systopia.tools"}Contact Tools{/ts}</h2><br/>

<div class="systopia-tool">
    <h3>{ts domain="de.systopia.tools"}Greetings Updater{/ts}</h3>
    <div><span>{ts domain="de.systopia.tools"}This will re-calculate <strong>email and postal greeting and the addressee</strong> of all contacts in the database. You would want to do this after changing any of the formulas, as this doesn't automatically update each contact.{/ts}</span></div>
    <div><a id="new" class="button" href="{crmURL p="civicrm/systopia/tools" q="run=greetings_updater"}"><span><i class="crm-i fa-refresh"></i>{ts domain="de.systopia.tools"}Update All Contacts{/ts}</span></a></div>
    <div class="clear"></div>
</div>
<br/>
<div class="systopia-tool">
    <h3>{ts domain="de.systopia.tools"}Name Updater{/ts}</h3>
    <div><span>{ts domain="de.systopia.tools"}This will re-calculate <strong>sort name and display name</strong> of all contacts in the database. You would want to do this after changing the formula for sort name or display name, as this doesn't automatically update each contact.{/ts}</span></div>
    <div><a id="new" class="button" href="{crmURL p="civicrm/systopia/tools" q="run=name_updater"}"><span><i class="crm-i fa-refresh"></i>{ts domain="de.systopia.tools"}Update All Contacts{/ts}</span></a></div>
    <div class="clear"></div>
</div>
