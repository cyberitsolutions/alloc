{:show_header}
  {:show_toolbar}

<form name="costForm" action="{url_alloc_expOneOff}" method="post">

<strong style="color: red; text-align:center; ">{error}</strong>

{table_box}
  <tr>
    <th colspan="5">Create Expense Form Line Items</th>
    <th class="right"><a href="{url_alloc_expOneOff}&expenseFormID={expenseFormID}&printVersion=true" TARGET="_blank">Printer Friendly Version</a>
  </tr>

  {optional:allow_edit}
  <tr>
    <td colspan="6"><b>Enter the Company details, including name, address and supplier ID (if applicable).</b></td> 
  </tr>
  <tr>
    <td colspan="6"><input type="text" size="90" name="companyDetails" value="{companyDetails}"></td>
  </tr>

  <tr rowspan="2">
	  <td colspan="6">
	    <table height="100%" width="100%">
		    <tr>
          <td><b>Date Incurred</b></td>
          <td><b>Product</b></td>
          <td><b>TF</b></td>
          <td><b>Project</b></td>
          <td><b>Quantity</b></td>
          <td><b>Price</b></td>
		    </tr>
        <tr>
          <td><nobr><input type="text" size="11" name="transactionDate" value="{transactionDate}">
                    <input type="button" onClick="transactionDate.value='{today}'" value="Today"></nobr>
          <td><input type="text" size="25" name="product" value="{product}"></td>
          <td><select name="tfID" value="{tfID}">{tfOptions}</select></td>
          <td><select width="25" name="projectID" value="{projectID}">{projectOptions}</select></td>
          <td><input type="text" size="9" name="quantity" value="{quantity}"></td>
          <td><input type="text" size="9" name="amount" value="{amount}"></td>

          </td>
		    </tr>
      </table>
      <input type="hidden" name="transactionID" value="{transactionID}">
    </td>
  </tr>
  <tr>
    <td colspan="6" align="center">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="6" align="center">
      <b><input type="submit" name="add" value="Add Expense Form Line Item"></b>&nbsp;
     </td>
  </tr>
  {/optional}

</table>

{table_box}
  <tr>
    <th colspan="6">Expense Form Line Items</th>
  </tr>
    

  
  
 {:show_all_exp templates/exp-one-offR.tpl}


  <tr>
    <td><nobr><strong>TOTAL: &nbsp;${formTotal}</strong></nobr></td>
    <td colspan="1" align="right"><nobr><b>Payment Method</b>
                                  {optional:allow_edit}<select name="paymentMethod" value="{paymentMethod}">{paymentOptions}</select>&nbsp;{/optional}
                                  {optional:no_edit}{paymentMethod}{/optional}
    </td>
    <td colspan="4" align="right">Reimbursement required? <input type="checkbox" name="reimbursementRequired" value="1"{reimbursementRequired_checked}> &nbsp;&nbsp;&nbsp; </td>
  </tr>

  <tr>
    <td colspan="3">This expense form ID is: <b>{expenseFormID}</b></td>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td colspan="6">Created By: <b>{user}</b></td>
  </tr>


  {optional:allow_edit}
  <tr>
	  <td colspan="6" align="center">
      <input height="30" type="submit" name="save" value="Save Expense Form">
      {optional:allow_edit}
      <input bgcolor="#339999" width="200" height="30" type="submit" name="cancel" value="Delete Expense Form">
      {/optional}
    </td>
  </tr>
  {/optional}
  
</table>

{table_box}
  {:show_admin_buttons}
</table>


<input type="hidden" name="status" value="pending">
<input type="hidden" name="expenseFormID" value="{expenseFormID}">


</form>

{:show_footer}

