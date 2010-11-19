{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th><nobr>Project: {$projectSelfLink} </nobr></th>
    <th class="right">{$navigation_links}</th>
  </tr>
  <tr>
    <td>Name</td>
    <td>{$project_projectName}</td>
  </tr>
  <tr>
    <td>Priority</td>
    <td>{$priorityLabel}</td>
  </tr>
  <tr>
    <td>Client</td>
    <td>{$client_clientName}</td>
  </tr>
  <tr>
    <td>Comments</td>
    <td>{$project_projectComments}</td>
  </tr>
</table>

{show_project_managers("templates/projectPersonSummaryViewS.tpl")}
{show_time_sheets("templates/projectTimeSheetS.tpl")}
{show_tasks()}
{show_comments()}
{page::footer()}
