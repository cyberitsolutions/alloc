<style>
  td { border-top:1px solid black; }
  td.print-label { border-right:1px solid black; border-left: 1px solid black; vertical-align:top;} 
</style>
<table cellspacing="0" cellpadding="4"> 
 <tr>
    <td>Project</td>
    <td>{projectName}&nbsp;</td>
    <td>Parent Task</td>
    <td>{parentTask}&nbsp;</td>
  </tr>
  <tr>
    <td>Client</td>
    <td colspan="3">{client_clientName}&nbsp;</td>
  </tr>
    <td>Contact Name</td>
    <td>{clientContact_clientContactName}&nbsp;</td>
    <td>Email</td>
    <td>{clientContact_clientContactEmail}&nbsp;</td>
  <tr>
    <td>Phone</td>
    <td>{clientContact_clientContactPhone}&nbsp;</td>
    <td>Mobile</td>
    <td>{clientContact_clientContactMobile}&nbsp;</td>
  </tr>
    <td>Postal Address</td>
    <td>{client_clientStreetAddressOne}&nbsp;<br>
    {client_clientSuburbOne}<br> 
    {client_clientStateOne} {client_clientPostcodeOne}</td>
    <td>Street Address</td>
    <td>{client_clientStreetAddressTwo}&nbsp;<br>
        {client_clientSuburbTwo}<br>
	{client_clientStateTwo} {client_clientPostcodeTwo}</td>
  </tr>  
  <tr>
    <td>Name</td>
    <td colspan="3">{task_taskName}&nbsp;</td>
  </tr>
  <tr>
    <td>Description</td>
    <td colspan="3">{task_taskDescription}&nbsp;</td>
  </tr>
  <tr>
    <td>Date Created</td>
    <td colspan="3">{task_dateCreated}&nbsp;</td>
  </tr>
 <tr>
    <td>Priority</td>
    <td>{priority}&nbsp;</td>
    <td>Task Type</td>
    <td>{taskType}&nbsp;</td>
  </tr>
  <tr>
    <td>Assigned To</td>
    <td>{person_username}&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>Time Estimate (Hours)</td>
    <td>{task_timeEstimate}&nbsp;</td>
    <td>Percent Complete</td>
    <td>{task_percentComplete}&nbsp;</td>
  </tr>
  <tr>
    <td>Target Start</td>
    <td>{task_dateTargetStart}&nbsp;</td>
    <td>Actual Start</td>
    <td>{task_dateActualStart}&nbsp;</td>
  </tr>
  <tr>
    <td>Target Completion</td>
    <td>{task_dateTargetCompletion}&nbsp;</td>
    <td>Actual Completion</td>
    <td>{task_dateActualCompletion}&nbsp;</td>
  </tr>
  <tr>
    <td>Reminders</td>
    <td colspan="3">
        <table border="0" cellspacing="0" cellpadding="2" width="100%">
          {:show_reminders ../notification/templates/reminderR.tpl}
        </table>&nbsp;
    </td>
  </tr>
  <tr>
    <td>Comments</td>
    <td colspan="3">{:show_comments templates/taskPrinterCommentsR.tpl}&nbsp;</td>
  </tr>
  <tr>
    <td>Child Tasks</td>
    <td colspan="3">{task_children_summary}&nbsp;</td>
  </tr>
</table>
