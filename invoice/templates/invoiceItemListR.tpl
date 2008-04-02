<table width="100%" cellspacing="0" border="0" class="panel{$box_class}">
<tr>
  <td width="10%" class="nobr">{$invoiceItem_iiDate}</td>
  <td width="15%" class="nobr">{$invoiceItem_iiQuantity} * {$invoiceItem_iiUnitPrice} = <b>{$invoiceItem_iiAmount}</b></td>
  <td>{$invoiceItem_iiMemo}</td>
  <td class="nobr" align="right">{$status_label}</td>
</tr>
<tr>
  {$str = $TPL["invoiceItem_buttons_top"].$TPL["invoiceItem_buttons"]}
  {$str && $TPL["transaction_info"] and $TPL["transaction_info"] = "<br>".$TPL["transaction_info"]}
  <td colspan="4" class="right">{$invoiceItem_buttons_top}{$invoiceItem_buttons}{$transaction_info}</td>
</tr>
</table>

