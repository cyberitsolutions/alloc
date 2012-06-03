{if $clientContactListRows}
<table class="list sortable">
  <tr>
    <th>Client</th>
    <th>Contact Name</th>
    <th>Contact Phone</th>
    <th>Contact Email</th>
    <th width="1%" style="font-size:120%"><i class="icon-star"></i></th>
  </tr>
  {foreach $clientContactListRows as $r}
  <tr>
    <td>{$r.clientLink}</td>
    <td>{=$r.clientContactName}</td>
    <td>{=$r.clientContactPhone}</td>
    <td>{$r.clientContactEmail}</td>
    <td width="1%">
      {page::star("clientContact",$r["clientContactID"])}
    </td>
  </tr>
  {/}
</table>
{/}
