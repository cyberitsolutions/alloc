{page::header()}
{page::toolbar()}
{$table_box}
  <tr>
    <th>Projects</th>
    <th class="right"><a href="{$url_alloc_project}">New Project</a></th>
  </tr>
  <tr>
    <td colspan="2" align="center">{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2">
      {show_project_list()}
    </td>
  </tr>
</table>
{page::footer()}
