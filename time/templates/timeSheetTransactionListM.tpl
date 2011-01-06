<table class="box">
<tr>
  <th colspan="8">Transactions</th>
</tr>
<tr>
  <td>
    <table class='sortable list'>
      <tr>
        <th>Date</th>
        <th>Product</th>
        <th>Source TF</th>
        <th>Dest TF</th>
        <th>Amount{$amount_msg}</th>
        <th>Type</th> 
        <th class="nobr">{$p_a_r_buttons}</th>
        <th>&nbsp;</th>
      </tr>
      {show_transaction_listR("templates/timeSheetTransactionListR.tpl")}
      {show_new_transaction("templates/timeSheetNewTransaction.tpl")}
      {$create_transaction_buttons}
    </table>
  </td>
</tr>
</table>
