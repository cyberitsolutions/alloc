<div class="message corner" style="margin:10px 0px">
<table width="100%">
  <tr>
    <td width="1%" class="right">Product:</td><td width="20%">{$productLink}</td>
    <td class="right nobr">Sell Price{$sellPriceTax_label}:</td><td class="right nobr">
      {page::money($sellPriceCurrencyTypeID, $itemSellPrice,"%s%mo %c")}
      {$sellPriceCurrencyTypeID!=config::get_config_item('currency') and print exchangeRate::convert($sellPriceCurrencyTypeID,$itemSellPrice,null,null,"(%s%mo %c)")}
    </td>
    <td class="right nobr">Transactions Incoming:</td><td class="right nobr">{page::money(config::get_config_item("currency"),$itemEarnt,"%s%m %c")}</td>
    <td width="20%" class="right nobr"></td><td width="1%" class="right"></td>
  </tr>
  <tr>
    <td class="right">Quantity:</td><td>{$quantity}</td>
    <td class="right nobr">Total Costs:</td><td class="right nobr">{$itemCosts}</td>
    <td width="20%" class="right nobr">Transactions Outgoing:</td><td width="1%" class="right nobr">{page::money(config::get_config_item("currency"),$itemSpent,"%s%m %c")}</td>
    <td class="right nobr"></td><td class="right"></td>
  </tr>
  <tr>
    <td class="right">Description:</td><td>{=$description}</td>
    <td class="right">Margin:</td><td class="right nobr">{page::money(config::get_config_item("currency"),$itemMargin,"%s%mo %c")}</td>
    <td class="right nobr">Transactions Other:</td><td class="right nobr">{page::money(config::get_config_item("currency"),$itemOther,"%s%mo %c")}</td>
    {$class = "good"}
    {0 != page::money(config::get_config_item("currency"),$itemTotalUnallocated,"%mo") and $class="bad"}
    <td class="right nobr {$class}">Total Unallocated:</td><td class="right nobr {$class}">{page::money(config::get_config_item("currency"),$itemTotalUnallocated,"%s%mo %c")}</td>
  </tr>
  <tr>
  </tr>
</table>
</div>

