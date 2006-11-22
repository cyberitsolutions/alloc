{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="2">Project Task Graph</th>
  </tr>
  <tr>
    <td colspan="2">{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2"><img src="{$url_alloc_projectGraph}{$FORM}" alt="Project Graph"></td>
  </tr>
  <tr>
    <td colspan="2">{show_task_list()}</td>
  </tr>
</table>
{show_footer()}
