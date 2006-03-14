<form action="{url_alloc_taskComment}" method="post">
<input type="hidden" name="taskID" value="{task_commentLinkID}">
<input type="hidden" name="commentID" value="{task_commentID}">
<input type="hidden" name="taskComment_id" value="{task_commentID}">
<tr>
  <td colspan="2"><hr width="100%"></td>
</tr>
<tr>
  <td valign="top"><b>{task_username}</b> {task_commentModifiedDate} {ts_label}<br/>{task_comment_trimmed}</td>
  <td valign="top" align="right">{comment_buttons}</td>
</tr>
</form>
