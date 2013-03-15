{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">

$(document).ready(function() {
  {if !$project_projectID}
    toggle_view_edit();
    $('#projectName').focus();
    // fake a click to the client status radio button
    clickClientStatus(); 
  {else}
    $('#editProject').focus();
  {/}

  // This listens to the client radio buttons and refreshes the client dropdown
  $('input[name=client_status]').bind("click", clickClientStatus);

  // This listens to the client dropdown and refreshes the client contact
  // dropdown, we have to use livequery() like this instead of bind() because the
  // client dropdown needs to maintain its onChange event once it is refreshed
  $('select[name=clientID]').livequery("change", function(e) {
    url = '{$url_alloc_updateProjectClientContactList}clientID='+this.value;
    makeAjaxRequest(url,'clientContactDropdown');
  });

  // This listens to the Copy Project radio buttons
  $('input[name=project_status]').bind("click", function(e) {
    url = '{$url_alloc_updateCopyProjectList}projectStatus='+this.value;
    makeAjaxRequest(url,'projectDropdown')
  });

  // This opens up the copy_project div and loads the dropdown list
  $('#copy_project_link').bind("click", function(e) {
    $('#copy_project').slideToggle();
    url = '{$url_alloc_updateCopyProjectList}projectStatus=Current';
    makeAjaxRequest(url,'projectDropdown')
  });

});

function updatePersonRate(dropdown) {
  var personID = dropdown.value;
  var tr = $(dropdown).parent().parent();
  var ratebox = tr.find('input[name=person_rate\\[\\]]');
  var rateunit = tr.find('select[name=person_rateUnitID\\[\\]]');

  // ratebox.data['value'] is the auto-set value - only change it if the user
  // hasn't touched it.
  if (!ratebox[0].value || !ratebox.data('value') || ratebox[0].value == ratebox.data('value')) {
    $.getJSON('{$url_alloc_updateProjectPersonRate}project={$project_projectID}&person=' + personID, function(data) {
      ratebox[0].value = data['rate'];
      rateunit[0].selectedIndex = data['unit'];
      ratebox.data('value', data['rate']);
    });
  }
}

function clickClientStatus(e) {

  if (!$('input[name=client_status]:checked').val()) {
    $('#client_status_current').attr("checked", "checked");
    this.value = 'current';
  }

  clientID = $('#clientID').val()
  url = '{$url_alloc_updateProjectClientList}clientStatus='+this.value+'&clientID='+clientID;
  makeAjaxRequest(url,'clientDropdown')

  // If there's a clientID update the Client Contact dropdown as well
  if (clientID) {
    clientContactID = $('#clientContactID').val()
    url = '{$url_alloc_updateProjectClientContactList}clientID='+clientID+'&clientContactID='+clientContactID;
    makeAjaxRequest(url,'clientContactDropdown')
  }
}

</script>

{if defined("PROJECT_EXISTS")}
{$first_div="hidden"}
{page::side_by_side_links(array("project"=>"Main"
                               ,"people"=>"People"
                               ,"commissions"=>"Commissions"
                               ,"comments"=>"Comments"
                               ,"attachments"=>"Attachments"
                               ,"tasks"=>"Tasks"
                               ,"reminders"=>"Reminders"
                               ,"time"=>"Time Sheets"
                               ,"transactions"=>"Transactions"
                               ,"invoices"=>"Invoices"
                               ,"sales"=>"Sales"
                               ,"importexport"=>"Import/Export"
                               ,"history"=>"History"
                               ,"sbsAll"=>"All")
                          ,$url_alloc_project."projectID=".$project_projectID)}
{/}

<div id="project" class="{$first_div}">
<form action="{$url_alloc_project}" method="post" id="projectForm">
<input type="hidden" name="projectID" value="{$project_projectID}">
<table class="box">
  <tr>
    <th class="header" colspan="5">{$projectSelfLink}
      <span>
        {if defined("PROJECT_EXISTS")}
        {$navigation_links}
        {page::star("project",$project_projectID)}
        {/}
      </span>
    </th>
  </tr>
  <tr>
    <td colspan="5" valign="top">
      <div style="min-width:400px; width:47%; float:left; padding:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>{$project_projectType}{page::mandatory($project_projectName)}</h6>
          <h2 style="margin-bottom:0px; display:inline;">{$project_projectID} {=$project_projectName}</h2>&nbsp;{$priorityLabel}
        </div>

        <div class="edit">
          <h6>{print $project_projectType ? $project_projectType : "Project"}{page::mandatory($project_projectName)}</h6>
          <input type="text" name="projectName" id="projectName" value="{$project_projectName_html}" size="45">
          <select name="projectPriority">{$projectPriority_options}</select>
          <select name="projectType">{$projectType_options}</select>
        </div>
        
        {if $project_projectComments_html}  
        <div class="view">
          <h6>Description</h6>
          {$project_projectComments_html}
        </div>
        {/}
        <div class="edit">
          <h6>Description</h6>  
          {page::textarea("projectComments",$project_projectComments,array("height"=>"medium","width"=>"100%"))}
        </div>

        {if $clientDetails}
        <div class="view">
          <h6>Client</h6>
          {$clientDetails}
        </div>
        {/}
        <div class="edit">
          <h6>Client</h6>
          {$clientHidden}
          <label for="client_status_current">Current Clients</label>
          <input id="client_status_current" type="radio" name="client_status" value="Current">
          &nbsp;&nbsp;&nbsp;
          <label for="client_status_potential">Potential Clients</label>
          <input id="client_status_potential" type="radio" name="client_status" value="Potential">
          &nbsp;&nbsp;&nbsp;
          <label for="client_status_archived">Archived Clients</label>
          <input id="client_status_archived" type="radio" name="client_status" value="Archived">
          <div id="clientDropdown">
            {$clientDropdown}
          </div>
          <div id="clientContactDropdown" style="margin-top:10px;">
            {$clientContactDropdown}
          </div>
        </div>
          
      </div>

      <div style="min-width:400px; width:47%; float:left; margin:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>Project Nickname<div><span style='width:50%; display:inline-block;'>Currency</span><span>Status</span></div></h6>
          <div style="float:left; width:40%;">
            {=$project_projectShortName}
          </div>
          <div style="float:right; width:50%;">
            <span style='width:50%; display:inline-block;'>{page::money($project_currencyTypeID,0,"%n")}</span>
            <span>{$project_projectStatus}</span>
          </div>
        </div>

        <div class="edit">
          <h6>Project Nickname<div><span style='width:50%; display:inline-block;'>Currency</span><span>Status</span></div></h6>
          <div style="float:left; width:40%;">
            <input type="text" name="projectShortName" value="{$project_projectShortName}" size="10">
          </div>
          <div style="float:right; width:50%;">
            <span style='width:50%; display:inline-block;'><select name="currencyTypeID">{$currencyType_options}</select></span>
            <span><select name="projectStatus">{$projectStatus_options}</select></span>
          </div>
        </div>

        {if imp($project_projectBudget) || $cost_centre_tfID_label}
        <div class="view">
          <h6>Budget<div>Cost Centre TF</div></h6>
          <div style="float:left; width:40%;">
            {page::money($project_currencyTypeID,$project_projectBudget,"%s%mo %c")}
            {$taxName && imp($project_projectBudget) and print " (inc. $taxName)"}
          </div>
          <div style="float:right; width:50%;">
            {$cost_centre_tfID_label}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Budget<div>Cost Centre TF</div></h6>
          <div style="float:left; width:40%;">
            <input type="text" name="projectBudget" value="{page::money($project_currencyTypeID,$project_projectBudget,"%mo")}" size="10"> 
            {$taxName and print " (inc. $taxName)"}
          </div>
          <div style="float:right; width:50%;" class="nobr">
            <select name="cost_centre_tfID" style="width:95%">
              <option value="">&nbsp;</option>
              {$cost_centre_tfID_options}
            </select>
            {page::help("project_cost_centre_tf")}
          </div>
        </div>

        {$tax_string2 = sprintf(" (per unit%s)", $taxName ? ", inc. ".$taxName : "")}
        {if imp($project_customerBilledDollars) || imp($project_defaultTaskLimit)}
        <div class="view">
          <h6>Client Billed At<div>Default Task Limit</div></h6>
          <div style="float:left; width:40%;">
            {page::money($project_currencyTypeID,$project_customerBilledDollars,"%s%mo %c")}
            {imp($project_customerBilledDollars) and print $tax_string2}
          </div>
          <div style="float:right; width:50%;">
            <span>{$project_defaultTaskLimit}</span>
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Client Billed At<div>Default Task Limit</div></h6>
          <div style="float:left; width:40%;">
            <input type="text" name="customerBilledDollars" value="{page::money($project_currencyTypeID,$project_customerBilledDollars,"%mo")}" size="10"> 
            {$tax_string2}
          </div>
          <div style="float:right; width:50%;">
            <span><input type="text" size="5" name="defaultTaskLimit" value="{$project_defaultTaskLimit}"> {page::help("project_defaultTaskLimit")}</span>
          </div>
        </div>

        <div class="edit">
          <h6>Default Interested Parties</h6> 
          <div id="interestedPartyDropdown" style="display:inline">{$interestedPartyOptions}</div>
          {page::help("project_interested_parties")}
        </div>
        {if $interestedParties}
        <div class="view">
          <h6>Default Interested Parties</h6> 
          <table class="nopad" style="width:100%;">
          {foreach $interestedParties as $ip}
            <tr class="hover">
              <td style="width:50%;">
                <a class='undecorated' href='mailto:{=$ip.name} <{=$ip.email}>'>{=$ip.name}</a>
              </td>
              <td style="width:50%;">
                {if $ip["phone"]["p"]}Ph: {=$ip.phone.p}{/}
                {if $ip["phone"]["p"] && $ip["phone"]["m"]} / {/}
                {if $ip["phone"]["m"]}Mob: {=$ip.phone.m}{/}
              </td>
            </tr>
          {/}
          </table>
        </div>
        {/}

        {if $project_defaultTimeSheetRate || $defaultTimeSheetRateUnits}
        <div class="view">
          <h6>Default timesheet rate<div>Default timesheet unit</div></h6>
          <div style="float:left; width:40%;">
            {page::money($project_currencyTypeID, $project_defaultTimeSheetRate)}
          </div>
          <div style="float:right; width:50%;">
            {$defaultTimeSheetRateUnits}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Default timesheet rate<div>Default timesheet unit</div></h6>
          <div style="float:left; width:40%;">
            <input type="text" name="defaultTimeSheetRate"
	    value="{page::money($project_currencyTypeID, $project_defaultTimeSheetRate,"%mo")}" size="10"> 
          </div>
          <div style="float:right; width:50%;">
            <select name="defaultTimeSheetRateUnitID"><option value="">{$defaultTimeSheetUnit_options}</select>
          </div>
        </div>

        {if $project_dateTargetStart || $project_dateTargetCompletion}
        <div class="view">
          <h6>Estimated Start<div>Estimated Completion</div></h6>
          <div style="float:left; width:40%;">
            {$project_dateTargetStart}
          </div>
          <div style="float:right; width:50%;">
            {$project_dateTargetCompletion}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Estimated Start<div>Estimated Completion</div></h6>
          <div style="float:left; width:40%;">
            {page::calendar("dateTargetStart",$project_dateTargetStart)}
          </div>
          <div style="float:right; width:50%;">
            {page::calendar("dateTargetCompletion",$project_dateTargetCompletion)}
          </div>
        </div>

        {if $project_dateActualStart || $project_dateActualCompletion}
        <div class="view">
          <h6>Actual Start<div>Actual Completion</div></h6>
          <div style="float:left; width:40%;">
            {$project_dateActualStart}
          </div>
          <div style="float:right; width:50%;">
            {$project_dateActualCompletion}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Actual Start<div>Actual Completion</div></h6>
          <div style="float:left; width:40%;">
            {page::calendar("dateActualStart",$project_dateActualStart)}
          </div>
          <div style="float:right; width:50%;">
            {page::calendar("dateActualCompletion",$project_dateActualCompletion)}
          </div>
        </div>

      </div>
    </td>
  </tr>
  <tr>
    <td align="center" colspan="5">
      <div class="view" style="margin-top:20px">
        <button type="button" id="editProject" value="1" onClick="toggle_view_edit();clickClientStatus();">Edit Project<i class="icon-edit"></i></button>
      </div>
      <div class="edit" style="margin-top:20px">
        <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        <button type="submit" name="save" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>
        <br><br>
        <a href="" onClick="return toggle_view_edit(true);">Cancel edit</a>
      </div>
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>

{if defined("PROJECT_EXISTS")}
<table class="box">
  <tr>
    <th class="nobr" width="10%">Financial Summary</th>
    <th class="right" colspan="3">{page::help("project_financial_summary")}</th>
  </tr>
  <tr>
    <td class="right nobr">Outstanding Invoices</td>
    <td class="right">{$total_invoice_transactions_pending}</td>
    <td class="right nobr">Pending time sheets</td>
    <td class="right">{$total_timeSheet_transactions_pending}</td>
  </tr>
  <tr>
    <td class="right">Paid Invoices</td>
    <td class="right">{$total_invoice_transactions_approved}</td>
    <td class="right">Paid time sheets</td>
    <td class="right">{$total_timeSheet_transactions_approved}</td>
  </tr>
  <tr>
    <td class="right nobr">Task Time Estimate</td>
    <td class="right">{$time_remaining} {page::money($project_currencyTypeID,$cost_remaining)} {$count_not_quoted_tasks}</td>
    <td class="right">Sum Customer Billed for Time Sheets</td>
    <td class="right">{$total_timeSheet_customerBilledDollars}</td>
  </tr>            
  <tr>
    <td class="right nobr">Expenses</td>
    <td class="right">{$total_expenses_transactions_approved}</td>
    <td colspan="2"></td>
  </tr>            
</table>
{/}

</div>
 
{if defined("PROJECT_EXISTS")}


<div id="people">
<form action="{$url_alloc_project}" method="post">


<table class="box">
  <tr>
    <th class="header" align="left">Project People
      <span>
        <a href="#x" class="magic" onClick="$('#project_people_footer').before('<tr>'+$('#new_projectPerson').html()+'</tr>');">New Project Person</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list">
        <tr>
          <th>Person</th>
          <th>Role</th>
          <th>Rate</th>
          <th colspan="2">Unit</th>
        </tr>
{show_person_list("templates/projectPersonListR.tpl")}
{show_new_person("templates/projectPersonListR.tpl")}
        <tr id="project_people_footer">
          <td colspan="5" class="center">
            <button type="submit" name="person_save" value="1" class="save_button">Save Project People<i class="icon-ok-sign"></i></button>
            <input type="hidden" name="projectID" value="{$project_projectID}">
            <input type="hidden" name="sbs_link" value="people">
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>


<input type="hidden" name="sessID" value="{$sessID}">
</form>
</div>

<div id="comments">
{show_comments()}
</div>


<div id="commissions">
<table class="box">
  <tr>
    <th class="header">
    Time Sheet Commissions
    <span>
    {page::help("timesheet_commission")}
    </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list">
        <tr>
          <th>Tagged Fund</th>
          <th colspan="3">Percentage</th>
        </tr>
{show_commission_list("templates/commissionListR.tpl")}
{show_new_commission("templates/commissionListR.tpl")}
      </table>
    </td>
  </tr>
</table>
</div>


<div id="attachments">
{show_attachments()}
</div>

<div id="tasks">
{show_tasks()}
</div>

<div id="reminders">
<table class="box">  
  <tr>
    <th class="header">Reminders
      <span>
        <a href="{$url_alloc_reminder}step=3&parentType=project&parentID={$project_projectID}&returnToParent=project">New Reminder</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {reminder::get_list_html("project",$project_projectID)}
    </td>
  </tr>
</table>
</div>

<div id="time">
{show_time_sheets("templates/projectTimeSheetS.tpl")}
</div>

<div id="transactions">
{show_transactions("templates/projectTransactionS.tpl")}
</div>

<div id="invoices">
  <table class="box">
    <tr>
      <th class="header">Invoices
        <span>
          {$invoice_links}
        </span>
      </th>
    </tr>
    <tr>
      <td>
        {show_invoices()}
      </td>
    </tr>
  </table>
</div>

<div id="sales">
<table class="box">
  <tr>
    <th class="header">Product Sales
      <span>
        <a href="{$url_alloc_productSale}projectID={$project_projectID}">New Sale</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {$productSaleRows = productSale::get_list(array("projectID"=>$project_projectID))}
      {echo productSale::get_list_html($productSaleRows)}
    </td>
  </tr>
</table>
</div>

<div id="importexport">
{show_import_export("templates/projectImportExportM.tpl")}
</div>

<div id="history">
{show_projectHistory()}
</div>
{/}


{page::footer()}
