
{$table_box}
  <tr>
    <th>Comments</th>
    <th class="right">{get_expand_link("id_new_task_comment")}</th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{$url_alloc_comment}" method="post" id="taskCommentForm">
      <div class="{$class_new_task_comment}" id="id_new_task_comment">
      <table width="100%">
        <tr>
          <td>
            <input type="hidden" name="entity" value="task">
            <input type="hidden" name="entityID" value="{$task_taskID}">
            <textarea name="comment" cols="85" rows="10" wrap="virtual" id="comment">{$comment}</textarea>&nbsp;
          </td>
          <td align="right" valign="top">
            <select name="taskCommentTemplateID" onChange="updateStuffWithAjax()">{$taskCommentTemplateOptions}</select>
            <br/>Email Task Creator <input type="checkbox" name="commentEmailCheckboxes[]" value="creator"{$email_comment_creator_checked}>
            <br/>Email Task Manager <input type="checkbox" name="commentEmailCheckboxes[]" value="manager"{$email_comment_manager_checked}>
            <br/>Email Task Assignee <input type="checkbox" name="commentEmailCheckboxes[]" value="assignee"{$email_comment_assignee_checked}>
            <br/>Email Interested Parties <input type="checkbox" name="commentEmailCheckboxes[]" value="CCList"{$email_comment_CCList_checked}>
            <br/>
            <br/>
            {$comment_buttons}
          </td>
        </tr>
      </table>
      </div>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      {$commentsR}
    </td>
</table>

