{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() {
  id = $("#projectID").val();
  var selectedPerson = $("#taskPersonList select").val();
  var selectedManager = $("#taskManagerPersonList select").val();
  var selectedEstimator = $("#taskManagerPersonList select").val();
  makeAjaxRequest('{$url_alloc_updateParentTasks}projectID='+id, 'parentTaskDropdown');
  makeAjaxRequest('{$url_alloc_updateInterestedParties}projectID='+id+'&taskID={$task_taskID}','interestedPartyDropdown','',1);
  makeAjaxRequest('{$url_alloc_updatePersonList}projectID='+id+'&taskID={$task_taskID}&selected='+selectedPerson,'taskPersonList');
  makeAjaxRequest('{$url_alloc_updateManagerPersonList}projectID='+id+'&taskID={$task_taskID}&selected='+selectedManager,'taskManagerPersonList');
  makeAjaxRequest('{$url_alloc_updateEstimatorPersonList}projectID='+id+'&taskID={$task_taskID}&selected='+selectedEstimator,'taskEstimatorPersonList');
  {if !$task_taskID}
  makeAjaxRequest('{$url_alloc_updateTaskDupes}', 'taskDupes', { projectID: id, taskName: $("#taskName").attr("value") });
  {/}
}
$(document).ready(function() {
  {if !$task_taskID}
    toggle_view_edit();
    $('#taskName').focus();
  {else}
    $('#editTask').focus();
  {/}
  $('#dateTargetStart, #dateTargetCompletion').keydown(function(evt) {
    // ctrl + enter
    if (evt.which == 13 && evt.ctrlKey) {
      var date = new Date();
      // date.print is an extension from the DHTML calendar
      evt.target.value = date.print("%Y-%m-%d");
      evt.preventDefault();
    }
  });

  var prev_taskStatus;
  $('#taskStatus').focus(function() {
    prev_taskStatus = $(this).val();
  }).change(function(evt) {
    $('.hidden_field').hide();
    $('#'+$(this).val()+'_div').css('display','inline');
    if (prev_taskStatus == "pending_tasks" && $(this).val() != "pending_tasks") {
      $("#pendingTasksIDs").val('');
    }
    if ($(this).val() != "pending_tasks" && $(this).val().indexOf("pending_") != -1) {
      $("#pending_reopen_div").css('display','inline');
    }
  });

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
      <span>
        {$navigation_links}
        {page::star("task",$task_taskID)}
      </span>
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
          <h6>Created By<div>Status</div></h6>
          <div style="float:left; width:47%;">
            {=$task_createdBy}&nbsp;&nbsp; <span class="faint">{$task_dateCreated}</span>
          </div>
          <div style="float:right; width:50%;">
            {if $task_taskStatusLabel}
              <span class="corner" style="display:block;width:10em;padding:5px;text-align:center;background-color:{$task_taskStatusColour};">
              {$task_taskStatusLabel}
              </span>
            {/}
          </div>
        </div>
        {if $manager_username || $person_username}
        <div class="enclose">
          <h6>Managed By<div>Assigned To</div></h6>
          <div style="float:left; width:47%;">
            {=$manager_username}
          </div>
          <div style="float:right; width:50%;">
            {=$person_username}&nbsp;&nbsp; <span class="faint">{$task_dateAssigned}</span>
          </div>
        </div>
        {/}
        {if $interestedParties}
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
            <h6>Target Start<div>Target Completion</div></h6>
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
        <button type="button" id="editTask" value="1" onClick="toggle_view_edit();">Edit Task<i class="icon-edit"></i></button>
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
          <table class="nopad">
            <tr>
              <td style="width:100%;padding-right:3px !important;">
                <input type="text" id="taskName" name="taskName" value="{=$task_taskName}" maxlength="75" style="width:100%;">
              </td>
              <td class="nobr" style="padding-right:3px !important;">
                <select name="priority">
                  {$priorityOptions}
                </select>
                <select name="taskTypeID">
                  {$taskTypeOptions}
                </select>
              </td>
              <td>
                {page::help("taskType")}
              </td>
            </tr>
          </table>
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
          <h6>Created By<div>Status</div></h6>
          <div style="float:left; width:47%;">
            {if $task_createdBy}
            {=$task_createdBy}&nbsp;&nbsp; <span class="faint">{$task_dateCreated}</span>
            {/}
          </div>
          <div style="float:right; width:50%; text-align:left;">
            <select name="taskStatus" id="taskStatus">
              {$task_taskStatusOptions}
            </select>
            <div id="closed_duplicate_div" class="hidden_field {print ($task_taskStatus == "closed_duplicate") ? "inline" : "hidden"}">
              <input type="text" name="duplicateTaskID" value="{$task_duplicateTaskID}" size="20">
              {page::help("task_duplicate")}
            </div>
            <div id="pending_tasks_div" class="hidden_field {print ($task_taskStatus == "pending_tasks") ? "inline" : "hidden"}">
              <input type="text" name="pendingTasksIDs" id="pendingTasksIDs" value="{$task_pendingTaskIDs}" size="20">
              {page::help("task_pending_tasks")}
            </div>
            <div id="pending_reopen_div" class="hidden_field {print ($task_taskStatus != "pending_tasks" && in_str("pending_",$task_taskStatus)) ? "inline" : "hidden"}">
              {page::calendar("reopen_task",$reopen_task)}
              {page::help("task_reopen_task")}
            </div>
          </div>
        </div>

        <div class="enclose">
          <h6>Managed By<div>Assigned To</div></h6>
          <div style="float:left; width:47%;">
            <div id="taskManagerPersonList" style="display:inline">{$managerPersonOptions}</div>
          </div>
          <div style="float:right; width:50%; text-align:left;">
            <div id="taskPersonList" style="display:inline">{$personOptions}</div>
          </div>
        </div>

        <div class="nobr">
          <h6>Default Interested Parties</h6> 
          <div id="interestedPartyDropdown" style="display:inline">{$interestedPartyOptions}</div>
          {page::help("task_interested_parties")}
        </div>

        <div class="enclose">
          <h6>Best / Likely / Worst<div>Estimator</div></h6>
          <div style="float:left; width:40%" class="nobr">
            <input type="text" name="timeBest" value="{$task_timeBest}" size="4"> /
            <input type="text" name="timeExpected" value="{$task_timeExpected}" size="4"> /
            <input type="text" name="timeWorst" value="{$task_timeWorst}" size="4"> {page::help("task_estimates")}
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
          <h6>Target Start<div>Target Completion</div></h6>
          <div style="float:left; width:30%">
            {page::calendar("dateTargetStart",$task_dateTargetStart)}
          </div>
          <div style="float:right; width:50%">
            {page::calendar("dateTargetCompletion",$task_dateTargetCompletion)}
          </div>
        </div>

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
        {if !$task_taskID}
        <br>
        <label for="createTaskReminder"><input type="checkbox" name="createTaskReminder" id="createTaskReminder" value="true" /> Create reminder for assignee</label> {page::help("task_create_reminder")}<br><br>
        {/}

        {if $task->can_be_deleted()}
        <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
        {/}

        {if $_GET["timeSheetID"]}
        <button type="submit" name="timeSheet_save" value="1" class="save_button">Save &amp; Return to Time Sheet<i class="icon-arrow-left"></i></button>
        <input type="hidden" name="timeSheetID" value="{$_GET.timeSheetID}">
        {/}

        <button type="submit" name="save_and_new" value="1" class="save_button">Save &amp; New<i class="icon-plus-sign"></i></button>
        {if $task_taskID}
        <button type="submit" name="close_task" value="1" class="save_button">Save &amp; Close<i class="icon-remove-sign"></i></button>
        {/}
        <button type="submit" name="save" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>

        {if $task_taskID}
        <br><br>
        <input type='hidden' name='view' value='brief'>
        <a href="" onClick="return toggle_view_edit(true);">Cancel edit</a>
        {/}

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
        <a href="{$url_alloc_reminder}step=3&parentType=task&parentID={$task_taskID}&returnToParent=task">Add Reminder</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {reminder::get_list_html("task",$task_taskID)}
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
