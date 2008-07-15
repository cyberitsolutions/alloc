{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th>Tasks</th>
    <th class="right noprint"><nobr><a href="{$url_alloc_task}">New Task</a></nobr></th>
  </tr>
  <tr>
    <td colspan="2" class="noprint" >{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2">
      {show_task_list()}
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {$task_update_result}
    </td>
  </tr>
</table>
{show_footer()}
