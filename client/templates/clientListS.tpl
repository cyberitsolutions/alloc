{if $clientListRows}
<table class="list sortable">
  <tr>
    <th>Client</th>
    <th>Phone</th>
    <th>Contact Name</th>
    <th>Contact Phone</th>
    <th>Contact Email</th>
    <th>Status</th>
    <th>Category</th>
    <th width="1%" style="font-size:120%"><i class="icon-star"></i></th>
  </tr>
  {foreach $clientListRows as $r}
  <tr>
    <td>{$r.clientLink}</td>
    <td>{$r.clientPhoneOne}</td>
    <td>{=$r.clientContactName}</td>
    <td>{if $r["clientContactPhone"]}Ph: {=$r.clientContactPhone}{/}&nbsp;&nbsp;
        {if $r["clientContactMobile"]}Mob: {=$r.clientContactMobile}{/}</td>
    <td>{$r.clientContactEmail}</td>
    <td>{$r.clientStatus}</td>
    <td>{$r.clientCategoryLabel}</td>
    <td width="1%">
      {page::star("client",$r["clientID"])}
    </td>
  </tr>
  {/}
</table>
{/}
