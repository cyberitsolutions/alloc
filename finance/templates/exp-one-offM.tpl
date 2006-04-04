{:show_header}
  {:show_toolbar}

<form name="costForm" action="{url_alloc_expOneOff}" method="post">

<strong style="color: red; text-align:center; ">{error}</strong>

{table_box}
  <tr> 
    <th>Expense Form</th>
    <th class="right"><a href="{url_alloc_expOneOff}&expenseFormID={expenseFormID}&printVersion=true" TARGET="_blank">Printer Friendly Version</a>
  </tr>
  <tr>
    <td align="right">Total:</td><td>${formTotal}</td>
  </tr>
  <tr>
    <td align="right">Created By:</td><td>{user}</td>
  </tr>
  <tr>
    <td align="right">Expense Form ID:</td><td>{expenseFormID}</td>
  </tr>
  <tr>
    <td align="right">Payment Method:</td><td>{paymentMethodOptions}</td>
  </tr>
  <tr>
    <td align="right">Reimbursement Required:</td><td>{reimbursementRequiredOption}</td>
  </tr>
  <tr>
    <td align="right">Cheque Number:</td><td>{chequeNumberInput}</td>
  </tr>
  <tr>
    <td align="right">Cheque Date:</td><td>{chequeDateInput}</td>
  </tr>
  <tr>
    <td colspan="2" align="center">
      {expenseFormButtons}
    </td>
  </tr>
      
</table>


{optional:show_line_item_add}

{table_box}
  <tr>
    <th colspan="6">Create Expense Form Line Items</th>
  </tr>

  <tr>
    <td colspan="4"><b>Enter the company name, address and supplier ID</b></td> 
    <td colspan="2"><b>Project</b></td> 
  </tr>
  <tr>
    <td colspan="4"><input type="text" size="70" name="companyDetails" value="{companyDetails}"></td>
    <td colspan="2"><select name="projectID" value="{projectID}">{projectOptions}</select></td>
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
          <td><nobr><input type="text" size="11" name="transactionDate" value="{transactionDate}">
                    <input type="button" onClick="transactionDate.value='{today}'" value="Today"></nobr>
          <td><input type="text" size="25" name="product" value="{product}"></td>
          <td><select name="tfID" value="{tfID}">{tfOptions}</select></td>
          <td><input type="text" size="9" name="quantity" value="{quantity}"></td>
          <td><input type="text" size="9" name="amount" value="{amount}"></td>

          </td>
		    </tr>
      </table>
      <input type="hidden" name="transactionID" value="{transactionID}">
    </td>
  </tr>
  <tr>
    <td colspan="6" align="center">
      <b><input type="submit" name="add" value="Add Expense Form Line Item"></b>&nbsp;
     </td>
  </tr>

</table>
{/optional}

<input type="hidden" name="status" value="pending">
<input type="hidden" name="expenseFormID" value="{expenseFormID}">
</form>

{optional:has_line_items}
{table_box}
  <tr>
    <th colspan="7">Expense Form Line Items</th>
  </tr>
 {:show_all_exp templates/exp-one-offR.tpl}
</table>
{/optional}


{:show_footer}

