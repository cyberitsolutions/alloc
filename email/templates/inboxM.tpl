{page::header()}
{page::toolbar()}

<script>
  function grabbody(num) {
    jQuery.get('{$url_alloc_fetchBody}',{ id:num }, function(data) {
      $("#mail_text_"+num).html(data).toggle();
    });
    return false;
  }

  // When the document has loaded...
  $(document).ready(function() {
    preload_field(".taskfield","Task ID");


    $(".existingtask").on('click',function(e) {
      e.preventDefault();
      var taskID = $(this).prevAll('.taskfield').val();
      var f = $(this).parent().parent();
      var rtn = false;
      var d = '';
      $.post('{$url_alloc_updateTaskName}',{ "taskID" : taskID }, function(data) {
        rtn = true;
        d = data;
      });
    
      var timer = window.setInterval(function() {
        if (rtn) {
          clearInterval(timer);
          if (window.confirm('Add email to task: '+d)) { 
            f.submit();
          }
        }
      }
      ,300);

    });


  });
</script>

<table class="box">
  <tr>
    <th class="header"><a href="{$url_alloc_inbox}">Manage Inbox for {page::htmlentities(str_replace(array("<",">"),"",ALLOC_DEFAULT_FROM_ADDRESS))}</a>
      <b> - {print count($rows)} emails</b>
      <span>
        {page::help("inbox")}
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {if $rows}
      <table class="list sortable">
        <tr>
          <th>ID</th>
          <th>New</th>
          <th>Date</th>
          <th>Email</th>
          {if have_entity_perm("inbox",PERM_UPDATE,$current_user)}
          <th>Actions</th>
          {/}
        </tr>
      {foreach (array)$rows as $r}
        <tr class="{$r["new"] and print "highlighted"}">
          <td class="top">{$r.id}</td>
          <td class="top">{$r["new"] and print "New"}</td>
          <td class="top">{$r.date}</td>
          <td class="top">{=$r.from}<br>
          <a href="" onClick="return grabbody({$r.id});">{=$r.subject}</a><div class="hidden" id="mail_text_{$r.id}"></div>
          </td>
          {if have_entity_perm("inbox",PERM_UPDATE,$current_user)}
          <td class="nobr top right" style="width:1%;">
            <form action="{$url_alloc_inbox}" method="post">

            <a href="" style="font-weight:normal;text-decoration:none;" onClick="$('#hidden_buttons_{$r.id}').toggle();return false;"><i class="icon-hand-left"></i></a>&nbsp;&nbsp;

            <span id="hidden_buttons_{$r.id}" class="hidden">
            {if $r["new"]}
            <button type="submit" name="readmail" value="1" class="filter_button">Mark Read<i class="icon-envelope-alt"></i></button> 
            {else}
            <button type="submit" name="unreadmail" value="1" class="filter_button">Mark Unread<i class="icon-envelope"></i></button> 
            {/}
            <button type="submit" name="download" value="1" class="filter_button">Download<i class="icon-download"></i></button> 
            <button type="submit" name="process" value="1" class="filter_button">Process<i class="icon-cogs"></i></button> 
            </span>

            <button type="submit" name="archive" value="1" class="filter_button">Archive<i class="icon-save"></i></button> 
            <button type="submit" name="newtask" value="1" class="filter_button">New Task<i class="icon-plus-sign"></i></button> 
            <button type="button" class="filter_button" onClick="$('#hidden_taskops_{$r.id}').toggle(); return false;">Existing Task<i class="icon-arrow-down"></i></button> 

            <div id="hidden_taskops_{$r.id}" class="hidden" style="margin:8px 0px;">
            <input type="text" size="8" value="" class="taskfield left" name="taskID">
            <select name="emailto">
              <option value="default">Default interested parties
              <option value="noone">No interested parties
              <option value="internal">Internal interested parties
            </select>
            <button type="submit" name="existingtask" value="1" class="save_button existingtask" style="padding:1px 4px !important;font-size:80%;">Go<i class="icon-ok-sign"></i></button> 
            <div class="left" id="task_name_label_{$r.id}" style="white-space:wrap;"></div>
            </div>

            <input type="hidden" name="id" value="{$r.id}">
            <input type="hidden" name="hash" value="{echo md5($r["date"].$r["from"].$r["subject"])}">
            </form>
          </td>
          {/}
        </tr>
      {/}
      </table>
      {else}
      No emails are in the inbox.
      {/}
    </td>
  </tr>
</table>
{page::footer()}
