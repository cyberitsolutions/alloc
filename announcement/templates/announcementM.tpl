{page::header()}
{page::toolbar()}
<form action="{$url_alloc_announcement}" method="post">
{$table_box} 
  <tr>
    <th>Announcement</th>
    <th class="right"><a href="{$url_alloc_announcementList}">Return to Announcement List</a></th>
  </tr>
  <tr>
    <td>Heading</td>
    <td><input type="text" name="heading" size="80" value="{$heading}"></td>
  </tr>
  <tr>
    <td>Display From</td>
    <td>{page::calendar("displayFromDate",$TPL["displayFromDate"])}</td>
  </tr>
  <tr>
    <td>Display To</td>
    <td>{page::calendar("displayToDate",$TPL["displayToDate"])}</td>
  </tr>
  <tr>
    <td>Body</td>
    <td>{page::textarea("body",$TPL["body"],array("height"=>"jumbo"))}</td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="Save" name="save">
      <input type="submit" value="Delete" name="delete" class="delete_button">
    </td>
  </tr>
</table>
<input type="hidden" name="announcementID" value="{$announcementID}">
</form>
{page::footer()}
