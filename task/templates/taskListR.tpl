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
{if $_FORM["showCreator"]}   <td>{=$creator_name}</td>{/}
{if $_FORM["showManager"]}   <td>{=$manager_name}</td>{/}
{if $_FORM["showAssigned"]}  <td>{=$assignee_name}</td>{/}
{if $_FORM["showDate1"]}     <td class="nobr">{$dateTargetStart}</td>{/}
{if $_FORM["showDate2"]}     <td class="nobr">{$dateTargetCompletion}</td>{/}
{if $_FORM["showDate3"]}     <td class="nobr">{$dateActualStart}</td>{/}
{if $_FORM["showDate4"]}     <td class="nobr">{$dateActualCompletion}</td>{/}
{if $_FORM["showDate5"]}     <td class="nobr">{$dateCreated}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr">{echo seconds_to_display_format($timeBest)}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr">{echo seconds_to_display_format($timeWorst)}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr">{echo seconds_to_display_format($timeExpected)}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr">{echo seconds_to_display_format($timeActual)}</td>{/}
{if $_FORM["showTimes"]}     <td class="nobr{$timeActual > $timeLimit and print ' bad'}">{echo seconds_to_display_format($timeLimit)}</td>{/}
{if $_FORM["showPercent"]}     <td class="nobr">{$percentComplete}</td>{/}
{if $_FORM["showStatus"]}    <td class="nobr" style="width:1%;">
                               <span class="corner" style="display:block;width:10em;padding:5px;text-align:center;background-color:{$taskStatusColour};">
                                 {$taskStatusLabel}
                               </span>
                             </td>{/}
</tr>
