
<table class="box">
  <tr>
    <th class="header">{page::help("comment")} Comments
      <span>
        {$extra_page_links}
        {page::expand_link("id_new_comment","New Comment")}
      </span>
    </th>
  </tr>
  <tr>
    <td>

      <form action="{$url_alloc_comment}" enctype="multipart/form-data" method="post" id="commentForm">
      <div class="{$class_new_comment}" id="id_new_comment">
      <table align="left" width="100%" cellpadding="3">
        <tr>  
          <td valign="top" align="right" style="width:50%">
            <input type="hidden" name="entity" value="{$entity}">
            <input type="hidden" name="entityID" value="{$entityID}">
            <div id="comment_textarea">
              {page::textarea("comment",$comment,array("height"=>"medium","width"=>"100%"))}
            </div>
            <div id="file_attachment_dialog" style="display:inline; float:left">
            </div>
            <div style="display:inline; float:left; clear:left;">
              <a href="#x" class="magic" onClick="$('#file_attachment_dialog').append('<input type=\'file\' name=\'attachment[]\'><br>');">Attach File</a>
              {$attach_extra_files}
            </div>
            <select name="commentTemplateID" onChange="makeAjaxRequest('{$url_alloc_updateCommentTemplate}entity={$entity}&entityID={$entityID}&commentTemplateID='+$(this).attr('value'),'comment_textarea')">{$commentTemplateOptions}</select>
          </td>

          <td colspan="2" valign="top" style="padding-left:10px;">
            <div style="display:block; clear:both; padding-bottom:8px;"><u>Email Recipients</u></div>
            {echo interestedParty::get_interested_parties_html($allParties)}
            <div style="float:left; clear:both; padding:10px 0px 8px 0px">{page::expand_link("email_other","Email Other Party")}</div>
            <div style="text-align:right; float:right; padding:10px 0px 8px 0px"><input type="submit" name="comment_save" value="Save Comment"></div>

            <table id="email_other" style="clear:both; display:none" width="100%" cellpadding="4" cellspacing="0">
              <tr>
                <td class="nobr">Name: <input type="text" name="eo_name" size="30"></td>
                <td class="right nobr"> 
                </td>
              </tr>
              <tr>
                <td class="nobr">Email: <input type="text" name="eo_email" size="30"></td>
                <td class="right nobr">
                  {if $clientID}
                  <input type="hidden" name="eo_client_id" value="{$clientID}">
                  {/}
                </td>
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
    <td>
      {$commentsR}
    </td>
  </tr>
</table>

