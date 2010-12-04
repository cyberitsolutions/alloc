<table width="100%" cellspacing="0" border="0" class="panel{$box_class}">
<tr>
  <td width="10%" class="nobr">{$invoiceItem_iiDate}</td>
  <td width="15%" class="nobr">{$invoiceItem_iiQuantity} * {page::money($currency,$invoiceItem_iiUnitPrice,"%s%m")} = <b>{page::money($currency,$invoiceItem_iiAmount,"%s%m")}</b></td>
  <td>{$invoiceItem_iiMemo}</td>
  <td class="nobr" align="right">{$status_label}</td>
</tr>
<tr>
  {$str = $invoiceItem_buttons_top.$invoiceItem_buttons}
  {$str && $transaction_info and $transaction_info = "<br>".$transaction_info}
  <td colspan="4" class="right">{$invoiceItem_buttons_top}{$invoiceItem_buttons}{$transaction_info}</td>
</tr>
</table>

