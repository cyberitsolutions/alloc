{show_header()}
{show_toolbar()}
  <form action="{$url_alloc_permission}" method="post">

{$table_box}
  <tr>
    <th>Permission</th>
    <th class="right" colspan="2"><a href="{$url_alloc_permissionList}">Return to Permission List</a></th>
  </tr>
  <tr>
    <td>Table</td>
    <td>{$tableName}<input type="hidden" name="tableName" value="{$tableName}"></td>
    <td></td>
  </tr>
  <tr>
    <td>Record ID</td>
    <td><input type="text" name="entityID" value="{$entityID}" size="5"></td>
    <td>Enter the ID of a record set the permission on a specific record.  Use 0 or leave this blank to indicate all records.  Use -1 to indicate records owned by the user.</td>
  </tr>
  <tr>
    <td>Person</td>
    <td>
      <select name="personID">
        <option value="">All
        {$personOptions}
      </select>
    </td>
    <td></td>
  </tr>
  <tr>
    <td>Role name</td>
    <td>
      <select name="roleName">
        <option value="">All
        {$roleNameOptions}
      </select>
    </td>
    <td></td>
  </tr>
  <tr>
    <td>Actions</td>
    <td>
      <select name="actions_array[]" size="5" multiple>
        {$actionOptions}
      </select>
    </td>
    <td></td>
  </tr>
  <tr>
    <td>Allow</td>
    <td>
      <select name="allow">
        {$allowOptions}
      </select>
    </td>
    <td></td>
  </tr>
  <tr>
    <td>Sort key</td>
    <td><input type="text" name="sortKey" value="{$sortKey}" size="5"></td>
    <td>Records with a lower sort key will have higher precedence than those with higher sort keys</td>
  </tr>
  <tr>
    <td valign="top">Comment</td>
    <td colspan="2" valign="top">{get_textarea("comment",$TPL["comment"])}</td>
  </tr>
  <tr>
    <td align="center" colspan="3">
      <input type="submit" value="Save" name="save">
      <input type="submit" value="Delete" name="delete" onClick="return confirm('Are you sure you want to delete this permission?')">
    </td>
    </tr>
  </table>

    <input type="hidden" name="permissionID" value="{$permissionID}">
  </form>


{show_footer()}


