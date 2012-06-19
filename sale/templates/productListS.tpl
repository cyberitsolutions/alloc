{if $productListRows}
<table class="list sortable">
<tr>
  <th>Product</th>
  <th>Description</th>
  <th class='nobr'>Price</th>
  <th>Active</th>
</tr>

{foreach $productListRows as $r}
<tr>
  <td class="nobr" sorttable_customkey="{print $r["productActive"] ? "1" : "2"}{$r.productName}">{echo product::get_link($r)}&nbsp;</td>
  <td>{page::htmlentities($r["description"])}&nbsp;</td>
  <td class="nobr">{page::money($r["sellPriceCurrencyTypeID"],$r["sellPrice"],"%s%mo %c")}&nbsp;</td>
  <td class="nobr">{print $r["productActive"] ? "Yes" : "No"}</td>
</tr>
{/}

</table>
{/}
