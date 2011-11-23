{include_template("templates/productSaleItemR2.tpl")}

<table class="list" style="margin:3px 0px 10px 0px;">
  <tr>
    <th width="1%">ID</th>
    <th width="10%">Amount</th>
    <th width="20%">Source TF</th>
    <th width="20%">Destination TF</th>
    <th>Description</th>
    <th width="1%">Status</th>
  </tr>
  {show_transaction_list($transactions, "templates/transactionViewR.tpl")}
</table>
