<form action="{$url_alloc_timeSheet}timeSheetID={$timeSheetID}" method="post">
<input type="hidden" name="transaction_invoiceItemID" value="{$invoiceItemID}">
<input type="hidden" name="transaction_quantity" value="1">
<input type="hidden" name="invoiceItemID" value="{$invoiceItemID}">
<input type="hidden" name="timeSheetID" value="{$transaction_timeSheetID}">
<tr>
  <td><input type="text" name="transaction_transactionDate" value="{$transaction_transactionDate}" size="10"></td>


  <td><input type="text" name="transaction_product" value="{$transaction_product}" style="width:100%"></td>
  <td>
    <select name="transaction_fromTfID">
    <option value="">
    {$tf_options}
    </select>
  </td>
  <td>
    <select name="transaction_tfID">
    <option value="">
    {$tf_options}
    </select>
  </td>

  <script>preload_field("#transaction_amount","{$total_remaining}")</script>
  <td class="nobr"><input id="transaction_amount" type="text" name="transaction_amount" size="8" value="">
  <select name="percent_dropdown" onChange="this.form.transaction_amount.value=this.options[this.selectedIndex].value;">
  {$percent_dropdown}
  </select>	  
  </td>

  <td><select name="transactionType">
      <option value="">
      {$transactionType_options}
      </select>
  </td>


  <td>
    <select name="transaction_status">
    {$status_options}
    </select>
  </td>

  <td>{$transaction_buttons}</td>
</tr>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
