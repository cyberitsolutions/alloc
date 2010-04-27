{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th>Permissions</th>
    <th class="right" colspan="8">
      <a class='magic toggleFilter' href=''>Show Filter</a>
      <a href="{$url_alloc_permission}">New Permission</a>
    </th>
  </tr>
  <tr>
    <td colspan="9" align="center">
      <form action="{$url_alloc_permissionList}" method="get">
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
    <td colspan="9">
      <table class="list sortable">
        <tr>
          <th>Table</th>
          <th>Record ID</th>
          <th>User</th>
          <th>Role</th>
          <th>Actions</th>
          <th>Sort Key</th>
          <th>Allow?</th>
          <th>Comments</th>
          <th>&nbsp;</th>
        </tr>
        {show_permission_list("templates/permissionListR.tpl")}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
