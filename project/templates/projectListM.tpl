{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Projects
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
      {show_project_list()}
    </td>
  </tr>
</table>
{page::footer()}
