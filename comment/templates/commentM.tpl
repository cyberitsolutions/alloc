
<table class="box">
  <tr>
    <th class="header">Comments
      <span>
        <a href="{$url_alloc_downloadComments}entity={$entity}&entityID={$entityID}" class="noprint">Download</a>
        {if $commentsR}
        {$extra_page_links}
        {/}
        <a class="growshrink nobr noprint commentnew" href="#x">New Comment</a>
        {page::help("comment")}
      </span>
    </th>
  </tr>
  <tr>
    <td id="new_comment_container" class="noprint" style="width:100%;">
      <div class="{$class_new_comment}" id="id_new_comment" style="width:99%">
      <form action="{$url_alloc_comment}" enctype="multipart/form-data" method="post" id="commentForm">
      <table align="left" width="100%" cellpadding="3">
        <tr>  
          <td valign="top" align="right" style="width:50%">
            <input type="hidden" name="commentMaster" value="{$entity}">
            <input type="hidden" name="commentMasterID" value="{$entityID}">
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
            {if have_entity_perm("commentTemplate",PERM_CREATE)}
            <a title="Add new comment template" href="{$url_alloc_commentTemplate}" class="icon-cogs"></a>
            {/}
            <select name="commentTemplateID" onChange="makeAjaxRequest('{$url_alloc_updateCommentTemplate}entity={$entity}&entityID={$entityID}&commentTemplateID='+$(this).val(),'comment_textarea')">{$commentTemplateOptions}</select>
          </td>

          <td colspan="2" valign="top" style="padding-left:10px;" id="interested_parties_selector">
            <div style="display:block; clear:both; padding-bottom:8px;"><u>Email Recipients</u></div>
            {echo interestedParty::get_interested_parties_html($allParties)}
            <div style="float:left; clear:both; padding:10px 0px 8px 0px">{page::expand_link("email_other","Email Other Party")}</div>

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
                <td class="right nobr">
                  {if $clientID}
                  <label for="eo_add_client_contact">Add to Client Contacts </label>
                  <input id="eo_add_client_contact" type="checkbox" name="eo_add_client_contact" value="1" checked>
                  <input type="hidden" name="eo_client_id" value="{$clientID}">
                  {/}
                </td>
              </tr>
            </table>

          </td>
        </tr>
        <tr>
          <td colspan="3" class="right">
            <input type="hidden" name="sessID" value="{$sessID}">
            <button type="submit" name="comment_save" value="1" class="save_button">Save Comment<i class="icon-comment"></i></button>
          </td>
        </tr>
      </table>
      </form>
      </div>
    </td>
  </tr>
  <tr>
    <td>
      {$commentsR}
    </td>
  </tr>
</table>

