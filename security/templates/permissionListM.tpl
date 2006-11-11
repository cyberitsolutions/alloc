{show_header()}
{show_toolbar()}

{$table_box}
  <tr>
    <th>Permissions</th>
    <th class="right" colspan="8"><a href="{$url_alloc_permission}">New Permission</a></th>
  </tr>
  <tr>
    <td colspan="9" align="center">
      <form action="{$url_alloc_permissionList}" method="post">
      <table class="filter" align="center">
        <tr>
          <td>Table Name</td>
          <td></td>
        </tr>
        <tr>
          <td><input type="text" size="30" name="filter"></td>
          <td><input type="submit" name="submit" value="Filter"></td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <td>Table</td>
    <td>Record ID</td>
    <td>User</td>
    <td>Role</td>
    <td>Actions</td>
    <td>Sort Key</td>
    <td>Allow?</td>
    <td>Comments</td>
    <td>&nbsp;</td>
  </tr>
  {show_permission_list("templates/permissionListR.tpl")}
</table>
{show_footer()}
