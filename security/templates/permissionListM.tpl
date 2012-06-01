{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th class="header">Permissions
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href="{$url_alloc_permission}">New Permission</a>
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">
      <form action="{$url_alloc_permissionList}" method="get">
      <table class="filter corner" align="center">
        <tr>
          <td>Table Name</td>
          <td></td>
        </tr>
        <tr>
          <td><input type="text" size="30" name="filter"></td>
          <td>
            <button type="submit" name="submit" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
          </td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
    </td>
  </tr>
  <tr>
    <td>
      <table class="list sortable">
        <tr>
          <th>Table</th>
          <th>Record ID</th>
          <th>Role</th>
          <th>Actions</th>
          <th>Sort Key</th>
          <th>Comments</th>
          <th>&nbsp;</th>
        </tr>
        {show_permission_list("templates/permissionListR.tpl")}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
