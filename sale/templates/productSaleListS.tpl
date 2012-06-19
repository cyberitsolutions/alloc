{if $productSaleListRows}
<table class="list sortable">
<tr>
  <th class="sorttable_numeric">ID</th>
  <th>Creator</th>
  <th>Salesperson</th>
  <th>Date</th>
  <th>Client</th>
  <th>Project</th>
  <th>Status</th>
  <th width="1%" style="font-size:120%"><i class="icon-star"></i></th>
</tr>
 
{foreach $productSaleListRows as $r}
<tr>
  <td class="nobr">{echo productSale::get_link($r)}</td>
  <td class="nobr">{=$r.creatorLabel}</td>
  <td class="nobr">{=$r.salespersonLabel}</td>
  <td class="nobr">{$r.productSaleDate}</td>
  <td class="nobr">{=$r.clientName}</td>
  <td class="nobr">{=$r.projectName}</td>
  <td class="nobr">{$r.statusLabel}</td>
  <td width="1%">
    {page::star("productSale",$r["productSaleID"])}
  </td>
</tr>
{/}


</table>
{/}
