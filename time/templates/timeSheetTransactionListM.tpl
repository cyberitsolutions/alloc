{$table_box}
<tr>
  <th colspan="8">Transactions</th>
</tr>
<tr>
  <td colspan="5"></td>
  <td></td>
  <td>&nbsp;</td>
</tr>
<tr>
  <td>Date</td>
  <td>Product</td>
  <td>TF</td>
  <td>Amount{$amount_msg}</td>
  <td>Type</td> 
  <td>{$p_a_r_buttons}</td>
  <td>&nbsp;</td>
  <td>&nbsp;</td>
</tr>
{show_transaction_listR("templates/timeSheetTransactionListR.tpl")}
{show_new_transaction("templates/timeSheetNewTransaction.tpl")}
{$create_transaction_buttons}
</table><br>
