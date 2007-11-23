{show_header()}
  {show_toolbar()}

<form name="costForm" action="{$url_alloc_expenseForm}" method="post">

<strong style="color: red; text-align:center; ">{$error}</strong>

{$table_box}
  <tr> 
    <th>Expense Form</th>
    <th class="right" colspan="3"><a href="{$url_alloc_expenseForm}expenseFormID={$expenseFormID}&printVersion=true" TARGET="_blank">Printer Friendly Version</a>
    <a href="{$url_alloc_expenseFormList}">Expense Form List</a>
    </th>
  </tr>
  <tr>
    <td align="right">Created By:</td><td>{$user}</td>
    <td align="right">Client:</td><td>{$field_clientID}</td>
  </tr>
  <tr>
    <td align="right">Total:</td><td>${$formTotal}</td>
    <td align="right">Seek Client Reimbursement:</td><td>{$seekClientReimbursementOption}</td>
  </tr>
  <tr>
    <td align="right">Expense Form ID:</td><td>{$expenseFormID}</td>
    <td align="right">{$invoice_label}</td><td>{$attach_to_invoice_button}{$invoice_link}</td>
  </tr>
  <tr>
    <td align="right" valign="top">Payment:</td><td colspan="2">{$reimbursementRequiredOption}</td>
  </tr>
  <tr>
    <td colspan="4">&nbsp;</td>
  </tr>
  <tr>
    <td style="text-align:right">Comment:</td>
    <td colspan="3">
    <textarea rows="5" cols="70" wrap="virtual" name="expenseFormComment">{$expenseFormComment}</textarea></td>
  </tr>
  <tr>
    <td colspan="4" align="center">
      {$expenseFormButtons}
    </td>
  </tr>
      
</table>


{if check_optional_show_line_item_add()}

{$table_box}
  <tr>
    <th colspan="6">Create Expense Form Line Items</th>
  </tr>

  <tr>
    <td colspan="4"><b>Enter the company name and address</b></td> 
    <td colspan="2"><b>Project</b></td> 
  </tr>
  <tr>
    <td colspan="4"><input type="text" size="70" name="companyDetails" value="{$companyDetails}"></td>
    <td colspan="2"><select name="projectID" value="{$projectID}">{$projectOptions}</select></td>
  </tr>

  <tr>
	  <td colspan="6">
	    <table height="100%" width="100%">
		    <tr>
          <td><b>Date Incurred</b></td>
          <td><b>Product</b></td>
          <td><b>TF</b></td>
          <td><b>Quantity</b></td>
          <td><b>Price</b></td>
		    </tr>
        <tr>
          <td><nobr>{get_calendar("transactionDate",$TPL["transactionDate"])}</nobr>
          <td><input type="text" size="25" name="product" value="{$product}"></td>
          <td><select name="tfID" value="{$tfID}">{$tfOptions}</select></td>
          <td><input type="text" size="9" name="quantity" value="{$quantity}"></td>
          <td><input type="text" size="9" name="amount" value="{$amount}"></td>

          </td>
		    </tr>
      </table>
      <input type="hidden" name="transactionID" value="{$transactionID}">
    </td>
  </tr>
  <tr>
    <td colspan="6" align="center">
      <b><input type="submit" name="add" value="Add Expense Form Line Item"></b>&nbsp;
     </td>
  </tr>

</table>
{/}

<input type="hidden" name="status" value="pending">
<input type="hidden" name="expenseFormID" value="{$expenseFormID}">
</form>

{if check_optional_has_line_items()}
{$table_box}
  <tr>
    <th>Expense Form Line Items</th>
  </tr>
  <tr>
    <td>{show_all_exp("templates/expenseFormR.tpl")}</td>
  </tr>
</table>
{/}


{show_footer()}

