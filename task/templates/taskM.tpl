{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() \{
  id = $("#projectID").attr("value")
  makeAjaxRequest('{$url_alloc_updateParentTasks}projectID='+id, 'parentTaskDropdown')
  makeAjaxRequest('{$url_alloc_updateInterestedParties}projectID='+id+'&taskID={$task_taskID}', 'interestedPartyDropdown')
  makeAjaxRequest('{$url_alloc_updatePersonList}projectID='+id+'&taskID={$task_taskID}', 'taskPersonList')
  makeAjaxRequest('{$url_alloc_updateManagerPersonList}projectID='+id+'&taskID={$task_taskID}', 'taskManagerPersonList')
  {if !$task_taskID}
  makeAjaxRequest('{$url_alloc_updateTaskDupes}', 'taskDupes', \{ projectID: id, taskName: $("#taskName").attr("value") \})
  {/}
\}
$(document).ready(function() \{
  {if !$task_taskID}
    $('.view').hide();
    $('.edit').show();
    $('#taskName').focus();
  {else}
    $('#editTask').focus();
  {/}
\});
</script>
<form action="{$url_alloc_task}" method="post">
<input type="hidden" name="taskID" value="{$task_taskID}">
<table class="box">
  <tr>
    <th class="nobr" colspan="2">{$taskSelfLink}</th>
    <th class="right nobr" colspan="3">{$navigation_links}</th>
  </tr>
  <tr>
    <td colspan="5" valign="top" class="toggle_edit_view">
      <div style="float:left; width:47%; padding:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>{$task_taskType}{page::mandatory($task_taskName)}</h6>
          <h2 style="margin-bottom:0px; display:inline;">{$taskTypeImage} {$task_taskID} {$task_taskName}</h2>&nbsp;{$priorityLabel}
          <br>{$blockerLabel}
        </div>
        <div class="edit nobr">
          <h6>{$task_taskType}{page::mandatory($task_taskName)}</h6>
          <input type="text" id="taskName" name="taskName" value="{$task_taskName_html}" size="35" maxlength="75">
          <select name="priority">
            {$priorityOptions}
          </select>
          <select name="taskTypeID">
            {$taskTypeOptions}
          </select>
          {page::help("taskType")}
          <br>
          <select name="blocker">{$blockerOptions}</select>
        </div>

        {if $project_projectName} 
        <div class="view">
          <h6>Project</h6>
          <a href="{$url_alloc_project}projectID={$project_projectID}">{$project_projectName}</a>
        </div>
        {/}
        <div class="edit">
          <h6>Project</h6>
          <select id="projectID" name="projectID" onChange="updateStuffWithAjax()"><option value="">{$projectOptions}</select>
        </div>

        {if $hierarchy_links} 
        <div class="view">
          <h6>Parent Task</h6>
          {$hierarchy_links}
        </div>
        {/}
        <div class="edit">
          <h6>Parent Task</h6>
          <div id="parentTaskDropdown">{$parentTaskOptions}</div>
        </div>

        {if $task_taskDescription_html}
        <div class="view">
          <h6>Description</h6>
          {$task_taskDescription_html}
        </div>
        {/}

        <div class="edit">
          <h6>Description</h6>
          {page::textarea("taskDescription",$TPL["task_taskDescription"],array("height"=>"medium","width"=>"100%"))}
        </div>

        {if $taskDuplicateLink}
        <div class="view" style="clear:both">
          <h6>Duplicate</h6>
          {$taskDuplicateLink}
        </div>
        {/}
        {if $task_taskID}
        <div class="edit nobr" style="clear:both">
          <h6>Duplicate</h6>
          <input type="text" name="duplicateTaskID" value="{$task_duplicateTaskID}" size="10">
          {page::help("task_duplicate")}
        </div>
        {else}
        <div class="edit">
          <h6>Possible Duplicates</h6>
          <div class="message" style="padding:4px 2px; width:100%; height:70px; border:1px solid #cccccc; overflow:auto;">
            <div id="taskDupes"></div>
          </div>
        </div>
        {/}

      </div>

      <div style="float:right; width:47%; padding:0px 12px; vertical-align:top;">
 
        <div class="view">
          <h6>People</h6>
          Created By <b>{$task_createdBy}</b> {$task_dateCreated}
          {if $manager_username}
          <br><br>
          Managed By <b>{$manager_username}</b>
          {/}
          {if $person_username}
          <br><br>
          Assigned To <b>{$person_username}</b> {$task_dateAssigned}
          {/}
          {if $task_closed_by}
          <br><br>
          Task Closed By <b>{$task_closed_by}</b> {$task_closed_when}
          <br>
          {/}
        </div>

        <div class="edit">
          <h6>People</h6>
          Created By <b>{$task_createdBy}</b> {$task_dateCreated}
          <br><br>
          Managed By <div id="taskManagerPersonList" style="display:inline">{$managerPersonOptions}</div>
          <br><br>
          Assigned To <div id="taskPersonList" style="display:inline">{$personOptions}</div> {$task_dateAssigned}
          {if $task_closed_by}
          <br><br>
          Task Closed By <b>{$task_closed_by}</b> {$task_closed_when}
          <br>
          {/}
          <br><br>
          {if !$task_taskID}
            <label for="createTaskReminder"><input type="checkbox" name="createTaskReminder" id="createTaskReminder" value="true" /> Create reminder for this task</label>{page::help("task_create_reminder")}
          {/}
        </div>

        {if $interestedParty_text}
        <div class="view">
          <h6>Interested Parties</h6> 
          {$interestedParty_text}
        </div>
        {/}

        <div class="edit">
          <h6>Interested Parties</h6> 
          <div id="interestedPartyDropdown" style="display:inline">{$interestedPartyOptions}</div>
          {page::help("task_interested_parties")}
        </div>

        {if $task_timeEstimate || $time_billed_link || ($percentComplete && $percentComplete != "0%")}
        <div class="view">
          <h6>Estimated Hours<div>Actual Hours</div></h6>
          <div style="float:left; width:30%;">
            {$task_timeEstimate} {if $task_timeEstimate} hrs &nbsp;&nbsp;{/}
          </div>
          <div style="float:right;width:50%;">
            {$time_billed_link} {if $percentComplete && $percentComplete != "0%"}({$percentComplete}){/}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Estimated Hours<div>Actual Hours</div></h6>
          <div style="float:left; width:30%">
            <input type="text" name="timeEstimate" value="{$task_timeEstimate}" size="5">
          </div>
          <div style="float:right;width:50%;">
            {$time_billed_link} {if $TPL["percentComplete"] && $TPL["percentComplete"] != "0%"}({$percentComplete}){/}
          </div>
        </div>

        {if $task_dateTargetStart || $task_dateTargetCompletion}
        <div class="view">
          <h6>Estimated Start<div>Estimated Completion</div></h6>
          <div style="float:left; width:30%">
            {$task_dateTargetStart}
          </div>
          <div style="float:right; width:50%">
            {$task_dateTargetCompletion}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Estimated Start<div>Estimated Completion</div></h6>
          <div style="float:left; width:30%">
            {page::calendar("dateTargetStart",$task_dateTargetStart)}
          </div>
          <div style="float:right; width:50%">
            {page::calendar("dateTargetCompletion",$task_dateTargetCompletion)}
          </div>
        </div>

        {if $task_dateActualStart || $task_dateActualCompletion}
        <div class="view">
          <h6>Actual Start<div>Actual Completion</div></h6>
          <div style="float:left; width:30%">
            {$task_dateActualStart}
          </div>
          <div style="float:right; width:50%">
            {$task_dateActualCompletion}
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Actual Start<div>Actual Completion</div></h6>
          <div style="float:left; width:30%">
            {page::calendar("dateActualStart",$task_dateActualStart)}
          </div>
          <div style="float:right; width:50%">
            {page::calendar("dateActualCompletion",$task_dateActualCompletion)}
          </div>
        </div>

       
      </div>

    </td>
  </tr>
  <tr>
    <td colspan="5" align="center" class="padded">
      <div class="view" style="margin-top:20px">
        <input type="button" id="editTask" value="Edit Task" onClick="$('.view').hide();$('.edit').show();">
      </div>
      <div class="edit" style="margin:20px">
          {if !$task_taskID}
          <br>
          <label for="createTaskReminder"><input type="checkbox" name="createTaskReminder" id="createTaskReminder" value="true" /> Create reminder for assignee</label> {page::help("task_create_reminder")}<br><br>
          {/}
        {$timeSheet_save}
        <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
        <input type="submit" name="save_and_new" value="Save &amp; New">
        <input type="submit" name="delete" value="Delete" class="delete_button">
        <input type='hidden' name='view' value='brief'>
        <input type="button" value="Cancel Edit" onClick="$('.edit').hide();$('.view').show();">
      </div>
    </td>
  </tr>
</table>

</form>

{if $task_taskID}

{show_task_children("templates/taskChildrenM.tpl")}

<table class="box">
  <tr>
{if (!$TPL["editing_disabled"])}
    <th>Reminders</th>
    <th class="right" colspan="3"><a href="{$url_alloc_reminderAdd}step=3&parentType=task&parentID={$task_taskID}&returnToParent=task">Add Reminder</a></th>
  </tr>
{else}
    <th colspan="4">Reminders</th>
{/}
  <tr>
    <td>Recipient</td>
    <td>Date / Time</td>
    <td>Subject</td>
    <td>Repeat</td>
  </tr>
  {show_reminders("../reminder/templates/reminderR.tpl")}
</table>


{show_attachments()}

{show_taskHistory()}

{show_taskComments()}

{/}

<br>&nbsp;

{page::footer()}
