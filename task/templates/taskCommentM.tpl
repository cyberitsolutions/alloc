
{$table_box}
  <tr>
    <th>{get_help("taskComment")} Comments</th>
    <th class="right">{if (!$TPL["editing_disabled"])}{print_expand_link("id_new_task_comment")}{/}</th>
  </tr>
  <tr>
    <td colspan="2">

      <form action="{$url_alloc_comment}" enctype="multipart/form-data" method="post" id="taskCommentForm">
      <div class="{$class_new_task_comment}" id="id_new_task_comment">
      <table align="left" width="100%" cellpadding="3">
        <tr>  
          <td valign="top" align="right">
            <input type="hidden" name="entity" value="task">
            <input type="hidden" name="entityID" value="{$task_taskID}">
            <div id="comment_textarea">
              {get_textarea("comment",$TPL["comment"],array("height"=>"medium"))}
            </div>
            <div id="file_attachment_dialog" style="display:inline; float:left">
            </div>
            <div style="display:inline; float:left; clear:left;">
              <a href="#x" class="magic" onClick="$('#file_attachment_dialog').append('<input type=\'file\' name=\'attachment[]\'><br>');">Attach File</a>
            </div>
            <select name="taskCommentTemplateID" onChange="makeAjaxRequest('{$url_alloc_updateTaskCommentTemplate}taskID={$task_taskID}&taskCommentTemplateID='+$(this).attr('value'),'comment_textarea')">{$taskCommentTemplateOptions}</select>
          </td>
          <td colspan="2" valign="top" width="60%">
            <div style="display:block; clear:both; padding-bottom:8px;"><u>Email Interested Parties</u></div>
            {echo interestedParty::get_interested_parties_html($TPL["allTaskParties"])}
            <div style="float:left; clear:both; padding:10px 0px 8px 0px">{print_expand_link("email_other","Email Other Party ")}</div>
            <div style="text-align:right; float:right; padding:10px 0px 8px 0px">{$comment_buttons}</div>

            <table id="email_other" style="clear:both; display:none" width="100%" cellpadding="4" cellspacing="0">
              <tr>
                <td class="nobr">Name: <input type="text" name="eo_name" size="30"></td>
                <td class="right nobr"> 
                  <label for="eo_add_interested_party">Add to Interested Parties </label> 
                  <input id="eo_add_interested_party" type="checkbox" name="eo_add_interested_party" value="1" checked>
                </td>
              </tr>
              <tr>
                <td class="nobr">Email: <input type="text" name="eo_email" size="30"></td>
                {if $TPL["task_clientID"]}
                <td class="right nobr">
                  <label for="eo_add_client_contact">Add to Client Contacts </label> 
                  <input id="eo_add_client_contact" type="checkbox" name="eo_add_client_contact" value="1" checked>
                  <input type="hidden" name="eo_client_id" value="{$task_clientID}">
                </td>
              {/}
              </tr>
            </table>

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
  </tr>
{if $TPL["editing_disabled"]}
  <tr>
    <td colspan="2">{$disabled_reason}</td>
  </tr>
{/}
</table>

