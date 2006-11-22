{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th>Task Summary</th>
    <th class="right"><nobr><a href="{$url_alloc_task}">New Task</a></nobr></th>
  </tr>
  <tr>
    <td colspan="2">{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2">
      {show_task_list()}
    </td>
  </tr>
</table>
{show_footer()}
