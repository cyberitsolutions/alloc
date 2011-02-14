<table class="box">
  <tr>
    <th class="header">Uncompleted Tasks
      <b> - {print count($taskListRows)} records</b>
      <span>
        <a href="{$url_alloc_task}projectID={$project_projectID}">New Task</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {task::get_list_html($taskListRows,$_FORM)}
    </td>
  </tr>
</table>
