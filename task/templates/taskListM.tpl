{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th class="header">Tasks
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href="{$url_alloc_task}">New Task</a>
      </span>
    </th>
  </tr>
  <tr>
    <td class="noprint" >{show_filter()}</td>
  </tr>
  <tr>
    <td>
      {task::get_list_html($taskListRows,$_FORM)}
    </td>
  </tr>
</table>
{page::footer()}
