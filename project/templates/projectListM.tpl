{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="3">Projects</th>
    <th class="right"><a href="{$url_alloc_project}">New Project</a></th>
  </tr>
  <tr>
    <td colspan="4" align="center">{show_filter("templates/projectListFilterS.tpl")}</td>
  </tr>
  <tr>
    <td colspan="4" align="center"></td>
  </tr>
  <tr>
    <td>Name</td>
    <td>Client</td>
    <td>Status</td>
    <td align="center">Action</td>
  </tr>
  {show_project("templates/projectListR.tpl")}
</table>
{show_footer()}
