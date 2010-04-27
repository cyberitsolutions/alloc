{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th>Tasks</th>
    <th class="right noprint">
      <a class='magic toggleFilter' href=''>Show Filter</a>
      <a href="{$url_alloc_task}">New Task</a>
    </th>
  </tr>
  <tr>
    <td colspan="2" class="noprint" >{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2">
      {show_task_list()}
    </td>
  </tr>
</table>
{page::footer()}
