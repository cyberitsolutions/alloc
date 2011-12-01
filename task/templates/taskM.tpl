{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() {
  id = $("#projectID").attr("value")
  makeAjaxRequest('{$url_alloc_updateParentTasks}projectID='+id, 'parentTaskDropdown')
  makeAjaxRequest('{$url_alloc_updateInterestedParties}projectID='+id+'&taskID={$task_taskID}', 'interestedPartyDropdown','',1)
  makeAjaxRequest('{$url_alloc_updatePersonList}projectID='+id+'&taskID={$task_taskID}', 'taskPersonList')
  makeAjaxRequest('{$url_alloc_updateManagerPersonList}projectID='+id+'&taskID={$task_taskID}', 'taskManagerPersonList')
  makeAjaxRequest('{$url_alloc_updateEstimatorPersonList}projectID='+id+'&taskID={$task_taskID}', 'taskEstimatorPersonList')
  {if !$task_taskID}
  makeAjaxRequest('{$url_alloc_updateTaskDupes}', 'taskDupes', { projectID: id, taskName: $("#taskName").attr("value") })
  {/}
}
$(document).ready(function() {
  {if !$task_taskID}
    toggle_view_edit();
    $('#taskName').focus();
  {else}
    $('#editTask').focus();
  {/}
});
</script>

<style>
.task_pane {
  min-width:400px;
  width:47%;
  float:left;
  margin:0px 12px;
  vertical-align:top;
}
</style>


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

<table class="box view">
  <tr>
    <th class="header">{$taskSelfLink}
      <span>{$navigation_links}</span>
    </th>
  </tr>
  <tr>
    <td valign="top">
      <div class="task_pane">
        <h6>{$task_taskType}{page::mandatory($task_taskName)}</h6>
        <h2 style="margin-bottom:0px; display:inline;">{$taskTypeImage} {$task_taskID} {=$task_taskName}</h2>&nbsp;{$priorityLabel}
        {if $project_projectName} 
          <h6>Project</h6>
          <a href="{$url_alloc_project}projectID={$project_projectID}">{=$project_projectName}</a>
        {/}
        {if $hierarchy_links} 
          <h6>Parent Task</h6>
          {$hierarchy_links}
        {/}
        {if $task_taskDescription}
          <h6>Description</h6>
          <pre class="comment">{=$task_taskDescription}</pre>
        {/}
      </div>
      <div class="task_pane">
        <div class="enclose">
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
          <div style="float:right; width:50%;">
            {if $task_taskStatusLabel}
              <span class="corner" style="display:block;width:10em;padding:5px;margin-top:8px;text-align:center;background-color:{$task_taskStatusColour};">
              {$task_taskStatusLabel}
              </span>
            {/}
          </div>
        </div>

        {if $interestedParty_text}
          <h6>Interested Parties</h6> 
          {$interestedParty_text}
        {/}
        {if $task_timeBest || $task_timeWorst || $task_timeExpected || $estimator_username}
          <div class="enclose">
            <h6>Best / Likely / Worst<div>Estimator</div></h6>
            <div style="float:left; width:40%;">
              {foreach array($task_timeBest,$task_timeExpected,$task_timeWorst) as $i}
                {$div}
                {print imp($i) ? $i : " --- "}
                {$div = " / "}
              {/}
            </div>
            <div style="float:right;width:50%;">
             {=$estimator_username}
            </div>
          </div>
        {/}
        {if $task_timeLimit || $time_billed_link || ($percentComplete && $percentComplete != "0%")}
          <div class="enclose">
            <h6>Actual Hours<div>Effort Limited To</div></h6>
            <div style="float:left;width:50%;">
              {$time_billed_link} {if $percentComplete && $percentComplete != "0%"}({$percentComplete}){/}
            </div>
            <div style="float:right;width:50%;">
              {$task_timeLimit} {if $task_timeLimit} hrs{/}
            </div>
          </div>
        {/}
        {if $task_dateTargetStart || $task_dateTargetCompletion}
          <div class="enclose">
            <h6>Estimated Start<div>Estimated Completion</div></h6>
            <div style="float:left; width:30%">
              {$task_dateTargetStart}
            </div>
            <div style="float:right; width:50%">
              {$task_dateTargetCompletion}
            </div>
          </div>
        {/}

        {if $task_dateActualStart || $task_dateActualCompletion}
          <div class="enclose">
            <h6>Actual Start<div>Actual Completion</div></h6>
            <div style="float:left; width:30%">
              {$task_dateActualStart}
            </div>
            <div style="float:right; width:50%">
              {$task_dateActualCompletion}
            </div>
          </div>
        {/}
      </div>
    </td>
  </tr>
  <tr>
    <td align="center" class="padded">
      <div style="margin:20px">
        <input type="button" id="editTask" value="Edit Task" onClick="toggle_view_edit();">
      </div>
    </td>
  </tr>
</table>

<table class="box edit">
  <tr>
    <th class="header">{$taskSelfLink}
      <span>{$navigation_links}</span>
    </th>
  </tr>
  <tr>
    <td valign="top">
      <div class="task_pane">
        <h6>{$task_taskType}{page::mandatory($task_taskName)}</h6>
        <div style="width:100%" class="">
          <input type="text" id="taskName" name="taskName" value="{=$task_taskName}" maxlength="75" size="35">
          <select name="priority">
            {$priorityOptions}
          </select>
          <select name="taskTypeID">
            {$taskTypeOptions}
          </select>
          {page::help("taskType")}
        </div>

        <h6>Project</h6>
        <select id="projectID" name="projectID" style="width:100%" onChange="updateStuffWithAjax()"><option value="">{$projectOptions}</select>

        <h6>Parent Task</h6>
        <div id="parentTaskDropdown">{$parentTaskOptions}</div>

        <h6>Description</h6>
        {page::textarea("taskDescription",$task_taskDescription,array("height"=>"medium","width"=>"100%"))}

        {if !$task_taskID}
          <h6>Possible Duplicates</h6>
          <div class="message" style="padding:4px 2px; width:100%; height:70px; border:1px solid #cccccc; overflow:auto;">
            <div id="taskDupes"></div>
          </div>
        {/}
      </div>
      <div class="task_pane">
        <div class="enclose">
          <h6>People<div>Status</div></h6>
          <div style="float:left; width:47%;">
            Managed By <div id="taskManagerPersonList" style="display:inline">{$managerPersonOptions}</div>
            <br><br>
            Assigned To <div id="taskPersonList" style="display:inline">{$personOptions}</div>
          </div>
          <div style="float:right; width:50%; text-align:left;">
            <select name="taskStatus" onChange="$('#closed_duplicate_div').hide(); $('#'+$(this).val()+'_div').css('display','inline');">
              {$task_taskStatusOptions}
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

        <div class="nobr">
          <h6>Interested Parties</h6> 
          <div id="interestedPartyDropdown" style="display:inline">{$interestedPartyOptions}</div>
          {page::help("task_interested_parties")}
        </div>

        <div class="enclose">
          <h6>Best / Likely / Worst<div>Estimator</div></h6>
          <div style="float:left; width:40%">
            <input type="text" name="timeBest" value="{$task_timeBest}" size="4"> /
            <input type="text" name="timeExpected" value="{$task_timeExpected}" size="5"> /
            <input type="text" name="timeWorst" value="{$task_timeWorst}" size="5"> {page::help("task_estimates")}
          </div>
          <div style="float:right;width:50%;" id="taskEstimatorPersonList">
            {$estimatorPersonOptions}
          </div>
        </div>

        <div class="enclose">
          <h6>Actual Hours<div>Effort Limited To</div></h6>
          <div style="float:left;width:50%;">
            {$time_billed_link} {if $percentComplete && $percentComplete != "0%"}({$percentComplete}){/}
          </div>
          <div style="float:right;width:50%;">
            <input type="text" name="timeLimit" value="{$task_timeLimit}" size="5"> {page::help("task_timeLimit")}
          </div>
        </div>

        <div class="enclose">
          <h6>Estimated Start<div>Estimated Completion</div></h6>
          <div style="float:left; width:30%">
            {page::calendar("dateTargetStart",$task_dateTargetStart)}
          </div>
          <div style="float:right; width:50%">
            {page::calendar("dateTargetCompletion",$task_dateTargetCompletion)}
          </div>
        </div>

        <div class="enclose">
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
    <td align="center" class="padded">
      <div style="margin:20px">
          {if !$task_taskID}
          <br>
          <label for="createTaskReminder"><input type="checkbox" name="createTaskReminder" id="createTaskReminder" value="true" /> Create reminder for assignee</label> {page::help("task_create_reminder")}<br><br>
          {/}
          {if $_GET["timeSheetID"]}
          <input type="submit" name="timeSheet_save" value="Save and Return to Time Sheet">
          <input type="hidden" name="timeSheetID" value="{$_GET.timeSheetID}">
          {/}
        <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
        <input type="submit" name="save_and_new" value="Save &amp; New">
        <input type="submit" name="delete" value="Delete" class="delete_button">
        <input type='hidden' name='view' value='brief'>
        <input type="button" value="Cancel Edit" onClick="toggle_view_edit();">
      </div>
    </td>
  </tr>
</table>

<input type="hidden" name="sessID" value="{$sessID}">
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
