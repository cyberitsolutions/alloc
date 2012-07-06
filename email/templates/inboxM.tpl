{page::header()}
{page::toolbar()}

<script>
  function grabbody(num) {
    jQuery.get('{$url_alloc_fetchBody}',{ id:num }, function(data) {
      $("#mail_text_"+num).html(data).toggle();
    });
    return false;
  }
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
          <th>Date</th>
          <th>From</th>
          <th>Subject</th>
          <th>Actions</th>
        </tr>
      {foreach (array)$rows as $r}
        <tr class="{$r["new"] and print "bold"}">
          <td>{$r.id}</td>
          <td>{$r.date}</td>
          <td>{=$r.from}</td>
          <td><a href="" onClick="return grabbody({$r.id});">{=$r.subject}</a><div class="hidden" id="mail_text_{$r.id}"></div></td>
          <td class="nobr right" style="width:1%;">
            <form action="{$url_alloc_inbox}" method="post">
            {if $r["new"]}
            <button type="submit" name="readmail" value="1" class="filter_button">Mark Read<i class="icon-envelope-alt"></i></button> 
            {else}
            <button type="submit" name="unreadmail" value="1" class="filter_button">Mark Unread<i class="icon-envelope"></i></button> 
            {/}
            <button type="submit" name="archive" value="1" class="filter_button">Archive<i class="icon-save"></i></button> 
            <button type="submit" name="download" value="1" class="filter_button">Download<i class="icon-download"></i></button> 
            <button type="submit" name="process" value="1" class="filter_button">Process<i class="icon-cogs"></i></button> 
            <button type="submit" name="newtask" value="1" class="filter_button">New Task<i class="icon-plus-sign"></i></button> 
            <input type="hidden" name="id" value="{$r.id}">
            <input type="hidden" name="hash" value="{echo md5($r["date"].$r["from"].$r["subject"])}">
            </form>
          </td>
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
