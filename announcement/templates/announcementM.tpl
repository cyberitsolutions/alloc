{page::header()}
{page::toolbar()}
<form action="{$url_alloc_announcement}" method="post">
<table class="box"> 
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
    <td>{page::calendar("displayFromDate",$displayFromDate)}</td>
  </tr>
  <tr>
    <td>Display To</td>
    <td>{page::calendar("displayToDate",$displayToDate)}</td>
  </tr>
  <tr>
    <td>Body</td>
    <td>{page::textarea("body",$body,array("height"=>"jumbo"))}</td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
      <button type="submit" name="save" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>
    </td>
  </tr>
</table>
<input type="hidden" name="announcementID" value="{$announcementID}">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
{page::footer()}
