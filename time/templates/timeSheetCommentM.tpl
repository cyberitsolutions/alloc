
<table class="box">
  <tr>
    <th>{page::help("taskComment")} Comments</th>
    <th class="right">{if (!$TPL["editing_disabled"])}{page::expand_link("id_new_comment")}{/}</th>
  </tr>
  <tr>
    <td colspan="2">

      <form action="{$url_alloc_comment}" enctype="multipart/form-data" method="post">
      <div class="hidden" id="id_new_comment">
      <table align="left" width="100%" cellpadding="3">
        <tr>  
          <td valign="top" align="right">
            <input type="hidden" name="entity" value="timeSheet">
            <input type="hidden" name="entityID" value="{$timeSheetID}">
            <div id="comment_textarea">
              {page::textarea("comment",$TPL["comment"],array("height"=>"medium"))}
            </div>
            <div id="file_attachment_dialog" style="display:inline; float:left">
            </div>
            <div style="display:inline; float:left; clear:left;">
              <a href="#x" class="magic" onClick="$('#file_attachment_dialog').append('<input type=\'file\' name=\'attachment[]\'><br>');">Attach File</a>
            </div>
            <select name="commentTemplateID" onChange="makeAjaxRequest('{$url_alloc_updateCommentTemplate}entity=timeSheet&entityID={$timeSheetID}&commentTemplateID='+$(this).attr('value'),'comment_textarea')">{$commentTemplateOptions}</select>
          </td>
          <td colspan="2" valign="top" width="60%">
            <div style="display:block; clear:both; padding-bottom:8px;"><u>Email Recipients</u></div>
            {echo interestedParty::get_interested_parties_html($TPL["allTimeSheetParties"])}
            <div style="float:left; clear:both; padding:10px 0px 8px 0px">{page::expand_link("email_other","Email Other Party")}</div>
            <div style="text-align:right; float:right; padding:10px 0px 8px 0px"><input type="submit" name="comment_save" value="Save Comment"></div>

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
                {if $TPL["clientID"]}
                <td class="right nobr">
                  <label for="eo_add_client_contact">Add to Client Contacts </label> 
                  <input id="eo_add_client_contact" type="checkbox" name="eo_add_client_contact" value="1" checked>
                  <input type="hidden" name="eo_client_id" value="{$clientID}">
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
</table>

