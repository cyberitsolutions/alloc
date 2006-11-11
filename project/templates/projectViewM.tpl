{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th><nobr>Project Details</nobr></th>
    <th class="right">{$navigation_links}</th>
  </tr>
  <tr>
    <td>Name</td>
    <td>{$project_projectName}</td>
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
  
{show_attachments("templates/project_attachmentsM.tpl")}

{$table_box}
  <tr>
    <th colspan="3">Uncompleted Tasks</th>
  </tr>
  <tr>
    <td>{$task_summary}</td>
  </tr>
</table>

{show_time_sheets("templates/projectTimeSheetS.tpl")}

{show_transactions("templates/projectTransactionS.tpl")}

{show_footer()}
