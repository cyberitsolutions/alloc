
{$table_box}
  <tr>
    <th>Comments</th>
{if (!$TPL["editing_disabled"])}
    <th class="right">{get_expand_link("id_new_task_comment")}</th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{$url_alloc_comment}" method="post" id="taskCommentForm">
      <div class="{$class_new_task_comment}" id="id_new_task_comment">
      <table width="100%">
        <tr>
          <td valign="top" align="right">
            <input type="hidden" name="entity" value="task">
            <input type="hidden" name="entityID" value="{$task_taskID}">
            <div id="comment_textarea">
              {get_textarea("comment",$TPL["comment"],array("height"=>"medium"))}<br>
            </div>
            <select name="taskCommentTemplateID" onChange="makeAjaxRequest('{$url_alloc_updateTaskCommentTemplate}taskID={$task_taskID}&taskCommentTemplateID='+$(this).attr('value'),'comment_textarea')">{$taskCommentTemplateOptions}</select>
          </td>
          <td align="right" valign="top" width="100%">
            <table cellpadding="0" cellspacing="2">
              <tr>
                <td class="left small nobr"><u>Email Comment To</u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
              </tr>
              <tr>
                <td class="right small"><label for="cec_creator">Task Creator</label> <input id="cec_creator" type="checkbox" name="commentEmailCheckboxes[]" value="creator"{$email_comment_creator_checked}></td>
              </tr>
              <tr>
                <td class="right small"><label for="cec_manager">Task Manager</label> <input id="cec_manager" type="checkbox" name="commentEmailCheckboxes[]" value="manager"{$email_comment_manager_checked}></td>
              </tr>
              <tr>
                <td class="right small"><label for="cec_assignee">Task Assignee</label> <input id="cec_assignee" type="checkbox" name="commentEmailCheckboxes[]" value="assignee"{$email_comment_assignee_checked}></td>
              </tr>
              <tr>
                <td class="right small"><label for="cec_CCList">Interested Parties</label> <input id="cec_CCList" type="checkbox" name="commentEmailCheckboxes[]" value="CCList"{$email_comment_CCList_checked}></td>
              </tr>
              <tr>
                <td>&nbsp;</td>
              </tr>
              <tr>
                <td class="left small">{get_expand_link("email_other","Email Other Party ")}</td>
              </tr>
            </table>

            <table id="email_other" style="display:none" cellpadding="0" cellspacing="0">
              <tr>
                <td class="right small nobr">Name: <input type="text" name="eo_name" size="12"></td>
              </tr>
              <tr>
                <td class="right small nobr">Email: <input type="text" name="eo_email" size="12"></td>
              </tr>
              <tr>
                <td class="right small nobr"><label for="eo_add_interested_party">Create Interested Party</label> <input id="eo_interested_party" type="checkbox" name="eo_add_interested_party" value="1" checked></td>
              </tr>
              {if $TPL["task_clientID"]}
              <tr>
                <td class="right small nobr"><label for="eo_add_client_contact">Create Client Contact</label> <input id="eo_client_contact" type="checkbox" name="eo_add_client_contact" value="1" checked>
                <input type="hidden" name="eo_client_id" value="{$task_clientID}">
                </td>
              </tr>
              {/}
            </table>
            <br/>
            <br/>
          </td>
        </tr>
        <tr>
          <td class="right">
          </td>
          <td class="right">
            {$comment_buttons}
          </td>
        </tr>
      </table>
      </div>
      </form>
    </td>
  </tr>
{/}
  <tr>
    <td colspan="2">
      {$commentsR}
    </td>
  </tr>
{if $TPL["editing_disabled"]}
  <tr>
    <td colspan="2">
      <p><em>{$disabled_reason}</em></p>
    </td>
  </tr>
{/}

</table>

