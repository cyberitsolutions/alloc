{show_header()}
{show_toolbar()}
<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() \{
  obj = document.getElementById("taskform").projectID;
  id = obj.options[obj.selectedIndex].value;

  document.getElementById("parenTaskDropdown").innerHTML = '<img src="{$url_alloc_images}ticker2.gif" alt="Updating field..." title="Updating field...">';
  url = '{$url_alloc_updateParentTasks}projectID='+id
  makeAjaxRequest(url,'callbackReceiver',1,'parenTaskDropdown')

  document.getElementById("taskCCListDropdown").innerHTML = '<img src="{$url_alloc_images}ticker2.gif" alt="Updating field..." title="Updating field...">';
  url = '{$url_alloc_updateTaskCCList}projectID='+id+'&taskID={$task_taskID}';
  makeAjaxRequest(url,'callbackReceiver',2,'taskCCListDropdown')

  document.getElementById("taskPersonList").innerHTML = '<img src="{$url_alloc_images}ticker2.gif" alt="Updating field..." title="Updating field...">';
  url = '{$url_alloc_updatePersonList}projectID='+id+'&taskID={$task_taskID}';
  makeAjaxRequest(url,'callbackReceiver',3,'taskPersonList')

  document.getElementById("taskManagerPersonList").innerHTML = '<img src="{$url_alloc_images}ticker2.gif" alt="Updating field..." title="Updating field...">';
  url = '{$url_alloc_updateManagerPersonList}projectID='+id+'&taskID={$task_taskID}';
  makeAjaxRequest(url,'callbackReceiver',4,'taskManagerPersonList')
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
    <th class="right nobr" colspan="3">&nbsp;&nbsp;<a href="{$url_alloc_task}taskID={$task_taskID}&view=brief">View</a>&nbsp;&nbsp;{$navigation_links}</th>
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

    <td width="1%"><nobr>Managed By</nobr></td>
    <td>
      <div id="taskManagerPersonList">
        {$managerPersonOptions}
      </div>
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
      <div id="taskPersonList">
        {$personOptions}
      </div>
<input type="hidden" name="dateAssigned" value="{$task_dateAssigned}">
<input type="hidden" name="dateCreated" value="{$task_dateCreated}">
<input type="hidden" name="dateClosed" value="{$task_dateClosed}">
<input type="hidden" name="timeEstimate" value="{$task_timeEstimate}">
</td>
  </tr>
  <tr>
    <td valign="top">Description</td>
    <td colspan="3"><textarea name="taskDescription" rows="7" cols="85" wrap="virtual">{$task_taskDescription}</textarea></td>

  </tr>  
  <tr>
    <td colspan="4"></td>
  </tr>
  <tr>
    <td>Parent Task</td>
    <td>
      <div id="parenTaskDropdown">
        {$parentTaskOptions}
      </div>
    </td>
    <td>Task Type</td>
    <td>
      <select name="taskTypeID">
        {$taskTypeOptions}
      </select>
      {get_help("taskType")}
    </td>
  </tr>

  <tr>    
    <td valign="top"><nobr>Interested Parties</nobr></td>
    <td valign="top" colspan="3">
      <div id="taskCCListDropdown" style="display:inline">
        {$taskCCListOptions}
      </div>
      {get_help("task_interested_parties")}

      <div>
      <table border="0" cellspacing="0" cellpadding="5" align="right">
        <tr>
          <td><nobr>Target Start/Complete</nobr></td>
          <td>
            <nobr>
              {get_calendar("dateTargetStart",$TPL["task_dateTargetStart"])}
              {get_calendar("dateTargetCompletion",$TPL["task_dateTargetCompletion"])}
            </nobr>
          </td>
        </tr>
        <tr>
          <td><nobr>Actual Start/Complete</nobr></td>
          <td>
            <nobr>
              {get_calendar("dateActualStart",$TPL["task_dateActualStart"])}
              {get_calendar("dateActualCompletion",$TPL["task_dateActualCompletion"])}
            </nobr>
          </td>
        </tr>
      </table>
      </div>

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
