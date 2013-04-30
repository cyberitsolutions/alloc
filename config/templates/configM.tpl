{page::header()}
{page::toolbar()}

{page::side_by_side_links(array("basic"=>"Basic Setup"
                               ,"company_info"=>"Company Info"
                               ,"finance"=>"Finance"
                               ,"time_sheets"=>"Time Sheets"
                               ,"email_gateway"=>"Email Gateway"
                               ,"email_subject"=>"Email Subject Lines"
                               ,"rss"=>"RSS Feed"
                               ,"misc"=>"Miscellaneous")
                          ,$url_alloc_config
                          ,true)}

<div id="basic">
<form action="{$url_alloc_config}" method="post">
<table class="box">

  <tr>
    <th colspan="3">Basic Setup</th>
  </tr>

  <tr>
    <td width="20%"><nobr>allocPSA Tabs</nobr></td>
    <td><select name="allocTabs[]" multiple>{$allocTabsOptions}</select></td> 
    <td width="1%">{page::help("config_allocTabs")}</td>
  </tr>

  <tr>
    <td width="20%"><nobr>allocPSA Base URL</nobr></td>
    <td><input type="text" size="70" value="{$allocURL}" name="allocURL"></td> 
    <td width="1%">{page::help("config_allocURL")}</td>
  </tr>
 
  <tr>
    <td width="20%"><nobr>Time Zone</nobr></td>
    <td><select name="allocTimezone">{page::select_options(get_timezone_array(),$allocTimezone)}</select></td>
    <td width="1%">{page::help("config_allocTimezone")}</td>
  </tr>
  
  <tr>
    <td width="20%"><nobr>Calendar 1st Day</nobr></td>
    <td><select name="calendarFirstDay">{$calendarFirstDayOptions}</select></td>
    <td width="1%">{page::help("config_calendarFirstDay")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Adminstrator Email Address</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailAdmin}" name="allocEmailAdmin"></td> 
    <td width="1%">{page::help("config_allocEmailAdmin")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Email Addressing Method</nobr></td>
    <td>
      <label for="eam_to">Use "To:"</label><input id="eam_to" type="radio" name="allocEmailAddressMethod" value="to"{$allocEmailAddressMethod == "to" and print " checked"}>&nbsp;&nbsp;&nbsp;&nbsp;
      <label for="eam_bcc">Use "Bcc:"</label><input id="eam_bcc" type="radio" name="allocEmailAddressMethod" value="bcc"{$allocEmailAddressMethod == "bcc" and print " checked"}>&nbsp;&nbsp;&nbsp;&nbsp;
      <label for="eam_tobcc">Use Both with special "To:"</label><input id="eam_tobcc" type="radio" name="allocEmailAddressMethod" value="tobcc"{$allocEmailAddressMethod == "tobcc" and print " checked"}>
    </td> 
    <td width="1%">{page::help("config_allocEmailAddressMethod")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Session Timeout Minutes</nobr></td>
    <td><input type="text" size="70" value="{$allocSessionMinutes}" name="allocSessionMinutes"></td> 
    <td width="1%">{page::help("config_allocSessionMinutes")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="finance">
<form action="{$url_alloc_config}" method="post">
<table class="box">
  <tr>
    <th colspan="3">Finance Setup</th>
  </tr>
  <tr>
    <td width="20%">Main Currency</td>
    <td><select name="currency">{$currencyOptions}</select><input type="submit" name="update_currencyless_transactions" value="Update Transactions That Have No Currency"></td>
    <td width="1%">{page::help("config_currency")}</td>
  </tr>
  <tr>
    <td width="20%">Update Exchange Rates</td>
    <td><input type="submit" name="fetch_exchange_rates" value="Manually Fetch Exchange Rates"></td>
    <td width="1%">{page::help("config_exchangeRates")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Finance Tagged Fund</nobr></td>
    <td><select name="mainTfID"><option value="">{$mainTfOptions}</select></td>
    <td width="1%">{page::help("config_mainTfID")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Outgoing Funds TF</nobr></td>
    <td><select name="outTfID"><option value="">{$outTfOptions}</select></td>
    <td width="1%">{page::help("config_outTfID")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Incoming Funds TF</nobr></td>
    <td><select name="inTfID"><option value="">{$inTfOptions}</select></td>
    <td width="1%">{page::help("config_inTfID")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Expense Form TF</nobr></td>
    <td><select name="expenseFormTfID"><option value="">{$expenseFormTfOptions}</option></td>
    <td width="1%">{page::help("config_expenseFormTfID")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Tax Tagged Fund</nobr></td>
    <td><select name="taxTfID"><option value="">{$taxTfOptions}</select></td>
    <td width="1%">{page::help("config_taxTfID")}</td>
  </tr>
  <tr>
    <td>Services Tax Name</td>
    <td><input type="text" size="70" value="{$taxName}" name="taxName"></td> 
    <td width="1%">{page::help("config_taxName")}</td>
  </tr>
  <tr>
    <td>Services Tax Percent</td>
    <td><input type="text" size="70" value="{$taxPercent}" name="taxPercent"></td> 
    <td width="1%">{page::help("config_taxPercent")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center">
      <input type="submit" name="save" value="Save">
    </td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="finance">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="email_gateway">
<form action="{$url_alloc_config}" method="post">
<table class="box">
  <tr>
    <th colspan="2">Email Gateway</th>
    <th class="right">{page::help("config_allocEmailGateway")}</th>
  </tr>
  <tr>
    <td width="20%"><nobr>From Address</nobr></td>
    <td><input type="text" size="70" value="{$AllocFromEmailAddress}" name="AllocFromEmailAddress"></td> 
    <td width="1%">{page::help("config_AllocFromEmailAddress")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Hostname/IP</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailHost}" name="allocEmailHost"></td> 
    <td width="1%">{page::help("config_allocEmailHost")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Port</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailPort}" name="allocEmailPort"></td> 
    <td width="1%">{page::help("config_allocEmailPort")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Username</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailUsername}" name="allocEmailUsername"></td> 
    <td width="1%">{page::help("config_allocEmailUsername")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Password</nobr></td>
    <td><input type="password" size="70" value="{$allocEmailPassword}" name="allocEmailPassword"></td> 
    <td width="1%">{page::help("config_allocEmailPassword")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Protocol</nobr></td>
    <td><select name="allocEmailProtocol">{page::select_options(array("imap"=>"IMAP","pop3"=>"POP3"),$allocEmailProtocol)}</select></td> 
    <td width="1%">{page::help("config_allocEmailProtocol")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Box Name</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailFolder}" name="allocEmailFolder"></td> 
    <td width="1%">{page::help("config_allocEmailFolder")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Email Connect Extra</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailExtra}" name="allocEmailExtra"></td> 
    <td width="1%">{page::help("config_allocEmailExtra")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center">
      <input type="submit" name="save" value="Save">
      <input type="submit" name="test_email_gateway" value="Test Connection">
    </td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="email_gateway">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="email_subject">
<form action="{$url_alloc_config}" method="post">
<table class="box">
  <tr>
    <th colspan="2">Email Subject Lines</th>
    <th class="right">{page::help("config_allocEmailSubject")}</th>
  </tr>
  <tr>
    <td width="20%"><nobr>Task Comments</nobr></td>
    <td><input type="text" size="70" value="{$emailSubject_taskComment}" name="emailSubject_taskComment"></td> 
    <td width="1%">{page::help("config_taskSubjectLine")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Daily Digest</nobr></td>
    <td colspan="2"><input type="text" size="70" value="{$emailSubject_dailyDigest}" name="emailSubject_dailyDigest"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Time sheet submitted to manager</nobr></td>
    <td><input type="text" size="70" value="{$emailSubject_timeSheetToManager}" name="emailSubject_timeSheetToManager"></td> 
    <td width="1%">{page::help("config_timeSheetSubjectLine")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Time sheet rejected by manager</nobr></td>
    <td colspan="2"><input type="text" size="70" value="{$emailSubject_timeSheetFromManager}" name="emailSubject_timeSheetFromManager"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Time sheet submitted to administrator</nobr></td>
    <td colspan="2"><input type="text" size="70" value="{$emailSubject_timeSheetToAdministrator}" name="emailSubject_timeSheetToAdministrator"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Time sheet rejected by administrator</nobr></td>
    <td colspan="2"><input type="text" size="70" value="{$emailSubject_timeSheetFromAdministrator}" name="emailSubject_timeSheetFromAdministrator"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Time sheet completed</nobr></td>
    <td colspan="2"><input type="text" size="70" value="{$emailSubject_timeSheetCompleted}" name="emailSubject_timeSheetCompleted"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Reminder about a client</nobr></td>
    <td><input type="text" size="70" value="{$emailSubject_reminderClient}" name="emailSubject_reminderClient"></td> 
    <td width="1%">{page::help("config_clientSubjectLine")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Reminder about a project</nobr></td>
    <td><input type="text" size="70" value="{$emailSubject_reminderProject}" name="emailSubject_reminderProject"></td> 
    <td width="1%">{page::help("config_projectSubjectLine")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Reminder about a task</nobr></td>
    <td><input type="text" size="70" value="{$emailSubject_reminderTask}" name="emailSubject_reminderTask"></td> 
    <td width="1%">{page::help("config_taskSubjectLine")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Other reminder</nobr></td>
    <td colspan="2"><input type="text" size="70" value="{$emailSubject_reminderOther}" name="emailSubject_reminderOther"></td> 
  </tr>
  <tr>
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="email_subject">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="time_sheets">
<form action="{$url_alloc_config}" method="post">
<table class="box">
  <tr>
    <th colspan="3">Time Sheets Setup</th>
  </tr>
  <tr>
    <td>Time Sheet Manager</td>
    <td><a href="{$url_alloc_configEdit}configName=defaultTimeSheetManagerList&amp;configType=people">Edit:</a>
    {$defaultTimeSheetManagerListText}
    </td>
    <td width="1%">{page::help("config_timeSheetManagerEmail")}</td>
  </tr>
  <tr>
    <td>Time Sheet Administrator</td>
    <td><a href="{$url_alloc_configEdit}configName=defaultTimeSheetAdminList&amp;configType=people">Edit:</a>
    {$defaultTimeSheetAdminListText}
    </td>
    <td width="1%">{page::help("config_timeSheetAdminEmail")}</td>
  </tr>
  <tr>
    <td>Hours in a Working Day</td>
    <td><input type="text" size="70" value="{$hoursInDay}" name="hoursInDay"></td> 
    <td width="1%">{page::help("config_hoursInDay")}</td>
  </tr>
  <tr>
    <td>Default timesheet rate</td>
    <td><input type="text" size="70" value="{page::money(0, $defaultTimeSheetRate, "%mo")}" name="defaultTimeSheetRate"></td>
    <td width="1%"></td>
  </tr>
  <tr>
    <td>Default timesheet unit</td>
    <td><select name="defaultTimeSheetUnit"><option value="">{$timesheetRate_options}</select></td>
    <td width="1%"></td>
  </tr>
  <tr>
    <td valign="top">Time Sheet Print Options</td>
    <td><select size="9" name="timeSheetPrint[]" multiple><option value="">{$timeSheetPrintOptions}</select><a href="{$url_alloc_configEdit}configName=timeSheetPrintOptions">Edit</a></td>
    <td width="1%" valign="top">{page::help("config_timeSheetPrint")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="time_sheets">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="company_info">
<form action="{$url_alloc_config}" method="post" enctype="multipart/form-data">
<table class="box">
  <tr>
    <th colspan="2">Company Information</th>
    <th width="1%">{page::help("config_companyInfo")}</th>
  </tr>
  <tr>
    <td width="20%">Company Name</td>
    <td><input type="text" size="70" value="{$companyName}" name="companyName"></td> 
  </tr>
  <tr>
    <td>Company Phone</td>
    <td><input type="text" size="70" value="{$companyContactPhone}" name="companyContactPhone"></td> 
  </tr>
  <tr>
    <td>Company Fax</td>
    <td><input type="text" size="70" value="{$companyContactFax}" name="companyContactFax"></td> 
  </tr>
  <tr>
    <td>Company Email</td>
    <td><input type="text" size="70" value="{$companyContactEmail}" name="companyContactEmail"></td> 
  </tr>
  <tr>
    <td>Company Home Page</td>
    <td><input type="text" size="70" value="{$companyContactHomePage}" name="companyContactHomePage"></td> 
  </tr>
  <tr>
    <td>Company Address (line 1)</td>
    <td><input type="text" size="70" value="{$companyContactAddress}" name="companyContactAddress"></td> 
  </tr>
  <tr>
    <td>Company Address (line 2)</td>
    <td><input type="text" size="70" value="{$companyContactAddress2}" name="companyContactAddress2"></td> 
  </tr>
  <tr>
    <td>Company Address (line 3)</td>
    <td><input type="text" size="70" value="{$companyContactAddress3}" name="companyContactAddress3"></td> 
  </tr>
  <tr>
    <td>Company Logo</td>
    <td>
      <input type="file" name="companyLogo" size="70">
      {if file_exists(ALLOC_LOGO)}<input type="submit" name="delete_logo" value="Delete Current Logo">{/}
    </td>
    <td width="1%">{page::help("config_companyLogo")}</td>
  </tr>
  <tr>
    <td>Invoice / Time Sheet PDF Header 2</td>
    <td><input type="text" size="70" value="{$companyACN}" name="companyACN"></td> 
  </tr>
  <tr>
    <td>Invoice / Time Sheet PDF Header 3</td>
    <td><input type="text" size="70" value="{$companyABN}" name="companyABN"></td> 
  </tr>
  <tr>
    <td>Invoice / Time Sheet PDF Footer</td>
    <td><input type="text" size="70" value="{$timeSheetPrintFooter}" name="timeSheetPrintFooter"></td> 
    <td width="1%">{page::help("config_timeSheetPrintFooter")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="company_info">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="rss">
<form action="{$url_alloc_config}" method="post">
<table class="box">
  <tr>
    <th colspan="2">RSS Feed Setup</th>
    <th>{page::help('config_rssFeed')}</th>
  </tr>
  <tr>
    <td>Number of entries</td>
    <td><input type="text" size="70" value="{$rssEntries}" name="rssEntries"></td> 
    <td width="1%">{page::help('config_rssEntries')}</tr>
  </tr>
  <tr>
    <td>Status changes to include</td>
    <td><select size="9" name="rssStatusFilter[]" multiple>{$rssStatusFilterOptions}</select></td>
    <td width="1%">{page::help('config_rssStatusFilter')}</td>
  <tr>
    <td>Show project name in feed</td>
    <td><input type="checkbox" name="rssShowProject" {if $rssShowProject}checked="checked"{/}></td>
    <td></td>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="rss">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="misc">
<form action="{$url_alloc_config}" method="post">
<table class="box">
  <tr>
    <th colspan="2">Miscellaneous Setup</th>
    <th width="1%">{page::help("config_misc_setup")}</th>
  </tr>
  <tr>
    <td valign="top" width="20%"><nobr>Extra Interested Parties Options</nobr></td>
    <td>
      <a href="{$url_alloc_configEdit}configName=defaultInterestedParties">Edit:</a>
      {foreach $defaultInterestedParties as $k => $v}
          {$br}{$k} {$v}
          {$br = ", "}
      {/}
    </td> 
    <td width="1%">{page::help("config_defaultInterestedParties.html")}</td>
  </tr>
  <tr>
    <td valign="top" width="20%"><nobr>Project Priorities</nobr></td>
    <td>
      <a href="{$url_alloc_configEdit}configName=projectPriorities">Edit:</a>
      {unset($br)}
      {foreach $projectPriorities as $k => $arr}
          {$br}<span style="color:{$arr.colour}">{$k} {$arr.label}</span>
          {$br = ", "}
      {/}
    </td> 
    <td width="1%">{page::help("config_projectPriorities.html")}</td>
  </tr>
  <tr>
    <td valign="top" width="20%"><nobr>Task Priorities</nobr></td>
    <td>
      <a href="{$url_alloc_configEdit}configName=taskPriorities">Edit:</a>
      {unset($br)}
      {foreach $taskPriorities as $k => $arr}
          {$br}<span style="color:{$arr.colour}">{$k} {$arr.label}</span>
          {$br = ", "}
      {/}
    </td> 
    <td width="1%">{page::help("config_taskPriorities.html")}</td>
  </tr>
  <tr>
    <td valign="top" width="20%"><nobr>Client Categories</nobr></td>
    <td>
      <a href="{$url_alloc_configEdit}configName=clientCategories">Edit:</a>
      {unset($br)}
      {foreach $clientCategories as $k => $arr}
          {$br}{$arr.label}
          {$br = ", "}
      {/}
    </td> 
    <td width="1%">{page::help("config_clientCategories.html")}</td>
  </tr>

  {$meta = new meta()}
  {foreach (array)$meta->get_tables() as $table => $label} 
    <tr>
      <td>{$label}</td>
      <td>
      <a href="{$url_alloc_metaEdit}configName={$table}">Edit:</a>
      {unset($br)}
      {$t = new meta($table)}
      {$rows = $t->get_list()}
      {foreach $rows as $row}{echo $br.$row[$table."ID"]}{$br = ", "}{/}
      </td>
    </tr>
  {/}
  <tr>
    <td>Map URL</td>
    <td><input type="text" size="70" value="{$mapURL}" name="mapURL"></td>
    <td width="1%">{page::help("config_mapURL")}</td>
  </tr>
  <tr>
    <td>Task Priority Spread</td>
    <td><input type="text" size="70" value="{$taskPrioritySpread}" name="taskPrioritySpread"></td>
    <td width="1%">{page::help("config_taskPrioritySpread")}</td>
  </tr>
  <tr>
    <td>Task Priority Scale</td>
    <td><input type="text" size="70" value="{$taskPriorityScale}" name="taskPriorityScale"></td>
    <td width="1%">{page::help("config_taskPriorityScale")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="misc">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>


  
{page::footer()}
