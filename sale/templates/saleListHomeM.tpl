{if $saleListRows}
<table class="list sortable">
  <tr>
    <th>ID</th>
    <th>Client</th>
    <th>Status</th>
    <th class="right">Amount</th>
  </tr>
  {foreach $saleListRows as $r}
  <tr>
    <td>{$r.productSaleLink}</td>
    <td>{$r.clientName}</td>
    <td>{$r.status}</td>
    <td class="nobr right obfuscate">{$r.amounts.total_sellPrice}</td>
  </tr>
  {/}
</table>
{else}
  <b>No Sales Found.</b>
{/}

