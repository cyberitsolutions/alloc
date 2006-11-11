{show_header()}
{show_toolbar()}
<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() {
  obj = document.getElementById("taskCommentForm").taskCommentTemplateID;
  id = obj.options[obj.selectedIndex].value;
  url = '{$url_alloc_updateTaskCommentTemplate}taskCommentTemplateID='+id+'&taskID={$task_taskID}'
  makeAjaxRequest(url,'updateTaskCommentTemplate',1)
}

// Here's the callback function
function updateTaskCommentTemplate(number) {
  if (http_request[number].readyState == 4) {
    if (http_request[number].status == 200) {
      document.getElementById("taskComment").value = http_request[number].responseText;
}
}
}

</script>
<form action="{$url_alloc_task}" method="post">
<input type="hidden" name="taskID" value="{$task_taskID}">
{$table_box}
  <tr>
    <th colspan="2"><nobr>{$task_taskType}</nobr></th>
    <th class="right" colspan="3">
&nbsp;<a href="{$url_alloc_task}taskID={$task_taskID}&view=detail">Edit</a>
&nbsp;<a target="_BLANK" href="{$url_alloc_task}taskID={$task_taskID}&view=printer">Printer</a>
&nbsp;<a href="{$url_alloc_project}projectID={$task_projectID}">Project</a>
&nbsp;{$navigation_links}
    </th>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>  
  <tr>
    <td valign="top" colspan="3" rowspan="2" style="padding-left:15px;" width="50%">

      <table border="0" cellspacing="0" cellpadding="5" class="panel" width="100%">
        <tr>
          <td>{$project_projectName}{$hierarchy_links}<br/>{$task_taskDescription}</td>
        </tr>
      </table>


    </td>
    <td valign="top" rowspan="2" width="50%">
    
      <table border="0" cellspacing="0" cellpadding="5" class="panel" width="100%">
        <tr> 
          <td>Task Created By</td>
          <td><b>{$task_createdBy}</b> {$task_dateCreated}</td> 
        </tr>
        <tr>
          <td>Task Assigned To</td> 
          <td><b>{$person_username}</b> {$task_dateAssigned}</td>
        </tr>
        {$task_closed_info}
        <tr>
          <td valign="top">Interested Parties</td>
          <td valign="top">{$taskCCList_hidden}</td>
        </tr>
        <tr>
          <td colspan="2">&nbsp;</td>
        </tr> 
        <tr>
          <td>Estimated Hours</td>
          <td><input type="text" name="timeEstimate" value="{$task_timeEstimate}" size="5">
            &nbsp;&nbsp;Actual Billed {$task_timeActual}
          </td>
        </tr>  
        <tr>
          <td>Percent</td>
          <td>
            {$percentComplete}
          </td>
        </tr>
        <tr>
          <td><nobr>Target Start/Completion</nobr></td>
          <td>
            <nobr>
              <input type="text" size="11" name="dateTargetStart" value="{$task_dateTargetStart}"><input type="button" value="Today" onClick="dateTargetStart.value='{$today}'">
              <input type="text" size="11" name="dateTargetCompletion" value="{$task_dateTargetCompletion}"><input type="button" value="Today" onClick="dateTargetCompletion.value='{$today}'">   
            </nobr>
          </td>
        </tr>
        <tr>
          <td>Actual Start/Completion</td>
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
    <td colspan="5">&nbsp;</td>
  </tr>
  
  <tr>
    <td colspan="5" align="center">
      {$timeSheet_save}
      
      <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
    <!--  <input type="submit" name="save_and_back" value="Save and Return to Project"> -->
    <!--  <input type="submit" name="save_and_summary" value="Save and Return to Task Summary"> -->
      <input type="submit" name="save_and_new" value="Save &amp; New">
      <input type="submit" name="delete" value="Delete" onClick="return confirm('Are you sure you want to delete this record?')">
      <input type='hidden' name='view' value='brief'>

    </td>
  </tr>
  <tr>
    <td colspan="5">&nbsp;</td>
  </tr>
</table>


{show_task_children("templates/taskChildrenM.tpl")}

{$table_box}
  <tr>
    <th>Reminders</th>
    <th class="right" colspan="3"><a href="{$url_alloc_reminderAdd}step=3&parentType=task&parentID={$task_taskID}&returnToParent=t">Add Reminder</a></th>
  </tr>
  <tr>
    <td>Recipient</td>
    <td>Date / Time</td>
    <td>Subject</td>
    <td>Repeat</td>
  </tr>
  {show_reminders("../notification/templates/reminderR.tpl")}
</table>

</form>

{show_attachments()}

{show_taskComments()}

{show_footer()}
