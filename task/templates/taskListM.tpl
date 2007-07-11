{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th>Tasks</th>
    <th class="right noprint"><nobr><a target="_BLANK"
    href="{$url_alloc_taskList}&media=print">Printer</a>&nbsp;&nbsp;<a href="{$url_alloc_task}">New Task</a></nobr></th>
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
{show_footer()}
