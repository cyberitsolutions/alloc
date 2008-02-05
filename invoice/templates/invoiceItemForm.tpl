
{$table_box}
  <tr>
    <th colspan="6">Create Invoice Item</th>
  </tr>
  <tr>
    <td colspan="6">
      {get_side_by_side_links(array("generic_ii"=>"Generic"
                                   ,"timeSheet_ii"=>"From Time Sheet"
                                   ,"expenseForm_ii"=>"From Expense Form")
                             ,$TPL["sbs_link"])}    
    </td>
  </tr>
  <tr>
    <td>
      <div id="generic_ii"{$div1}>
      <table border="0" width="100%">
      <tr>
        <td style="width:10%">Date</td>
        <td style="width:1%">Qty</td>
        <td style="width:1%">&nbsp;</td>
        <td>Amount</td>
        <td>Memo</td>
      </tr>
      <tr>
        <td class="nobr">{get_calendar("iiDate",$TPL["invoiceItem_iiDate"])}&nbsp;&nbsp;</td>
        <td><input type="text" size="4" name="iiQuantity" value="{$invoiceItem_iiQuantity}"></td>
        <td>*</td>
        <td><input type="text" size="7" name="iiUnitPrice" value="{$invoiceItem_iiUnitPrice}"></td>
        <td><input type="text" size="60" name="iiMemo" value="{$invoiceItem_iiMemo}"></td>
        <td class="right nobr">{$invoiceItem_buttons}</td>
      </tr>
      </table>
      </div>

      <div id="timeSheet_ii"{$div2}>
      <table border="0" width="100%">
      <tr>
        <td>Create Item from Time Sheet</td>
        <td>Time Sheet Max Amount</td>
      </tr>
      <tr>
        <td><select name="timeSheetID"><option value=""></option>{$timeSheetOptions}</select></td>
        <td><input type="text" size="10" name="iiAmount" value="{$invoiceItem_iiAmount}"></td>
        <td align="right">{$invoiceItem_buttons}</td>
      </tr>
      </table>
      </div>

      <div id="expenseForm_ii"{$div3}>
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

