{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Projects
      <b> - {print count($projectListRows)} records</b>
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href="{$url_alloc_project}">New Project</a>
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">{show_filter()}</td>
  </tr>
  <tr>
    <td>
      {project::get_list_html($projectListRows,$_FORM)}
    </td>
  </tr>
</table>
{page::footer()}
