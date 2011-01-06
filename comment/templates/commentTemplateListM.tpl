{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Comment Templates
      <span>
        <a href="{$url_alloc_commentTemplate}">New Comment Template</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="list sortable">
        <tr>
          <th width="1%" class="sorttable_numeric">ID</th>
          <th>Template</th>
          <th>Type</th>
        </tr>
        {show_commentTemplate("templates/commentTemplateListR.tpl")}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
