{show_header()}
{show_toolbar()}
<form action="{$url_alloc_permission}" method="post">
{$table_box}
  <tr>
    <th><nobr>New Permission - Select Table</nobr></th>
    <th class="right"><a href="{$url_alloc_permissionList}">Return to Permission List</a></th>
  </tr>
  <tr>
    <td width="10%">Table</td>
    <td>
      <select name="tableName">{$tableNameOptions}</select>
      <input type="submit" value="Next" name="next">
    </td>
  </tr>
</table>
<input type="hidden" name="permissionID" value="{$permissionID}">
</form>
{show_footer()}
