<script type="text/javascript" language="javascript">

// Make the XML request thing, specify the callback function 
function refreshInvoiceItemForm(radiobutton) \{
  if (radiobutton.value == "default") \{
    document.getElementById("new_invoice_item").style.display = 'inline';
    document.getElementById("new_invoice_item_from_timeSheet").style.display = 'none';
    document.getElementById("new_invoice_item_from_expenseForm").style.display = 'none';

  \} else if (radiobutton.value == "timeSheet") \{
    document.getElementById("new_invoice_item").style.display = 'none';
    document.getElementById("new_invoice_item_from_timeSheet").style.display = 'inline';
    document.getElementById("new_invoice_item_from_expenseForm").style.display = 'none';

  \} else if (radiobutton.value == "expenseForm") \{
    document.getElementById("new_invoice_item").style.display = 'none';
    document.getElementById("new_invoice_item_from_timeSheet").style.display = 'none';
    document.getElementById("new_invoice_item_from_expenseForm").style.display = 'inline';
  \}
\}

</script>




{$table_box}
  <tr>
    <th colspan="6">
      <input type="radio" name="change" id="change_default" value="default" onClick="refreshInvoiceItemForm(this)"{$radio1}> <label for="change_default">Create Invoice Item&nbsp;&nbsp;&nbsp;</label>
      <input type="radio" name="change" id="change_timeSheet" value="timeSheet" onClick="refreshInvoiceItemForm(this)"{$radio2}> <label for="change_timeSheet">From Time Sheet&nbsp;&nbsp;&nbsp;</label>
      <input type="radio" name="change" id="change_expenseForm" value="expenseForm" onClick="refreshInvoiceItemForm(this)"{$radio3}> <label for="change_expenseForm">From Expense Form&nbsp;&nbsp;&nbsp;</label>
    </th>
  </tr>
  <tr>
    <td>

      <div id="new_invoice_item"{$div1}>
      <table border="0" width="100%">
      <tr>
        <td>Date</td>
        <td>Qty</td>
        <td>Amount</td>
        <td>Total</td>
        <td>Memo</td>
      </tr>
      <tr>
        <td class="nobr">{get_calendar("iiDate",$TPL["invoiceItem_iiDate"])}</td>
        <td><input type="text" size="4" name="iiQuantity" value="{$invoiceItem_iiQuantity}"></td>
        <td><input type="text" size="7" name="iiUnitPrice" value="{$invoiceItem_iiUnitPrice}"></td>
        <td><input type="text" size="10" name="iiAmount" value="{$invoiceItem_iiAmount}"></td>
        <td><input type="text" size="50" name="iiMemo" value="{$invoiceItem_iiMemo}"></td>
        <td class="right nobr">{$invoiceItem_buttons}</td>
      </tr>
      </table>
      </div>

      <div id="new_invoice_item_from_timeSheet"{$div2}>
      <table border="0" width="100%">
      <tr>
        <td>Create Item from Time Sheet</td>
      </tr>
      <tr>
        <td><select name="timeSheetID"><option value=""></option>{$timeSheetOptions}</select></td>
        <td></td>
        <td align="right">{$invoiceItem_buttons}</td>
      </tr>
      </table>
      </div>

      <div id="new_invoice_item_from_expenseForm"{$div3}>
      <table border="0" width="100%">
      <tr>
        <td>Create Item from Expense Form</td>
      </tr>
      <tr>
        <td><select name="expenseFormID"><option value=""></option>{$expenseFormOptions}</select></td>
        <td></td>
        <td align="right">{$invoiceItem_buttons}</td>
      </tr>
      </table>
      </div>

    </td>
  </tr>


</table>



<input type="hidden" name="transactionID" value="{$invoiceItem_transactionID}">
<input type="hidden" name="status" value="{$invoiceItem_status}">
<input type="hidden" name="invoiceItemID" value="{$invoiceItem_invoiceItemID}">
<input type="hidden" name="invoiceID" value="{$invoiceItem_invoiceID}">

