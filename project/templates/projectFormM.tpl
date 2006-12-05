{show_header()}
{show_toolbar()}
<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() \{
  obj = document.getElementById("projectForm").clientID;
  id = obj.options[obj.selectedIndex].value;
  url = '{$url_alloc_updateProjectClientContactList}clientID='+id
  makeAjaxRequest(url,'updateClientContact',1)
\}

// Here's the callback function
function updateClientContact(number) \{
  if (http_request[number].readyState == 4) \{
    if (http_request[number].status == 200) \{
      document.getElementById("clientContactDropdown").innerHTML = http_request[number].responseText;
    \}
  \}
\}

</script>

<form action="{$url_alloc_project}" method="post" id="projectForm">
<input type="hidden" name="projectID" value="{$project_projectID}">

{$table_box}
  <tr>
    <th colspan="2">Project Details</th>
    <th class="right" colspan="2">{$navigation_links}</th>
  </tr>
  <tr>
    <td colspan="4">&nbsp;</td>
  </tr>
  <tr>
    <td align="right">Name</td>
    <td colspan="1"><input type="text" name="projectName" value="{$project_projectName}" size="45"></td>
    <td align="right">Short Name</td>
    <td><input type="text" name="projectShortName" value="{$project_projectShortName}" size="10"></td>
  </tr>
  <tr>
    <td align="right" rowspan="4" valign="top">Description</td>
    <td rowspan="4">
      <textarea name="projectComments" rows="7" wrap="virtual" cols="55">{$project_projectComments}</textarea>
    </td>
    <td align="right">Priority</td>
    <td><select name="projectPriority">{$projectPriority_options}</select></td>
  </tr>
  <tr>
    <td align="right">Status</td>
    <td><select name="projectStatus">{$projectStatus_options}</select></td>
  </tr>
  <tr>
    <td align="right">Type</td>
    <td><select name="projectType">{$projectType_options}</select></td>
  </tr>
  <tr>
    <td align="right">Project Budget $</td>
    <td>
      <input type="text" name="projectBudget" value="{$project_projectBudget}" size="10">
      <select name="currencyType">{$currencyType_options}</select>
    </td>
  </tr>
  <tr>
    <td align="right">Client</td>
    <td><nobr><select name="clientID" onChange="updateStuffWithAjax()">{$clientOptions}</select>&nbsp; &nbsp;<a href="{$url_alloc_client}">New Client</a></nobr></td>
    <td align="right">Customer Billed At $</td>
    <td><input type="text" name="customerBilledDollars" value="{$project_customerBilledDollars}"></td>
  </tr>
  <tr>
    <td align="right">Client Contact</td>
    <td>
      <div id="clientContactDropdown">
        {$clientContactDropdown}
      </div>
    </td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td align="right"></td>
    <td rowspan="2">{$clientDetails}</td>
    <td align="right">{$cost_centre_label}&nbsp;</td>
    <td>{$cost_centre_bit}&nbsp;</td>
  </tr>
  <tr>
    <td align="right"></td>
    <td align="right">Through an Agency<br>(Payroll Tax Exempt)</td>
    <td><input type="checkbox" name="project_is_agency" value="1"{$project_is_agency}></td>
  </tr>
  <tr>
    <td align="right"></td>
    <td align="right">Target Start/Completion</td>
    <td colspan="2">
      <input type="text" size="11" name="dateTargetStart" value="{$project_dateTargetStart}">
      <input type="button" value="Today" onClick="dateTargetStart.value='{$today}'">
      <input type="text" size="11" name="dateTargetCompletion" value="{$project_dateTargetCompletion}">
      <input type="button" value="Today" onClick="dateTargetCompletion.value='{$today}'">
    </td>
  </tr>

  <tr>
    <td align="right"></td>
    <td align="right">Actual Start/Completion</td>
    <td colspan="2">
      <input type="text" size="11" name="dateActualStart" value="{$project_dateActualStart}">
      <input type="button" value="Today" onClick="dateActualStart.value='{$today}'">
      <input type="text" size="11" name="dateActualCompletion" value="{$project_dateActualCompletion}">
      <input type="button" value="Today" onClick="dateActualCompletion.value='{$today}'">
    </td>
  </tr>

  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
  <tr>
    <td align="center" colspan="5">
      <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
      &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
      <input type="submit" name="delete" value="Delete Record" 
      onClick="return confirm('Are you sure you want to delete this record?')">
    </td>
  </tr>
  <tr>
    <td colspan="4">&nbsp;</td>
  </tr>
</table>

{$table_box}
  <tr>
    <th align="left" colspan="11">Project People</th>
  </tr>
  <tr>
    <td>Person</td>
    <td>Role</td>
    <td>Email Type</td>
    <td>Rate Type</td>
    <td>Rate</td>
    <td>Action</td>
  </tr>
{show_person_list("templates/projectPersonListR.tpl")}
{show_new_person("templates/projectPersonListR.tpl")}
</table>

{$table_box}
  <tr>
    <th colspan="3">Comments</th>
  </tr>
  <tr>
    <td>User</td>
    <td>Date</td>
    <td width="75%">Comment</td>
  </tr>
  {show_comments("templates/projectCommentsR.tpl")}
  <tr>
    <td valign="top">{$project_projectComment_title}</td>
    <td colspan="2">
      <form action="{$url_alloc_project}" method=post>
      <input type="hidden" name="projectID" value="{$project_projectID}">
      <textarea name="projectComment" cols="70" rows="4" wrap="virtual">{$project_projectComment}</textarea><br>{$project_projectComment_buttons}
      </form>
    </td>
  </tr>
</table>

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


{show_attachments()}


{$table_box}
  <tr>
    <th>Uncompleted Tasks</th>
    <th class="right" colspan="3"><a href="{$url_alloc_task}projectID={$project_projectID}">New Task</a></th>
  </tr>
  <tr>
    <td colspan="2">
    {$task_summary}
    </td>
  </tr>
</table>


{$table_box}  
  <tr>
    <th colspan="4">Reminders</th>
    <th class="right">
      <a href="{$url_alloc_reminderAdd}step=3&parentType=project&parentID={$project_projectID}&returnToParent=t">
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
  {show_reminders("../notification/templates/reminderR.tpl")}
  </form>
</table>

{show_time_sheets("templates/projectTimeSheetS.tpl")}
{show_transactions("templates/projectTransactionS.tpl")}

{$table_box}
  <tr>
    <th>Total of all time sheets and transactions: ${$grand_total}</th>
  </tr>
  <tr>
    <th>Project budget: ${$project_projectBudget}</th>
  </tr>
  <tr>
    <th>Percentage used: {$percentage}%</th>
  </tr>
</table>
{show_footer()}
