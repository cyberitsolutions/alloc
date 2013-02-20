<table class="box">
  <tr>
    <th colspan="6">Create Invoice Item</th>
  </tr>
  <tr>
    <td colspan="6">
      {page::side_by_side_links(array("generic_ii"=>"Generic"
                                     ,"timeSheet_ii"=>"From Time Sheet"
                                     ,"expenseForm_ii"=>"From Expense Form"
                                     ,"productSale_ii"=>"From Sale")
                               ,$url_alloc_invoice."invoiceID=".$invoiceID)}    
    </td>
  </tr>
  <tr>
    <td>
      <div id="generic_ii"{$div1}>
      <table border="0" width="100%" cellpadding="5">
      <tr>
        <td style="width:15%">Date</td>
        <td style="width:1%">Qty</td>
        <td style="width:1%">&nbsp;</td>
        <td>Amount</td>
        {if config::get_config_item("taxPercent")}
        <td>Includes <br>{echo config::get_config_item("taxName")}</td>
        {/}
        <td>Memo</td>
      </tr>
      <tr>
        <td class="nobr">{page::calendar("iiDate",$invoiceItem_iiDate)}</td>
        <td><input type="text" size="4" name="iiQuantity" id="iiQuantity" value="{$invoiceItem_iiQuantity}"></td>
        <td>*</td>
        <td><input type="text" size="7" name="iiUnitPrice" id="iiUnitPrice" value="{$invoiceItem_iiUnitPrice}"></td>
        {if config::get_config_item("taxPercent")}
        {$invoiceItem_invoiceItemID or $invoiceItem_iiTax = "checked"}
        <td><input type="checkbox" name="iiTax" id="iiTax" value="{echo config::get_config_item("taxPercent")}" {$invoiceItem_iiTax>0 and print "checked"}></td>
        {/}
        <td><input type="text" size="60" name="iiMemo" value="{$invoiceItem_iiMemo}"></td>
        <td class="right nobr">{$invoiceItem_buttons}</td>
      </tr>
      </table>
      </div>

      <div id="timeSheet_ii"{$div2}>
      <table border="0" width="100%">
      <tr>
        <td>Create Item from Time Sheet</td>
      </tr>
      <tr>
        <td>
          <select name="timeSheetID"><option value=""></option>{$timeSheetOptions}</select>
          <input type='checkbox' name='split_timeSheet' value='1'>Use Time Sheet Items
        </td>
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
        <td>
          <select name="expenseFormID"><option value=""></option>{$expenseFormOptions}</select>
          <input type='checkbox' name='split_expenseForm' value='1'>Use Expense Form Items
        </td>
        <td></td>
        <td align="right">{$invoiceItem_buttons}</td>
      </tr>
      </table>
      </div>

      <div id="productSale_ii"{$div4}>
      <table border="0" width="100%">
      <tr>
        <td>Create Item from Sale</td>
      </tr>
      <tr>
        <td>
          <select name="productSaleID"><option value=""></option>{$productSaleOptions}</select>
          <input type='checkbox' name='split_productSale' value='1'>Use Sale Items
        </td>
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

