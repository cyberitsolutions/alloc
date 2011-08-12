<form action="{$url_alloc_timeSheet}timeSheetID={$timeSheetID}" method="post">
<tr>
  <td><input type="text" name="transaction_transactionDate" value="{$transaction_transactionDate}" size="10"></td>
  <td><input type="text" name="transaction_product" value="{$transaction_product}" size="20"></td>
  <td><select name="transaction_fromTfID" style="width:100%"><option value="">{$from_tf_options}</select></td>
  <td><select name="transaction_tfID" style="width:100%"><option value="">{$tf_options}</select></td>
  <td><input type="text" name="transaction_amount" value="{$transaction_amount}" size="8" style="width:48%">
      <select name="percent_dropdown" onChange="this.form.transaction_amount.value=this.options[this.selectedIndex].value;" style="width:48%">
      {$percent_dropdown}
      </select>
  </td>
  <td><select name="transactionType"><option value="">{$transactionType_options}</select></td>
  <td><select name="transaction_status">{$status_options}</select></td>
  <td class="nobr">{$transaction_buttons}</td>
</tr>

<input type="hidden" name="transaction_transactionID" value="{$transaction_transactionID}">
<input type="hidden" name="transaction_invoiceItemID" value="{$transaction_invoiceItemID}">
<input type="hidden" name="transaction_quantity" value="1">
<input type="hidden" name="timeSheetID" value="{$transaction_timeSheetID}">
<input type="hidden" name="sessID" value="{$sessID}">
</form>

