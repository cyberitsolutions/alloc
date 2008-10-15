{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">

$(document).ready(function() \{
  {if !$project_projectID}
    $('.view').hide();
    $('.edit').show();
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
  $('select[name=clientID]').livequery("change", function(e)\{
    url = '{$url_alloc_updateProjectClientContactList}clientID='+this.value;
    makeAjaxRequest(url,'clientContactDropdown');
  \});

  // This listens to the Copy Project radio buttons
  $('input[name=project_status]').bind("click", function(e)\{
    url = '{$url_alloc_updateCopyProjectList}projectStatus='+this.value;
    makeAjaxRequest(url,'projectDropdown')
  \});

  // This opens up the copy_project div and loads the dropdown list
  $('#copy_project_link').bind("click", function(e)\{
    $('#copy_project').slideToggle();
    url = '{$url_alloc_updateCopyProjectList}projectStatus=curr';
    makeAjaxRequest(url,'projectDropdown')
  \});

\});

function clickClientStatus(e)\{

  if (!$('input[name=client_status]:checked').val()) \{
    $('#client_status_current').attr("checked", "checked");
    this.value = 'current';
  \}

  clientID = $('#clientID').val()
  url = '{$url_alloc_updateProjectClientList}clientStatus='+this.value+'&clientID='+clientID;
  makeAjaxRequest(url,'clientDropdown')

  // If there's a clientID update the Client Contact dropdown as well
  if (clientID) \{
    clientContactID = $('#clientContactID').val()
    url = '{$url_alloc_updateProjectClientContactList}clientID='+clientID+'&clientContactID='+clientContactID;
    makeAjaxRequest(url,'clientContactDropdown')
  \}
\}

</script>

{$_POST["person_save"] and $_POST["sbs_link"] = "people"}
{$_POST["commission_delete"] || $_POST["commission_save"] and $_POST["sbs_link"] = "commissions"}
{$_POST["delete_file_attachment"] || $_POST["save_attachment"] and $_POST["sbs_link"] = "attachments"}

{$sbs_link = $_POST["sbs_link"] or $sbs_link = $_GET["sbs_link"] or $sbs_link = "project"}
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
                             ,"importexport"=>"Import/Export"
                             ,"prodsales"=>"Product Sales"
                             ,"sbsAll"=>"All"
                             ),$sbs_link)}
{/}

<div id="project" class="{$first_div}">
<form action="{$url_alloc_project}" method="post" id="projectForm">
<input type="hidden" name="projectID" value="{$project_projectID}">
<table class="box">
  <tr>
    <th class="nobr" colspan="2">{$projectSelfLink}</th>
    <th class="right" colspan="3">{if defined("PROJECT_EXISTS")}{$navigation_links}{/}</th>
  </tr>
  <tr>
    <td colspan="5" valign="top" ondblclick="$('.view').hide();$('.edit').show();clickClientStatus();">
      <div style="float:left; width:47%; padding:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>{$project_projectType}{page::mandatory($project_projectName)}</h6>
          <h2 style="margin-bottom:0px; display:inline;">{$project_projectID} {$project_projectName}</h2>&nbsp;{$priorityLabel}
        </div>

        <div class="edit">
          <h6>{$project_projectType}{page::mandatory($project_projectName)}</h6>
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
          <input id="client_status_current" type="radio" name="client_status" value="current">
          &nbsp;&nbsp;&nbsp;
          <label for="client_status_potential">Potential Clients</label>
          <input id="client_status_potential" type="radio" name="client_status" value="potential">
          &nbsp;&nbsp;&nbsp;
          <label for="client_status_archived">Archived Clients</label>
          <input id="client_status_archived" type="radio" name="client_status" value="archived">
          <div id="clientDropdown">
            {$clientDropdown}
          </div>
          <div id="clientContactDropdown" style="margin-top:10px;">
            {$clientContactDropdown}
          </div>
        </div>
          
      </div>

      <div style="float:right; width:47%; padding:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>Project Nickname<div>Status</div></h6>
          <div style="float:left; width:40%;">
            {$project_projectShortName}
          </div>
          <div style="float:right; width:50%;">
            {echo ucwords($project_projectStatus)}
          </div>
        </div>

        <div class="edit">
          <h6>Project Nickname<div>Status</div></h6>
          <div style="float:left; width:40%;">
            <input type="text" name="projectShortName" value="{$project_projectShortName}" size="10">
          </div>
          <div style="float:right; width:50%;">
            <select name="projectStatus">{$projectStatus_options}</select>
          </div>
        </div>


        {if $project_projectBudget || $project_currencyType || $cost_centre_tfID_label}
        <div class="view">
          <h6>Budget<div>Cost Centre TF</div></h6>
          <div style="float:left; width:40%;">
            {$project_projectBudget} {$project_currencyType}{if $project_projectBudget && $taxName} (inc. {$taxName}){/}
          </div>
          <div style="float:right; width:50%;">
            {$cost_centre_tfID_label}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Budget<div>Cost Centre TF</div></h6>
          <div style="float:left; width:40%;">
            <input type="text" name="projectBudget" value="{$project_projectBudget}" size="10"> 
            <select name="currencyType"><option value="">{$currencyType_options}</select><br>
          </div>
          <div style="float:right; width:50%;">
            <select name="cost_centre_tfID">
              <option value="">&nbsp;</option>
              {$cost_centre_tfID_options}
            </select>
          </div>
        </div>

        {if $project_customerBilledDollars || $project_is_agency_label}
        <div class="view">
          <h6>Client Billed At<div>Payroll Tax Exempt</div></h6>
          <div style="float:left; width:40%;">
            {$project_customerBilledDollars}{if $project_customerBilledDollars} {$project_currencyType} (per unit{if $taxName}, inc. {$taxName}){/}{/}
          </div>
          <div style="float:right; width:50%;">
            {$project_is_agency_label}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Client Billed At<div>Payroll Tax Exempt</div></h6>
          <div style="float:left; width:40%;">
            <input type="text" name="customerBilledDollars" value="{$project_customerBilledDollars}" size="10"> (per unit, inc. {$taxName})
          </div>
          <div style="float:right; width:50%;">
            <select name="is_agency">{$is_agency_options}</select>
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
        <input type="button" id="editProject" value="Edit Project" onClick="$('.view').hide();$('.edit').show();clickClientStatus();">
      </div>
      <div class="edit" style="margin-top:20px">
        <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
        <input type="submit" name="delete" value="Delete" class="delete_button">
        <input type="button" value="Cancel Edit" onClick="$('.edit').hide();$('.view').show();">
      </div>
    </td>
  </tr>
</table>
</form>

{if defined("PROJECT_EXISTS")}
<table class="box">
  <tr>
    <th>Financial Summary</th>
    <th class="right">{page::help("project_financial_summary")}</th>
  </tr>
  <tr>
    <td>Project Spend: ${$grand_total} ({$percentage}%)</td>
  </tr>
  <tr>
    <td>Task Time Estimate: {$time_remaining} Hours  ${$cost_remaining} ({$count_not_quoted_tasks} tasks not included in estimate)</td>
  </tr>            
</table>
{/}

</div>
 
{if defined("PROJECT_EXISTS")}


<div id="people" class="hidden">
<form action="{$url_alloc_project}" method="post">
<table class="box">
  <tr>
    <th align="left">Project People</th>
    <th class="right"><a href="#x" class="magic" onClick="$('#projectPersonContainer').append($('#new_projectPerson').html());">New</a></th>
  </tr>
  <tr>
    <td colspan="2" id="projectPersonContainer">
{show_person_list("templates/projectPersonListR.tpl")}
{show_new_person("templates/projectPersonListR.tpl")}
    </td>
  </tr>
  <tr>
    <td colspan="2" align="right">
      <input type="submit" name="person_save" value="Save Project People">
      <input type="hidden" name="projectID" value="{$project_projectID}">
    </td>
  </tr>
</table>
</form>
</div>

<div id="comments" class="hidden">
{show_comments()}
</div>


<div id="commissions" class="hidden">
<table class="box">
  <tr>
    <th align="left" colspan="4">Time Sheet Commission</th>
  </tr>
  <tr>
    <td colspan="4">Enter TF and commision amount or 0 to indicate "All Remaining Funds"</td>
  </tr>
{show_commission_list("templates/commissionListR.tpl")}
{show_new_commission("templates/commissionListR.tpl")}
</table>
</div>


<div id="attachments" class="hidden">
{show_attachments()}
</div>

<div id="tasks" class="hidden">
<table class="box">
  <tr>
    <th>Uncompleted Tasks</th>
    <th class="right"><a href="{$url_alloc_task}projectID={$project_projectID}">New Task</a></th>
  </tr>
  <tr>
    <td colspan="2">
    {$task_summary}
    </td>
  </tr>
</table>
</div>

<div id="reminders" class="hidden">
<table class="box">  
  <tr>
    <th colspan="4">Reminders</th>
    <th class="right">
      <a href="{$url_alloc_reminderAdd}step=3&parentType=project&parentID={$project_projectID}&returnToParent=project">
      New Reminder</a>
    </th>
  </tr>
  <tr>
    <td>Recipient</td>
    <td>Date / Time</td>
    <td>Subject</td>
    <td>Repeat</td>
  </tr>
  <form action="{$url_alloc_project}" method=post>
  <input type="hidden" name="projectID" value="{$project_projectID}">
  {show_reminders("../reminder/templates/reminderR.tpl")}
  </form>
</table>
</div>

<div id="time" class="hidden">
{show_time_sheets("templates/projectTimeSheetS.tpl")}
</div>
<div id="transactions" class="hidden">
{show_transactions("templates/projectTransactionS.tpl")}
</div>
<div id="prodsales" class="hidden">
{show_product_sales("templates/projectProductSaleS.tpl")}
</div>
<div id="importexport" class="hidden">
{show_import_export("templates/projectImportExportM.tpl")}
</div>

{/}


{page::footer()}
