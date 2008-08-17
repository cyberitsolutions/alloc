{show_header()}
{show_toolbar()}
<script type="text/javascript" language="javascript">
// Make the XML request thing, specify the callback function 
function updateStuffWithAjax() \{
  id = $("#projectID").attr("value")
  makeAjaxRequest('{$url_alloc_updateParentTasks}projectID='+id, 'parentTaskDropdown')
  makeAjaxRequest('{$url_alloc_updateInterestedParties}projectID='+id+'&taskID={$task_taskID}', 'interestedPartyDropdown')
  makeAjaxRequest('{$url_alloc_updatePersonList}projectID='+id+'&taskID={$task_taskID}', 'taskPersonList')
  makeAjaxRequest('{$url_alloc_updateManagerPersonList}projectID='+id+'&taskID={$task_taskID}', 'taskManagerPersonList')
\}
$(document).ready(function() \{
  {if !$task_taskID}
    $('.view').hide();
    $('.edit').show();
  {/}
\});
</script>
<form action="{$url_alloc_task}" method="post">
<input type="hidden" name="taskID" value="{$task_taskID}">
{$table_box}
  <tr>
    <th class="nobr" colspan="2">{$taskSelfLink}</th>
    <th class="right nobr" colspan="3">{$navigation_links}</th>
  </tr>
  <tr>
    <td colspan="5" valign="top">
      <div style="float:left; width:48%; padding:0px 12px; vertical-align:top;">

        <h6>Task Name</h6>
        <div class="view">
          <h2 style="margin-bottom:0px; display:inline;">{$taskTypeImage} {$taskName_label}</h2>{$priorityLabel}
        </div>
        <div class="edit nobr">
          <input type="text" name="taskName" value="{$task_taskName}" size="40" maxlength="75">
          <select name="priority">
            {$priorityOptions}
          </select>
          <select name="taskTypeID">
            {$taskTypeOptions}
          </select>
          {get_help("taskType")}
        </div>

        {if $project_projectName} 
        <div class="view">
          <h6>Project</h6>
          <a href="{$url_alloc_project}projectID={$project_projectID}">{$project_projectName}</a>
        </div>
        {/}
        <div class="edit">
          <h6>Project</h6>
          <select id="projectID" name="projectID" onChange="updateStuffWithAjax()">{$projectOptions}</select>
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

        <h6>Task Description</h6>
        <div class="view">
          {$task_taskDescription}
        </div>
        <div class="edit">
          {get_textarea("taskDescription",$TPL["task_taskDescription"],array("height"=>"medium","width"=>"100%"))}
        </div>

        {if $taskDuplicateLink}
        <div class="view" style="clear:both">
          <h6>Task Duplicate</h6>
          {$taskDuplicateLink}
        </div>
        {/}
        {if $task_taskID}
        <div class="edit nobr" style="clear:both">
          <h6>Task Duplicate</h6>
          <input type="text" name="duplicateTaskID" value="{$task_duplicateTaskID}" size="10">
          {get_help("task_duplicate")}
        </div>
        {/}

      </div>

      <div style="float:left; width:48%; padding:0px 12px; vertical-align:top;">
 
       <h6>Task People</h6>

        <div class="col1">Created By</div>
        <div class="coln"><b>{$task_createdBy}</b></div>
        <div class="coln">{$task_dateCreated}</div> 

        <div class="col1">Managed By</div>
        <div class="coln view"><b>{$manager_username}</b></div>
        <div class="coln edit"><div id="taskManagerPersonList">{$managerPersonOptions}</div></div>

        <div class="col1">Assigned To</div> 
        <div class="coln view"><b>{$person_username}</b></div>  
        <div class="coln view">{$task_dateAssigned}</div>  
        <div class="coln edit"><div id="taskPersonList">{$personOptions}</div></div>
    
        {if $task_closed_by}
        <div class="col1">Task Closed By</div> 
        <div class="coln"><b>{$task_closed_by}</b></div>
        <div class="coln">{$task_closed_when}</div>
        {/}

        <div class="col1">Interested Parties</div>
        <div class="coln view nobr">{$interestedParty_text}</div>
        <div class="coln edit nobr">
          <div id="interestedPartyDropdown" style="display:inline">
            {$interestedPartyOptions}
          </div>
          {get_help("task_interested_parties")}
        </div>

        <br style="clear:both">
  
        <h6>Estimated Hours</h6>
        <div class="view">
          {$task_timeEstimate}
          &nbsp;&nbsp;{$time_billed_link}
          &nbsp;&nbsp;{if $TPL["percentComplete"] != "" && $TPL["percentComplete"] != "0%"}({$percentComplete}){/}
        </div>
        <div class="edit">
          <input type="text" name="timeEstimate" value="{$task_timeEstimate}" size="5">
        </div>

        <h6>Estimated Date</h6>
        <div class="col1 nobr">Start: </div>
        <div class="coln nobr">
          <div class="view">{$task_dateTargetStart}</div>
          <div class="edit">{get_calendar("dateTargetStart",$task_dateTargetStart)}</div>
        </div>
        <div class="coln nobr">Completion: </div>
        <div class="coln nobr">
          <div class="view">{$task_dateTargetCompletion}</div>
          <div class="edit">{get_calendar("dateTargetCompletion",$task_dateTargetCompletion)}</div>
        </div>

        <br style="clear:both">

        <h6>Actual Date</h6>
        <div class="col1 nobr">Start: </div>
        <div class="coln nobr">
          <div class="view">{$task_dateActualStart}</div>
          <div class="edit">{get_calendar("dateActualStart",$task_dateActualStart)}</div>
        </div>
        <div class="coln nobr">Completion: </div>
        <div class="coln nobr">
          <div class="view">{$task_dateActualCompletion}</div>
          <div class="edit">{get_calendar("dateActualCompletion",$task_dateActualCompletion)}</div>
        </div>

      </div>

    </td>
  </tr>
  <tr>
    <td colspan="5" align="center" class="padded">
      <div class="view">
      <input type="button" value="Edit Task" onClick="$('.view').hide();$('.edit').show();">
      </div>
      <div class="edit" style="margin-top:20px">
      {$timeSheet_save}
      <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
      <input type="submit" name="save_and_new" value="Save &amp; New">
      <input type="submit" name="delete" value="Delete" class="delete_button">
      <input type='hidden' name='view' value='brief'>
      <input type="button" value="Cancel" onClick="$('.edit').hide();$('.view').show();">
      </div>
    </td>
  </tr>
</table>

{if $task_taskID}

{show_task_children("templates/taskChildrenM.tpl")}

{$table_box}
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

</form>

{show_attachments()}

{show_taskComments()}

{/}

<br>&nbsp;

{show_footer()}
