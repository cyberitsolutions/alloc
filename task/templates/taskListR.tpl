{if $dateTargetStart      == date("Y-m-d")}{$dateTargetStart      = "<b>".$dateTargetStart."</b>"}{/}
{if $dateTargetCompletion == date("Y-m-d")}{$dateTargetCompletion = "<b>".$dateTargetCompletion."</b>"}{/}
{if $dateActualStart      == date("Y-m-d")}{$dateActualStart      = "<b>".$dateActualStart."</b>"}{/}
{if $dateActualCompletion == date("Y-m-d")}{$dateActualCompletion = "<b>".$dateActualCompletion."</b>"}{/}

<tr class="clickrow" id="clickrow_{$taskID}">
{if $_FORM["showEdit"]}      <td class="nobr noprint"><input type="checkbox" id="checkbox_{$taskID}" name="select[{$taskID}]" class="task_checkboxes"></td>{/}
                             <td sorttable_customkey="{$taskTypeID}">{$taskTypeImage}</td>
{if $_FORM["showTaskID"]}    <td>{$taskID}</td>{/}
                             <td style="padding-left:{echo $padding*25+6}px">{$taskLink}&nbsp;&nbsp;{$newSubTask}{$str}</td>
{if $_FORM["showProject"]}   <td><a href="{$url_alloc_project}projectID={$projectID}">{=$project_name}</a></td>{/}
{if $_FORM["showPriority"]}  <td>{echo sprintf("%0.2f",$priorityFactor)}</td>{/}
{if $_FORM["showPriority"]}  <td style="color:{$_FORM.taskPriorities.$priority.colour}">{$_FORM.taskPriorities.$priority.label}</td>{/}
{if $_FORM["showPriority"]}  <td style="color:{$_FORM.projectPriorities.$projectPriority.colour}">{$_FORM.projectPriorities.$projectPriority.label}</td>{/}
{if $_FORM["showDateStatus"]}<td>{$taskDateStatus}</td>{/}
{if $_FORM["showCreator"]}   <td>{=$_FORM.people_cache.$creatorID.name}</td>{/}
{if $_FORM["showManager"]}   <td>{=$_FORM.people_cache.$managerID.name}</td>{/}
{if $_FORM["showAssigned"]}  <td>{=$_FORM.people_cache.$personID.name}</td>{/}
{if $_FORM["showDate1"]}     <td class="nobr">{$dateTargetStart}</td>{/}
{if $_FORM["showDate2"]}     <td class="nobr">{$dateTargetCompletion}</td>{/}
{if $_FORM["showDate3"]}     <td class="nobr">{$dateActualStart}</td>{/}
{if $_FORM["showDate4"]}     <td class="nobr">{$dateActualCompletion}</td>{/}
{if $_FORM["showDate5"]}     <td class="nobr">{$dateCreated}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr">{echo seconds_to_display_format($timeEstimate)}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr">{echo seconds_to_display_format(task::get_time_billed($taskID))}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr">{$percentComplete}</td>{/}
{if $_FORM["showStatus"]}    <td class="nobr" style="width:1%; {$taskStatusColour}">{$taskStatusLabel}</td>{/}
</tr>
