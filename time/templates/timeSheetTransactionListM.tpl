<table class="box">
<tr>
  <th class="header">Transactions
    <span>
      <div class="center">
        Amount required: {$total_dollars}&nbsp;&nbsp;&nbsp;
        Amount allocated: {$total_allocated}
      </div>
    </span>
  </th>
</tr>
<tr>
  <td>
    <!-- FIXME: ideally this table would be sortable, but the stupidtable() function in jquery.stupidtable.js is broken. cjb, 2019-05 -->
    <table class='list'>
      <tr>
        <th width="1%">Date</th>
        <th>Product</th>
        <th width="1%">Source TF</th>
        <th width="1%">Dest TF</th>
        <th width="1%">Amount{$amount_msg}</th>
        <th width="1%">Type</th>
        <th width="1%" data-sort="none" class="nobr" style="font-size:80%">{$p_a_r_buttons}</th>
        <th width="1%">&nbsp;</th>
      </tr>
      {show_transaction_listR("templates/timeSheetTransactionListR.tpl")}
      <tfoot>
      {show_new_transaction("templates/timeSheetNewTransaction.tpl")}
      {$create_transaction_buttons}
      </tfoot>
    </table>
  </td>
</tr>
</table>
