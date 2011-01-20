{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() {
  id = $("#projectID").attr("value")
  makeAjaxRequest('{$url_alloc_updateParentTasks}projectID='+id, 'parentTaskDropdown')
  makeAjaxRequest('{$url_alloc_updateInterestedParties}projectID='+id+'&taskID={$task_taskID}', 'interestedPartyDropdown')
  makeAjaxRequest('{$url_alloc_updatePersonList}projectID='+id+'&taskID={$task_taskID}', 'taskPersonList')
  makeAjaxRequest('{$url_alloc_updateManagerPersonList}projectID='+id+'&taskID={$task_taskID}', 'taskManagerPersonList')
  {if !$task_taskID}
  makeAjaxRequest('{$url_alloc_updateTaskDupes}', 'taskDupes', { projectID: id, taskName: $("#taskName").attr("value") })
  {/}
}
$(document).ready(function() {
  {if !$task_taskID}
    $('.view').hide();
    $('.edit').show();
    $('#taskName').focus();
  {else}
    $('#editTask').focus();
  {/}
});
</script>

{if $task_taskID}
{$first_div="hidden"}
{page::side_by_side_links(array("task"=>"Main"
                               ,"comments"=>"Comments"
                               ,"reminders"=>"Reminders"
                               ,"attachments"=>"Attachments"
                               ,"history"=>"History"
                               ,"sbsAll"=>"All")
                          ,$url_alloc_task."taskID=".$task_taskID)}
{/}


<div id="task" class="{$first_div}">

<form action="{$url_alloc_task}" method="post">
<input type="hidden" name="taskID" value="{$task_taskID}">
<table class="box">
  <tr>
    <th class="header" colspan="5">{$taskSelfLink}
      <span>{$navigation_links}</span>
    </th>
  </tr>
  <tr>
    <td colspan="5" valign="top">

      <div style="min-width:400px; width:47%; float:left; margin:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>{$task_taskType}{page::mandatory($task_taskName)}</h6>
          <h2 style="margin-bottom:0px; display:inline;">{$taskTypeImage} {$task_taskID} {$task_taskName_html}</h2>&nbsp;{$priorityLabel}
        </div>
        <div class="edit">
          <h6>{$task_taskType}{page::mandatory($task_taskName)}</h6>
          <div style="width:100%" class="">
            <input type="text" id="taskName" name="taskName" value="{$task_taskName_html}" maxlength="75" size="35">
            <select name="priority">
              {$priorityOptions}
            </select>
            <select name="taskTypeID">
              {$taskTypeOptions}
            </select>
            {page::help("taskType")}
          </div>
        </div>

        {if $project_projectName} 
        <div class="view">
          <h6>Project</h6>
          <a href="{$url_alloc_project}projectID={$project_projectID}">{=$project_projectName}</a>
        </div>
        {/}
        <div class="edit">
          <h6>Project</h6>
          <select id="projectID" name="projectID" style="width:100%" onChange="updateStuffWithAjax()"><option value="">{$projectOptions}</select>
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
          {page::textarea("taskDescription",$task_taskDescription,array("height"=>"medium","width"=>"100%"))}
        </div>

        {if !$task_taskID}
        <div class="edit">
          <h6>Possible Duplicates</h6>
          <div class="message" style="padding:4px 2px; width:100%; height:70px; border:1px solid #cccccc; overflow:auto;">
            <div id="taskDupes"></div>
          </div>
        </div>
        {/}

      </div>


      <div style="min-width:400px; width:47%; float:left; margin:0px 12px; vertical-align:top;">

        <div class="view">
          <h6>People<div>Status</div></h6>
          <div style="float:left; width:47%;">
            Created by <b>{=$task_createdBy}</b><br><span class="faint">{$task_dateCreated}</span>
            {if $manager_username}
            <br>
            Managed by <b>{=$manager_username}</b>
            {/}
            {if $person_username}
            <br>
            Assigned to <b>{=$person_username}</b><br><span class="faint">{$task_dateAssigned}</span>
            {/}
            {if $task_closed_by}
            <br>
            Closed by <b>{=$task_closed_by}</b><br><span class="faint">{$task_closed_when}</span>
            <br>
            {/}
          </div>
          <div style="float:right; width:50%; text-align:left;">
            {if $taskStatusLabel}
              <span class="corner" style="display:block;width:10em;padding:5px;margin-top:8px;text-align:center;background-color:{$taskStatusColour};">
              {$taskStatusLabel}
              </span>
            {/}
          </div>
        </div>
        <div class="edit">
          <h6>People<div>Status</div></h6>
          <div style="float:left; width:47%;">
            Managed By <div id="taskManagerPersonList" style="display:inline">{$managerPersonOptions}</div>
            <br><br>
            Assigned To <div id="taskPersonList" style="display:inline">{$personOptions}</div>
          </div>
          <div style="float:right; width:50%; text-align:left;">
            <select name="taskStatus" onChange="$('#closed_duplicate_div').hide(); $('#'+$(this).val()+'_div').css('display','inline');">
              {$taskStatusOptions}
            </select>
            {$class="inline"}
            {if !$task_duplicateTaskID}
              {$class="hidden"}
            {/}
            <div id="closed_duplicate_div" class="{$class}">
              <input type="text" name="duplicateTaskID" value="{$task_duplicateTaskID}" size="10">
              {page::help("task_duplicate")}
            </div>
          </div>
        </div>

        {if $interestedParty_text}
        <div class="view">
          <h6>Interested Parties</h6> 
          {$interestedParty_text}
        </div>
        {/}

        <div class="edit nobr">
          <h6>Interested Parties</h6> 
          <div id="interestedPartyDropdown" style="display:inline">{$interestedPartyOptions}</div>
          {page::help("task_interested_parties")}
        </div>

        {if $task_timeLimit || $task_timeBest || $task_timeWorst || $task_timeExpected || $time_billed_link || ($percentComplete && $percentComplete != "0%")}
        <div class="view">
          <h6>Best / Worst / Expected Hours<div><span style='width:50%; display:inline-block;'>Actual Hours</span><span>Effort Limited To</span></div></h6>
          <div style="float:left; width:40%;">
            {foreach array($task_timeBest,$task_timeWorst,$task_timeExpected) as $i}
              {$div}
              {print imp($i) ? $i : " --- "}
              {$div = " / "}
            {/}
          </div>
          <div style="float:right;width:50%;">
            <span style='width:50%; display:inline-block;'>{$time_billed_link} {if $percentComplete && $percentComplete != "0%"}({$percentComplete}){/}</span>
            <span>{$task_timeLimit} {if $task_timeLimit} hrs{/}</span>
          </div>
        </div>
        {/}

        <div class="edit">
          <h6>Best / Worst / Expected Hours<div><span style='width:50%; display:inline-block;'>Actual Hours</span><span>Effort Limited To</span></div></h6>
          <div style="float:left; width:40%">
            <input type="text" name="timeBest" value="{$task_timeBest}" size="5"> /
            <input type="text" name="timeWorst" value="{$task_timeWorst}" size="5"> /
            <input type="text" name="timeExpected" value="{$task_timeExpected}" size="5"> {page::help("task_estimates")}
          </div>
          <div style="float:right;width:50%;">
            <span style='width:50%; display:inline-block;'>{$time_billed_link} {if $percentComplete && $percentComplete != "0%"}({$percentComplete}){/}</span>
            <span><input type="text" name="timeLimit" value="{$task_timeLimit}" size="5"> {page::help("task_timeLimit")}</span>
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
      <div class="view" style="margin:20px">
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

{/}


</div> <!-- end id=task -->


{if $task_taskID}

<div id="reminders">
<table class="box">
  <tr>
    <th class="header">Reminders
      <span>
        <a href="{$url_alloc_reminderAdd}step=3&parentType=task&parentID={$task_taskID}&returnToParent=task">Add Reminder</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="sortable list">
        <tr>
          <th>Recipient</th>
          <th>Date / Time</th>
          <th>Subject</th>
          <th>Repeat</th>
        </tr>
        {show_reminders("../reminder/templates/reminderR.tpl")}
      </table>
    </td>
  </tr>
</table>
</div>

<div id="attachments">
{show_attachments()}
</div>

<div id="comments">
{show_comments()}
</div>

<div id="history">
{show_taskHistory()}
</div>

{/}

<br>&nbsp;

{page::footer()}
