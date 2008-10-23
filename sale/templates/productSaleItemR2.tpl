<div class="message corner" style="margin:10px 0px">
<table width="100%">
  <tr>
    <td width="1%" class="right">Product:</td><td width="20%">{$productLink}</td>
    <td width="1%" class="right nobr">Buy Cost{$buyCostTax_label}:</td><td width="1%" class="right">{$itemBuyCost}</td>
    <td width="20%" class="right nobr">Transactions Outgoing:</td><td width="1%" class="right">{$itemSpent}</td>
    <td width="20%" class="right nobr"></td><td width="1%" class="right"></td>
  </tr>
  <tr>
    <td class="right">Quantity:</td><td>{$quantity}</td>
    <td class="right nobr">Sell Price{$sellPriceTax_label}:</td><td class="right">{$itemSellPrice}</td>
    <td class="right nobr">Transactions Incoming:</td><td class="right">{$itemEarnt}</td>
    <td class="right nobr"></td><td class="right"></td>
  </tr>
  <tr>
    <td class="right">Description:</td><td>{$description}</td>
    <td class="right">Margin:</td><td class="right">{$itemMargin}</td>
    <td class="right nobr">Transactions Other:</td><td class="right">{$itemOther}</td>
    {$class = "good"}
    {$itemTotalUnallocated != 0 and $class="bad"}
    <td class="right nobr {$class}">Total Unallocated:</td><td class="right {$class}">{$itemTotalUnallocated}</td>
  </tr>
  <tr>
  </tr>
</table>
</div>

