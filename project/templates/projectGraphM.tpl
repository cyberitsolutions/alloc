{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th colspan="2">Project Task Graph</th>
  </tr>
  <tr>
    <td colspan="2">{show_filter()}</td>
  </tr>
  <tr>
    <td colspan="2" align="center">{show_projects("templates/projectGraphR.tpl")}</td>
  </tr>
</table>
{page::footer()}
