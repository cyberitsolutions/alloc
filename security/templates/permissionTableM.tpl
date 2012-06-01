{page::header()}
{page::toolbar()}
<form action="{$url_alloc_permission}" method="post">
<table class="box">
  <tr>
    <th><nobr>New Permission - Select Table</nobr></th>
    <th class="right"><a href="{$url_alloc_permissionList}">Return to Permission List</a></th>
  </tr>
  <tr>
    <td width="10%">Table</td>
    <td>
      <select name="tableName">{$tableNameOptions}</select>
      <button type="submit" name="next" value="1" class="save_button">Next<i class="icon-arrow-right"></i></button>
    </td>
  </tr>
</table>
<input type="hidden" name="permissionID" value="{$permissionID}">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
{page::footer()}
