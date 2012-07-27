{include_template("templates/productSaleItemR2.tpl")}

<form action="{$url_alloc_productSale}" method="post">
<input type="hidden" name="productSaleID" value="{$productSaleID}">
<input type="hidden" name="productSaleItemID" value="{$productSaleItemID}">

<table class="list" style="margin:3px 0px 10px 0px;">
  <tr>
    <th>&nbsp;</th>
    <th>Amount</th>
    <th>Source TF</th>
    <th>Destination TF</th>
    <th style="width:100%">Description</th>
    <th>Status</th>
    <th class="right">
      <a href="#x" class="magic" onClick="$('#transactions_footer_{$productSaleItemID}').before('<tr>'+$('#transactionRow').html()+'</tr>');">New</a>
    </th>
  </tr>
  {show_transaction_list($transactions, "templates/transactionR.tpl")}
  {show_transaction_new("templates/transactionR.tpl")}
  <tr id="transactions_footer_{$productSaleItemID}">
    <th colspan="7" class="center">
      <button type="submit" name="delete_transactions" value="1" class="delete_button">Delete All Transactions<i class="icon-trash"></i></button>
      <button type="submit" name="create_default_transactions" value="1" class="save_button">Create Default Transactions<i class="icon-cogs"></i></button>
      {if $taxName && 0}
      <button type="submit" name="add_tax" value="1" class="save_button">Add {$taxName}<i class="icon-cogs"></i></button>
      {/}
      <button type="submit" name="save_transactions" value="1" class="save_button">Save Transactions<i class="icon-ok-sign"></i></button>
    </th>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
