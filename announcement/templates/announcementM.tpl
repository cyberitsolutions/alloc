{show_header()}
{show_toolbar()}
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
    <td>{get_calendar("displayFromDate",$TPL["displayFromDate"])}</td>
  </tr>
  <tr>
    <td>Display To</td>
    <td>{get_calendar("displayToDate",$TPL["displayToDate"])}</td>
  </tr>
  <tr>
    <td>Body</td>
    <td><textarea name="body" rows="5" cols="80">{$body}</textarea></td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <input type="submit" value="Save" name="save">
      <input type="submit" value="Delete" name="delete" onClick="return confirm('Are you sure you want to delete this announcement?')">
    </td>
  </tr>
</table>
<input type="hidden" name="announcementID" value="{$announcementID}">
</form>
{show_footer()}
