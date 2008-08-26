{show_header()}
{show_toolbar()}

{$_POST["person_save"] and $_POST["sbs_link"] = "people"}
{$_POST["commission_delete"] || $_POST["commission_save"] and $_POST["sbs_link"] = "commissions"}
{$_POST["delete_file_attachment"] || $_POST["save_attachment"] and $_POST["sbs_link"] = "attachments"}

{$sbs_link = $_POST["sbs_link"] or $sbs_link = $_GET["sbs_link"] or $sbs_link = "project"}
{if defined("PROJECT_EXISTS")}
{$first_div="hidden"}
{get_side_by_side_links(array("project"=>"Main"
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
{$table_box}
  <tr>
    <th class="nobr" colspan="2">Project: {$projectSelfLink}</th>
    <th class="right" colspan="3">{if defined("PROJECT_EXISTS")}{$navigation_links}{/}</th>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td width="1%" align="right" class="nobr">Project Name{mandatory($project_projectName)}</td>
    <td colspan="1"><input type="text" name="projectName" value="{$project_projectName}" size="45">
                    <select name="projectPriority">{$projectPriority_options}</select></td>
    <td></td>
    <td align="right">Short Name</td>
    <td><input type="text" name="projectShortName" value="{$project_projectShortName}" size="10"></td>
  </tr>
  <tr>
    <td align="right" rowspan="4" valign="top">Description</td>
    <td rowspan="4" colspan="2" valign="top">{get_textarea("projectComments",$TPL["project_projectComments"],array("height"=>"medium"))}</td>
    <td align="right">Status</td>
    <td><select name="projectStatus">{$projectStatus_options}</select></td>
  </tr>
  <tr>
    <td align="right">Type</td>
    <td><select name="projectType">{$projectType_options}</select></td>
  </tr>
  <tr>
    <td align="right">Currency</td>
    <td><select name="currencyType">{$currencyType_options}</select></td>
  </tr>
  <tr>
    <td align="right" class="nobr">Project Budget $</td>
    <td class="nobr"><input type="text" name="projectBudget" value="{$project_projectBudget}" size="10"> (ex. {$taxName})</td>
  </tr>
  <tr>
    <td align="right">Client</td>
    <td><nobr><select id="clientID" name="clientID" onChange="makeAjaxRequest('{$url_alloc_updateProjectClientContactList}clientID='+$('#clientID').attr('value'),'clientContactDropdown')">{$clientOptions}</select>&nbsp; &nbsp;<a href="{$url_alloc_client}">New Client</a></nobr></td>
    <td></td>
    <td align="right" class="nobr">Client Billed At $</td>
    <td><input type="text" name="customerBilledDollars" value="{$project_customerBilledDollars}" size="10"> (per unit, inc. {$taxName})</td>
  </tr>
  <tr>
    <td align="right">Contact</td>
    <td>
      <div id="clientContactDropdown">
        {$clientContactDropdown}
      </div>
    </td>
    <td></td>
    <td align="right">{$cost_centre_label}&nbsp;</td>
    <td>{$cost_centre_bit}&nbsp;</td>
  </tr>
  <tr>
    <td rowspan="2"  align="right"></td>
    <td rowspan="2">{$clientDetails}</td>
    <td align="right" colspan="2">Payroll Tax Exempt</td>
    <td><input type="checkbox" name="project_is_agency" value="1"{$project_is_agency}></td>
  </tr>
  <tr>
    <td colspan="2" class="right nobr" valign="bottom"><div style="margin-bottom:8px;">Target Start/Complete</div><div>Actual Start/Complete</div></td>
    <td valign="bottom">
      <div class="nobr">
      {get_calendar("dateTargetStart",$TPL["project_dateTargetStart"])}&nbsp;&nbsp;
      {get_calendar("dateTargetCompletion",$TPL["project_dateTargetCompletion"])}
      </div>
      <div class="nobr">
      {get_calendar("dateActualStart",$TPL["project_dateActualStart"])}&nbsp;&nbsp;
      {get_calendar("dateActualCompletion",$TPL["project_dateActualCompletion"])}
      </div>
    </td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td align="center" colspan="5">
      <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="submit" name="delete" value="Delete" class="delete_button">
    </td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
</table>
</form>

{if defined("PROJECT_EXISTS")}
{$table_box}
  <tr>
    <th>Financial Summary</th>
    <th class="right">{get_help("project_financial_summary")}</th>
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
{$table_box}
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
{$table_box}
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
{$table_box}
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
{$table_box}  
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


{show_footer()}
