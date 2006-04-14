{:show_header}
<style>
  td.overline { border-top:1px solid #666666; }
</style>
<table cellspacing="0" cellpadding="4"> 
 <tr>
    <td class="overline">Project</td>
    <td class="overline">{projectName}&nbsp;</td>
    <td class="overline">Parent Task</td>
    <td class="overline">{parentTask}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Client</td>
    <td class="overline" colspan="3">{client_clientName}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Contact Name</td>
    <td class="overline">{clientContact_clientContactName}&nbsp;</td>
    <td class="overline">Email</td>
    <td class="overline">{clientContact_clientContactEmail}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Phone</td>
    <td class="overline">{clientContact_clientContactPhone}&nbsp;</td>
    <td class="overline">Mobile</td>
    <td class="overline">{clientContact_clientContactMobile}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Postal Address</td>
    <td class="overline">{client_clientStreetAddressOne}&nbsp;<br>
    {client_clientSuburbOne}<br>{client_clientStateOne} {client_clientPostcodeOne}</td>
    <td class="overline">Street Address</td>
    <td class="overline">{client_clientStreetAddressTwo}&nbsp;<br>
        {client_clientSuburbTwo}<br>
	{client_clientStateTwo} {client_clientPostcodeTwo}</td>
  </tr>  
  <tr>
    <td class="overline">Name</td>
    <td class="overline" colspan="3">{task_taskName}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline" valign="top">Description</td>
    <td class="overline" colspan="3">{task_taskDescription}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Date Created</td>
    <td class="overline" colspan="3">{task_dateCreated}&nbsp;</td>
  </tr>
 <tr>
    <td class="overline">Priority</td>
    <td class="overline">{priority}&nbsp;</td>
    <td class="overline">Task Type</td>
    <td class="overline">{taskType}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Assigned To</td>
    <td class="overline">{person_username}&nbsp;</td>
    <td class="overline">&nbsp;</td>
    <td class="overline">&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Time Estimate</td>
    <td class="overline">{task_timeEstimate}&nbsp;</td>
    <td class="overline">Percent Complete</td>
    <td class="overline">{task_percentComplete}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Target Start</td>
    <td class="overline">{task_dateTargetStart}&nbsp;</td>
    <td class="overline">Target Completion</td>
    <td class="overline">{task_dateTargetCompletion}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline">Actual Start</td>
    <td class="overline">{task_dateActualStart}&nbsp;</td>
    <td class="overline">Actual Completion</td>
    <td class="overline">{task_dateActualCompletion}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline" valign="top">Reminders</td>
    <td class="overline" colspan="3">
        <table border="0" cellspacing="0" cellpadding="2" width="100%">
          {:show_reminders ../notification/templates/reminderR.tpl}
        </table>&nbsp;
    </td>
  </tr>
  <tr>
    <td class="overline" valign="top">Child Tasks</td>
    <td class="overline" colspan="3">{task_children_summary}&nbsp;</td>
  </tr>
  <tr>
    <td class="overline" valign="top">Comments</td>
    <td class="overline" colspan="3">{:show_taskCommentsR templates/taskPrinterCommentsR.tpl}&nbsp;</td>
  </tr>
</table>
{:show_footer}
