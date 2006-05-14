{:show_header}
{:show_toolbar}



<form action="{url_alloc_transactionRepeat}john={john}" method="post">

{table_box}
  <tr>
    <th colspan="5">Create Repeating Expense</th>
    <th class="right"><a href="{url_alloc_transactionRepeatList}tfID={tfID}">Return to Repeating Expenses List</a></th>
  </tr>
	<tr>
	  <td><b>Product / Service</b></td>
 	  <td><b>Amount</b></td>
    <td><b>TF</b></td>
    <td><b>Basis</b></td>
    <td><b>Start Date</b></td>
    <td><b>Finish Date</b></td>
	</tr>
	<tr>
 	  <td><input type="text" size="20" name="product" value="{product}"></td>
 	  <td><input type="text" size="9" name="amount" value="{amount}"></td>
   	<td><select name="tfID" value="{tfID}">{tfOptions}</select></td>
    <td><select name="paymentBasis" value="{paymentBasis}">{basisOptions}</select></td>
    <td><input type="text" name="transactionStartDate" size="11" value="{transactionStartDate}"></td>
    <td><input type="text" name="transactionFinishDate" size="11" value="{transactionFinishDate}"></td>
 	</tr>

  <tr>
    <td rowspan="3"><b>Company Details</b></td>
    <td colspan="2" rowspan="3"><textarea rows="4" cols="30" name="companyDetails" wrap="virtual">{companyDetails}</textarea></td>
    <td rowspan="3">
      <nobr><b>Reminder email</b> <input type="text" size="30" name="emailOne" value="{emailOne}"></nobr><br>
      <nobr><b>Reminder email</b> <input type="text" size="30" name="emailTwo" value="{emailTwo}"></nobr>
    </td> 
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr>
    <td><b>Date Incurred</b></td>
    <td colspan="3"><input type="text" size="11" name="dateEntered" value="{dateEntered}"><input type="button" onClick="dateEntered.value='{today}'" value="Today"></td>
  </tr>
  <tr>
    <td colspan="4"><b>Form ID:</b> {transactionRepeatID}</td>
  </tr>
  <tr>
    <td colspan="4"><b>Created By:</b> {user}</td>
  </tr>
  <tr>
    <td colspan="4"><b>Reimbursement required?</b>
    <input type="checkbox" name="reimbursementRequired" value="1"{reimbursementRequired_checked}></td>
  </tr>
  <tr>
    <td align="right" colspan="3"><input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;"></td>
    <td colspan="3"><input type="submit" name="delete" value="Delete Record" onClick="return confirm('Are you sure you want to delete this record?')"></td>
  </tr>
</table>


<input type="hidden" name="status" value="pending">
<input type="hidden" name="transactionRepeatID" value="{transactionRepeatID}">


<br><br>
</form>
{:show_footer}
