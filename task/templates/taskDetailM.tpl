{show_header()}
{show_toolbar()}
<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() \{
  obj = document.getElementById("taskform").projectID;
  id = obj.options[obj.selectedIndex].value;
  url = '{$url_alloc_updateParentTasks}projectID='+id
  makeAjaxRequest(url,'updateParentTasks',1)
  url = '{$url_alloc_updateTaskCCList}projectID='+id
  makeAjaxRequest(url,'updateTaskCCList',2)
\}

// Here's the callback function
function updateParentTasks(number) \{
  if (http_request[number].readyState == 4) \{
    if (http_request[number].status == 200) \{
      document.getElementById("parenTaskDropdown").innerHTML = http_request[number].responseText;
    \}
  \}
\}

// Another callback function
function updateTaskCCList(number) \{
  if (http_request[number].readyState == 4) \{
    if (http_request[number].status == 200) \{
      document.getElementById("taskCCListDropdown").innerHTML = http_request[number].responseText;
    \}
  \}
\}

</script>

<form action="{$url_alloc_task}" method="post" id="taskform">
<input type="hidden" name="taskID" value="{$task_taskID}">
<input type="hidden" name="creatorID" value="{$task_creatorID}">
<input type="hidden" name="closerID" value="{$task_closerID}">
<input type="hidden" name="timeSheetID" value="{$timeSheetID}">

{$table_box}
  <tr>
    <th class="nobr">{$task_taskType}</th>
    <th class="right nobr" colspan="3">&nbsp;&nbsp;<a href="{$url_alloc_task}taskID={$task_taskID}&view=brief">View</a>&nbsp;&nbsp;<a target="_BLANK" href="{$url_alloc_task}taskID={$task_taskID}&view=printer">Printer</a>&nbsp;&nbsp;<a href="{$url_alloc_project}projectID={$task_projectID}">Project</a>&nbsp;&nbsp;{$navigation_links}</th>
  </tr>
  <tr>
    <td colspan="4">&nbsp;</td>
  </tr>    
  <tr>
    <td>Project</td>
    <td>
      <select name="projectID" onChange="updateStuffWithAjax()">
        {$projectOptions}
      </select>
    </td>
    <td>Task Type</td>
    <td>
      <select name="taskTypeID">
        {$taskTypeOptions}
      </select>
      {help_button("taskType")}
    </td>
  </tr>

  <tr>
    <td>Task Name</td>
    <td>
      <nobr>
      <input type="text" name="taskName" value="{$task_taskName}" size="50" maxlength="75">
      <select name="priority">
        {$priorityOptions}
      </select>
      </nobr>
    </td>
    <td width="1%"><nobr>Assigned To</nobr></td>
    <td>
      <select name="personID">
        {$personOptions}
      </select>

<input type="hidden" name="dateAssigned" value="{$task_dateAssigned}">
<input type="hidden" name="dateCreated" value="{$task_dateCreated}">
<input type="hidden" name="dateClosed" value="{$task_dateClosed}">
<input type="hidden" name="timeEstimate" value="{$task_timeEstimate}">
</td>
  </tr>
  <tr>
    <td valign="top">Description</td>
    <td colspan="3"><textarea name="taskDescription" rows="7" cols="100" wrap="virtual">{$task_taskDescription}</textarea></td>

  </tr>  
  <tr>
    <td colspan="4"></td>
  </tr>
  <tr>
    <td>Parent Task</td>
    <td colspan="3">
      <div id="parenTaskDropdown">
        {$parentTaskOptions}
      </div>
    </td>
  </tr>

  <tr>    
    <td valign="top"><nobr>Interested Parties</nobr><br/>{$new_client_contact_link}</td>
    <td valign="top" colspan="3">
      <div id="taskCCListDropdown" style="display:inline">
        {$taskCCListOptions}
      </div>
      {help_button("task_interested_parties")}

      <table border="0" cellspacing="0" cellpadding="5" align="right">
        <tr>
          <td><nobr>Target Start/Complete</nobr></td>
          <td>
            <nobr>
              <input type="text" size="11" name="dateTargetStart" value="{$task_dateTargetStart}"><input type="button" value="Today" onClick="dateTargetStart.value='{$today}'">
              <input type="text" size="11" name="dateTargetCompletion" value="{$task_dateTargetCompletion}"><input type="button" value="Today" onClick="dateTargetCompletion.value='{$today}'">
            </nobr>
          </td>
        </tr>
        <tr>
          <td><nobr>Actual Start/Complete</nobr></td>
          <td>
            <nobr>
              <input type="text" size="11" name="dateActualStart" value="{$task_dateActualStart}"><input type="button" value="Today" onClick="dateActualStart.value='{$today}'">
              <input type="text" size="11" name="dateActualCompletion" value="{$task_dateActualCompletion}"><input type="button" value="Today" onClick="dateActualCompletion.value='{$today}'">
            </nobr>
          </td>
        </tr>
      </table>

    </td>
  </tr>

  <tr>
    <td colspan="4">&nbsp;</td>
  </tr>

  <tr>
    <td align="center" colspan="4">
      {$timeSheet_save}
      <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
      <input type="submit" name="save_and_new" value="Save &amp; New">
      <input type="submit" name="delete" value="Delete" onClick="return confirm('Are you sure you want to delete this record?')">
    </td>
  </tr>
  <tr>
   
</table>
</form>
{show_footer()}
