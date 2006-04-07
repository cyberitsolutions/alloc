<form action="{url_alloc_invoiceItem}mode={mode}" method="post">
<tr>
<td align="center"><a href="{url_alloc_timeSheet}timeSheetID={transaction_timeSheetID}">{transaction_timeSheetID}</a>&nbsp;</td>
  <td><input type="text" name="transaction_transactionDate" value="{transaction_transactionDate}" size="10"></td>
  <td>
    <select name="transaction_tfID">
      <option value="">
      {:show_tf_options}
    </select>
  </td>
  <td>
    <input type="text" name="transaction_amount" value="{transaction_amount}" size="8">
	<select name="percent_dropdown" onChange="this.form.transaction_amount.value=this.options[this.selectedIndex].value;"> 
	{percent_dropdown}
	</select>
  </td>
  <td>
    <select name="transaction_status">
      {status_options}
    </select>
  </td>
  <td align="center">
    {transaction_buttons}
  </td>
</tr>
<input type="hidden" name="transaction_transactionID" value="{transaction_transactionID}">
<input type="hidden" name="transaction_invoiceItemID" value="{invoiceItemID}">
<input type="hidden" name="transaction_companyDetails" value="{invoiceName}">
<input type="hidden" name="transaction_product" value="{iiMemo}">
<input type="hidden" name="transaction_expenseFormID" value="0">
<input type="hidden" name="transaction_quantity" value="1">
<input type="hidden" name="invoiceItemID" value="{invoiceItemID}">
<input type="hidden" name="timeSheetID" value="{transaction_timeSheetID}">
</form>
