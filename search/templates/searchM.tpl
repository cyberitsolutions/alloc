{:show_header}
{:show_toolbar}
<form action="{url_alloc_search}" method="post">
{table_box}
  <tr>
    <th>Global Search</th>
  </tr>
  <tr>
    <td align="center">


      <table class="filter" align="center">
        <tr>
          <td><input size="30" name="needle" value="{needle}"></td>
          <td><select size="1" name="category">{category_options}</select></td>
          <td><input type="submit" name="search" value="Search"></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      {:search_projects templates/searchProjectR.tpl}
      {:search_clients templates/searchClientR.tpl}
      {:search_tasks templates/searchTaskR.tpl}
      {:search_taskID templates/searchTaskR.tpl}
      {:search_announcements templates/searchAnnouncementR.tpl}
      {:search_items templates/searchItemR.tpl}
    </td>
  </tr>
  </table>

</form>



{:show_footer}

