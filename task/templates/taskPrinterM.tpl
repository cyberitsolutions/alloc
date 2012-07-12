{page::header()}
<style>
  body { background:white; color:black; }
  td.overline { border-top:1px solid #666666; }
  td { text-align:left; }
  table.comments { background:white; border:1px solid #666666; }
  table.comments th { background:white; }
</style>


<table width="100%" cellspacing="0" cellpadding="4"> 
  <tr>
    <td class="overline" width="20%">Client</td>
    <td class="overline" colspan="3">{$client_clientName}&nbsp;</td>
  </tr>
  <tr>
    <td>Contact Name</td>
    <td>{$clientContact_clientContactName}&nbsp;</td>
    <td>Email</td>
    <td>{$clientContact_clientContactEmail}&nbsp;</td>
  </tr>
  <tr>
    <td>Phone</td>
    <td>{$clientContact_clientContactPhone}&nbsp;</td>
    <td>Mobile</td>
    <td>{$clientContact_clientContactMobile}&nbsp;</td>
  </tr>
  <tr>
    <td>Postal Address</td>
    <td>{$client_clientStreetAddressOne} {$cbOne} {$client_clientStateOne} {$client_clientPostcodeOne}</td>
    <td>Street Address</td>
    <td>{$client_clientStreetAddressTwo} {$client_clientSuburbTwo} {$client_clientStateTwo} {$client_clientPostcodeTwo}</td>
  </tr>  
  <tr>
    <td class="overline">Task Name</td>
    <td class="overline" colspan="3">#{$task_taskID} {=$task_taskName}&nbsp;</td>
  </tr>
  <tr>
    <td>Project</td>
    <td colspan="3">{=$project_projectName}&nbsp;</td>
  </tr>
  <tr>
    <td>Parent Task</td>
    <td colspan="3">{=$parentTask_taskName}&nbsp;</td>
  </tr>
  <tr>
    <td valign="top">Description</td>
    <td colspan="3"><pre class="comment">{=$task_taskDescription}</pre>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top">Managed By</td>
    <td colspan="3">{$manager_username}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Created By</td>
    <td class="overline">{$task_createdBy} {$task_dateCreated}&nbsp;</td>
    <td class="overline">Task Type</td>
    <td class="overline">{$taskType}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Assigned To</td>
    <td class="overline">{$person_username} {$task_dateAssigned}</td>
    <td class="overline">Priority</td>
    <td class="overline">{$priority}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Time Estimates</td>
    <td class="overline">{$task_timeBest} / {$task_timeWorst} / {$task_timeExpected}</td>
    <td class="overline">Actual Hours</td>
    <td class="overline">{$time_billed_link}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Effort Limited To</td>
    <td class="overline">{$task_timeLimit}&nbsp;</td>
    <td class="overline">Percent Complete</td>
    <td class="overline">{$percentComplete}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Target Start</td>
    <td class="overline">{$task_dateTargetStart}&nbsp;</td>
    <td class="overline">Target Completion</td>
    <td class="overline">{$task_dateTargetCompletion}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Actual Start</td>
    <td class="overline">{$task_dateActualStart}&nbsp;</td>
    <td class="overline">Actual Completion</td>
    <td class="overline">{$task_dateActualCompletion}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline" valign="top">Reminders</td>
    <td class="overline" colspan="3">
      {reminder::get_list_html("task",$task_taskID)}
    </td>
  </tr>
  <tr>
    <td class="overline" valign="top">Child Tasks</td>
    <td class="overline" colspan="3">{$task_children_summary}&nbsp;</td>
  </tr>
</table>
{show_taskCommentsPrinter()}



{page::footer()}
