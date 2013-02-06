<div class="panel{$box_class} corner">
<table width="100%" cellspacing="0" border="0">
<tr>
  <td width="10%" class="top nobr">{$invoiceItem_iiDate}</td>
  <td width="15%" class="top nobr">{$invoiceItem_iiQuantity} * {page::money($currency,$invoiceItem_iiUnitPrice,"%s%m")} = <b>{page::money($currency,$invoiceItem_iiAmount,"%s%m %c")}</b>
  {if $tn = config::get_config_item("taxName")}
  ({print $invoiceItem_iiTax>0? "incl":"excl"} {$tn})
  {/}
  </td>
  <td class="top">{$invoiceItem_iiMemo}</td>
  <td class="top nobr right">{$status_label}</td>
</tr>
<tr>
  {$str = $invoiceItem_buttons_top.$invoiceItem_buttons}
  {$str && $transaction_info and $transaction_info = "<br>".$transaction_info}
  <td colspan="4" class="right"><div style='padding:10px 0px;'>{$invoiceItem_buttons_top}{$invoiceItem_buttons}</div>{$transaction_info}</td>
</tr>
</table>
</div>
