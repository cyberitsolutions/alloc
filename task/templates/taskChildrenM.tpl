<table class="box">
    <tr>
      <th class="header">Child Tasks
        <span>
          <a href="{$url_alloc_task}projectID={$task_projectID}&parentTaskID={$task_taskID}">New Subtask</a>
        </span>
      </th>
    </tr>
    <tr>
      <td>
        {task::get_list_html($taskListRows,$taskListOptions)}
      </td>
    </tr>
</table>

