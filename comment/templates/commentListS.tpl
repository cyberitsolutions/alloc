{if $commentListRows}
<table class="list sortable">
  <tr>
    <th>Entity</th>
    <th>Date</th>
    <th>Person</th>
    <th>Comment</th>
    <th width="1%" style="font-size:120%"><i class="icon-star"></i></th>
  </tr>
  {foreach $commentListRows as $r}
  <tr>
    <td>
    {echo ucwords($r["commentMaster"])} {$r.commentMasterID} {$r.entity_link}
    </td>
    <td>{$r.commentCreatedTime}</td>
    <td>{$r.person}</td>
    <td onClick='return set_grow_shrink("longText_{$r.commentID}","shortText_{$r.commentID}")'>
      <div id='shortText_{$r.commentID}'>{echo substr(trim(page::htmlentities($r["comment"])),0,100)}</div>
      <div style='display:none' id='longText_{$r.commentID}'>{echo nl2br(trim(page::htmlentities($r["comment"])))}</div>
    </td>
    <td width="1%">
      {if $r["timeSheetItemID"]}
        {page::star("timeSheetItem",$r["timeSheetItemID"])}
      {else}
        {page::star("comment",$r["commentID"])}
      {/}
    </td>
  </tr>
  {/}
</table>
{/}
