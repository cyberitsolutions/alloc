{if $taskListRows}

<!-- Header -->
{if $_FORM["showEdit"]}<form action="{$_FORM["url_form_action"]}" method="post">{/}
<table class="list sortable">
  <tr>
  {if $_FORM["showEdit"]}
    <th width="1%" class="sorttable_nosort noprint"> <!-- checkbox toggler -->
      <input type="checkbox" class="toggler">
    </th>
  {/}
    <th width="1%"></th> <!-- taskTypeImage -->
  {if $_FORM["showTaskID"]}<th class="sorttable_numeric" width="1%">ID</th>{/}
    <th>Task</th>
  {if $_FORM["showProject"]}<th>Project</th>{/}
  {if $_FORM["showPriority"]}<th class="sorttable_numeric">Priority</th>{/}
  {if $_FORM["showPriority"]}<th>Task Pri</th>{/}
  {if $_FORM["showPriority"]}<th>Proj Pri</th>{/}
  {if $_FORM["showCreator"]}<th>Task Creator</th>{/}
  {if $_FORM["showManager"]}<th>Task Manager</th>{/}
  {if $_FORM["showAssigned"]}<th>Assigned To</th>{/}
  {if $_FORM["showDate1"]}<th>Targ Start</th>{/}
  {if $_FORM["showDate2"]}<th>Targ Compl</th>{/}
  {if $_FORM["showDate3"]}<th>Act Start</th>{/}
  {if $_FORM["showDate4"]}<th>Act Compl</th>{/}
  {if $_FORM["showDate5"]}<th>Task Created</th>{/}
  {if $_FORM["showTimes"]}<th>Best</th>{/}
  {if $_FORM["showTimes"]}<th>Likely</th>{/}
  {if $_FORM["showTimes"]}<th>Worst</th>{/}
  {if $_FORM["showTimes"]}<th>Actual</th>{/}
  {if $_FORM["showTimes"]}<th>Limit</th>{/}
  {if $_FORM["showPercent"]}<th>%</th>{/}
  {if $_FORM["showStatus"]}<th>Status</th>{/}
  {if $_FORM["showEdit"] || $_FORM["showStarred"]}<th width="1%" style="font-size:120%"><i class="icon-star"></i></th>{/}
  </tr>
  
  <!-- Rows -->
  {$n = date("Y-m-d")}
  {foreach $taskListRows as $r}
  <tr class="clickrow" id="clickrow_{$r.taskID}">
  {if $_FORM["showEdit"]}      <td class="nobr noprint"><input type="checkbox" id="checkbox_{$r.taskID}" name="select[{$r.taskID}]" class="task_checkboxes"></td>{/}
                               <td sorttable_customkey="{$r.taskTypeID}">{$r.taskTypeImage}</td>
  {if $_FORM["showTaskID"]}    <td>{$r.taskID}</td>{/}
                               <td style="padding-left:{echo $r["padding"]*25+6}px">{$r.taskLink}&nbsp;&nbsp;{$r.newSubTask}
  {if $_FORM["showDescription"]}<br>{=$r.taskDescription}{/}
  {if $_FORM["showComments"] && $r["comments"]}<br>{$r.comments}{/}
                               </td>
  {if $_FORM["showProject"]}   <td><a href="{$url_alloc_project}projectID={$r.projectID}">{=$r.project_name}</a></td>{/}
  {if $_FORM["showPriority"]}  <td>{$r.priorityFactor}</td>{/}
  {if $_FORM["showPriority"]}  <td style="color:{echo $taskPriorities[$r["priority"]]["colour"]}">{echo $taskPriorities[$r["priority"]]["label"]}</td>{/}
  {if $_FORM["showPriority"]}  <td style="color:{echo $projectPriorities[$r["projectPriority"]]["colour"]}">{echo $projectPriorities[$r["projectPriority"]]["label"]}</td>{/}
  {if $_FORM["showCreator"]}   <td>{=$r.creator_name}</td>{/}
  {if $_FORM["showManager"]}   <td>{=$r.manager_name}</td>{/}
  {if $_FORM["showAssigned"]}  <td>{=$r.assignee_name}</td>{/}
  {$dts = $r["dateTargetStart"]; $dtc = $r["dateTargetCompletion"]; $das = $r["dateActualStart"]; $dac = $r["dateActualCompletion"];}
  {unset($dts_style)}
  {$dts == $n   and $dts_style = 'color:green'}
  {$dts && $das > $dts and $dts_style = 'color:red'}
  {unset($dtc_style)}
  {$dtc == $n   and $dtc_style = 'color:green'}
  {$dtc && $dac > $dtc and $dtc_style = 'color:red'}
  {if $_FORM["showDate1"]}     <td class="nobr" style="{$dts_style}">{$dts}</td>{/}
  {if $_FORM["showDate2"]}     <td class="nobr" style="{$dtc_style}">{$dtc}</td>{/}
  {if $_FORM["showDate3"]}     <td class="nobr">{$das}</td>{/}
  {if $_FORM["showDate4"]}     <td class="nobr">{$dac}</td>{/}
  {if $_FORM["showDate5"]}     <td class="nobr">{$r.dateCreated}</td>{/}
  {if $_FORM["showTimes"]}     <td class="nobr">{$r.timeBestLabel}</td>{/}
  {if $_FORM["showTimes"]}     <td class="nobr">{$r.timeExpectedLabel}</td>{/}
  {if $_FORM["showTimes"]}     <td class="nobr">{$r.timeWorstLabel}</td>{/}
  {if $_FORM["showTimes"]}     <td class="nobr">{$r.timeActualLabel}</td>{/}
  {if $_FORM["showTimes"]}     <td class="nobr{$r["timeActual"] > $r["timeLimit"] and print ' bad'}">{$r.timeLimitLabel}</td>{/}
  {if $_FORM["showPercent"]}     <td class="nobr">{$r.percentComplete}</td>{/}
  {if $_FORM["showStatus"]}    <td class="nobr" style="width:1%;">
                                 <span class="corner" style="display:block;width:10em;padding:5px;text-align:center;background-color:{$r.taskStatusColour};">
                                   {$r.taskStatusLabel}
                                 </span>
                               </td>{/}
  {if $_FORM["showEdit"] || $_FORM["showStarred"]}
    <td width="1%">
      {page::star("task",$r["taskID"])}
    </td>
  {/}

  </tr>
  {/}

  <!-- Footer -->

  {if $_FORM["showEdit"]}
  {$person_options = page::select_options(person::get_username_list())}
  {$taskType = new meta("taskType")}
  {$taskType_array = $taskType->get_assoc_array("taskTypeID","taskTypeID")}
  <tfoot>
    <tr>
      <th colspan="26" class="nobr noprint" style="padding:2px;">
        <span style="margin-right:5px;">
          <select name="update_action" onChange="$('.hidden').hide();$('#'+$(this).val()+'_span').show();$('#mass_update').show();"> 
            <option value="">Modify Checked...
            <option value="personID">Assign to --&gt;
            <option value="managerID">Manager to --&gt;
            <option value="timeLimit">Limit to --&gt;
            <option value="timeBest">Best to --&gt;
            <option value="timeWorst">Worst to --&gt;
            <option value="timeExpected">Expected to --&gt;
            <option value="priority">Task Priority to --&gt;
            <option value="taskTypeID">Task Type to --&gt;
            <option value="dateTargetStart">Target Start Date to --&gt;
            <option value="dateTargetCompletion">Target Completion Date to --&gt;
            <option value="dateActualStart">Actual Start Date to --&gt;
            <option value="dateActualCompletion">Actual Completion Date to --&gt;
            <option value="projectIDAndParentTaskID">Project and Parent Task to --&gt;
            <option value="taskStatus">Task Status to --&gt;
          </select>
        </span>
        <span class="hidden" id="dateTargetStart_span">{page::calendar("dateTargetStart")}</span>
        <span class="hidden" id="dateTargetCompletion_span">{page::calendar("dateTargetCompletion")}</span>
        <span class="hidden" id="dateActualStart_span">{page::calendar("dateActualStart")}</span>
        <span class="hidden" id="dateActualCompletion_span">{page::calendar("dateActualCompletion")}</span>
        <span class="hidden" id="personID_span"><select name="personID"><option value="">{$person_options}</select></span>
        <span class="hidden" id="managerID_span"><select name="managerID"><option value="">{$person_options}</select></span>
        <span class="hidden" id="timeLimit_span"><input name="timeLimit" type="text" size="5"></span>
        <span class="hidden" id="timeBest_span"><input name="timeBest" type="text" size="5"></span>
        <span class="hidden" id="timeWorst_span"><input name="timeWorst" type="text" size="5"></span>
        <span class="hidden" id="timeExpected_span"><input name="timeExpected" type="text" size="5"></span>
        <span class="hidden" id="priority_span"><select name="priority">{echo task::get_task_priority_dropdown(3)}</select></span>
        <span class="hidden" id="taskTypeID_span"><select name="taskTypeID">{page::select_options($taskType_array)}</select></span>
        <span class="hidden" id="projectIDAndParentTaskID_span">
          <select name="projectID" id="projectID" 
                  onChange="makeAjaxRequest('{$url_alloc_updateParentTasks}projectID='+$(this).val(),'parentTaskDropdown')">
            <option value="">
            {echo task::get_project_options()}
          </select>
          <span style="display:inline" id="parentTaskDropdown"></span>
        </span>
        <span class="hidden" id="taskStatus_span"><select name="taskStatus">{page::select_options(task::get_task_statii_array(true))}</select></span>
        <button type="submit" id="mass_update" name="mass_update" value="1" class="hidden save_button" style="margin-left:5px;text-transform:none !important;">Update Tasks<i class="icon-ok-sign"></i></button>
      </th>
    </tr>
  </tfoot>
  <input type="hidden" name="sessID" value="{$sessID}">
  </form>
  {/}

</table>


{else}
  <b>No Tasks Found</b>
{/}

