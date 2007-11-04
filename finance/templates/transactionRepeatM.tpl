{show_header()}
{show_toolbar()}



<form action="{$url_alloc_transactionRepeat}john={$john}" method="post">

{$table_box}
  <tr>
    <th colspan="6">Create Repeating Expense{$statusLabel}</th>
    <th class="right"><a href="{$url_alloc_transactionRepeatList}tfID={$tfID}">Return to Repeating Expenses List</a></th>
  </tr>
  <tr>
    <td><b>Basis</b></td>
    <td><b>Start Date</b></td>
    <td><b>Finish Date</b></td>
  </tr>
  <tr>
    <td><select name="paymentBasis" value="{$paymentBasis}">{$basisOptions}</select></td>
    <td>{get_calendar("transactionStartDate",$TPL["transactionStartDate"])}</td>
    <td>{get_calendar("transactionFinishDate",$TPL["transactionFinishDate"])}</td>
  </tr>
	<tr>
	  <td></td>
	  <td><b>Product/Service</b></td>
 	  <td><b>Amount</b></td>
 	  <td><b>Type</b></td>
    <td><b>TF</b></td>
	</tr>
	<tr>
    <td></td>
 	  <td><input type="text" size="20" name="product" value="{$product}"></td>
 	  <td><input type="text" size="9" name="amount" value="{$amount}"></td>
   	<td><select name="transactionType">{$transactionTypeOptions}</select></td>
   	<td><select name="tfID">{$tfOptions}</select></td>
 	</tr>

  <tr>
    <td rowspan="3"><b>Company Details</b></td>
    <td colspan="2" rowspan="3"><textarea rows="4" cols="40" name="companyDetails" wrap="virtual">{$companyDetails}</textarea></td>
    <td rowspan="3" colspan="3">
      <nobr><b>Reminder email</b><br/><input type="text" size="40" name="emailOne" value="{$emailOne}"></nobr><br>
      <nobr><b>Reminder email</b><br/><input type="text" size="40" name="emailTwo" value="{$emailTwo}"></nobr>
    </td> 
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr><td>&nbsp;</td></tr>
  <tr>
  </tr>
  <tr>
    <td colspan="4"><b>Form ID:</b> {$transactionRepeatID}</td>
  </tr>
  <tr>
    <td colspan="4"><b>Created By:</b> {$user}</td>
  </tr>
  <tr>
    <td colspan="4"><b>Date Created:</b> {$transactionRepeatCreatedTime}</td>
  </tr>
  <tr>
    <td colspan="4"><b>Status:</b> {$status}</td>
  </tr>
  <tr>
    <td colspan="4"><b>Reimbursement required?</b>
    <input type="checkbox" name="reimbursementRequired" value="1"{$reimbursementRequired_checked}></td>
  </tr>
  <tr>
    <td align="center" colspan="6">
    {$adminButtons}
    <input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;">
    <input type="submit" name="delete" value="Delete Record" onClick="return confirm('Are you sure you want to delete this record?')"></td>
  </tr>
</table>

<input type="hidden" name="transactionRepeatID" value="{$transactionRepeatID}">
</form>

{show_footer()}
