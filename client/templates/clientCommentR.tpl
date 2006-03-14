<form action="{url_alloc_client}" method="post">
<input type="hidden" name="clientID" value="{client_commentLinkID}">
<input type="hidden" name="commentID" value="{client_commentID}">
<input type="hidden" name="clientComment_id" value="{client_commentID}">
<tr>
  <td colspan="2">
    <hr width="100%">
  </td>
</tr>
<tr>
  <td valign="top"><b>{client_username}</b> {client_commentModifiedDate} {ts_label}<br/>{client_comment_trimmed}<br>&nbsp;</td>
  <td valign="top" align="right">
    {comment_buttons}
  </td>
</tr>
</form>
