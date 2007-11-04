<table width="100%" cellspacing="0" border="0" class="comments{$box_class}">
<tr>
  <td colspan="4"><b>Incoming Funds {$link}</b></td>
  <td valign="top" class="nobr" align="right">{$status_label}</td>
</tr>
<tr>
  <td valign="top" width="10%" class="nobr">{$invoiceItem_iiDate}</td>
  <td valign="top" width="10%" class="nobr">{$invoiceItem_iiQuantity} * {$invoiceItem_iiUnitPrice}</td>
  <td valign="top" width="8%"><b>{$invoiceItem_iiAmount}</b></td>
  <td width="30%" colspan="2">&nbsp;</td>
</tr>
<tr>
  <td colspan="5">{$invoiceItem_iiMemo}</td>
</tr>
<tr>
  <td colspan="4"><b>Transactions </b></td>
  <td colspan="1" class="nobr right">{$invoiceItem_buttons_top}{$invoiceItem_buttons}</td>
</tr>
<tr>
  <td colspan="5">{$transaction_info}</td>
</tr>
</table>

