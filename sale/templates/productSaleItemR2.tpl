<div class="message corner" style="margin:10px 0px">
<table width="100%">
  <tr>
    {$classItemEarnt=""}
    { page::money($sellPriceCurrencyTypeID,$itemSellPrice,"%mo")>page::money(config::get_config_item("currency"),$itemEarnt,"%m") and $classItemEarnt="bad"}
    <td width="1%" class="right">Product:</td><td width="20%">{$productLink}</td>
    <td class="right nobr">Sell Price{$sellPriceTax_label}:</td><td class="right nobr">
      {page::money($sellPriceCurrencyTypeID, $itemSellPrice,"%s%mo %c")}
      {$sellPriceCurrencyTypeID!=config::get_config_item('currency') and print exchangeRate::convert($sellPriceCurrencyTypeID,$itemSellPrice,null,null,"(%s%mo %c)")}
    </td>
    <td class="right nobr {$classItemEarnt}">Transactions Incoming:</td><td class="right nobr {$classItemEarnt}">{page::money(config::get_config_item("currency"),$itemEarnt,"%s%m %c")}</td>
    <td width="20%" class="right nobr"></td><td width="1%" class="right"></td>
  </tr>
  <tr>
    <td class="right">Quantity:</td><td>{$quantity}</td>
    <td class="right nobr">Total Product Costs:</td><td class="right nobr">{$itemCosts}</td>
    <td width="20%" class="right nobr">Transactions Outgoing:</td><td width="1%" class="right nobr">{page::money(config::get_config_item("currency"),$itemSpent,"%s%m %c")}</td>
  </tr>
  <tr>
    <td class="right">Description:</td><td>{=$description}</td>
    <td class="right">Margin:</td><td class="right nobr">{page::money(config::get_config_item("currency"),$itemMargin,"%s%mo %c")}</td>
    <td class="right nobr">Transactions Other:</td><td class="right nobr">{page::money(config::get_config_item("currency"),$itemOther,"%s%mo %c")}</td>
  </tr>
  <tr>
  </tr>
</table>
</div>

