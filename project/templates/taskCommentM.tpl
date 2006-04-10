
{table_box}
  <tr>
    <th colspan="2">Comments</th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{url_alloc_taskComment}" method="post" id="taskCommentForm">
      <table width="100%">
        <tr>
          <td>
            <input type="hidden" name="taskID" value="{task_taskID}">
            <textarea name="taskComment" cols="85" rows="7" wrap="virtual" id="taskComment">{task_taskComment}</textarea>&nbsp;
          </td>
          <td align="right" valign="top">
            <select name="taskCommentTemplateID" onChange="updateStuffWithAjax()">{taskCommentTemplateOptions}</select>
            <br/>Email Task Creator <input type="checkbox" name="commentEmailCheckboxes[]" value="creator"{email_comment_creator_checked}>
            <br/>Email Task Assignee <input type="checkbox" name="commentEmailCheckboxes[]" value="assignee"{email_comment_assignee_checked}>
            <br/>Email Interested Parties <input type="checkbox" name="commentEmailCheckboxes[]" value="CCList"{email_comment_CCList_checked}>
            <br/>
            <br/>
            {task_taskComment_buttons}
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  {:show_taskCommentsR templates/taskCommentR.tpl}
</table>

