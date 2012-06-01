{page::header()}
{page::toolbar()}



<form action="{$url_alloc_transactionRepeat}" method="post">

<table class="box">
  <tr>
    <th class="header" colspan="7">Create Repeating Expense{$statusLabel}
      <span>
        <a href="{$url_alloc_transactionRepeatList}">Return to Repeating Expenses List</a>
      </span>
    </th>
  </tr>
  <tr>
    <td><b>Basis</b></td>
    <td><b>Start Date{page::mandatory($transactionStartDate)}</b></td>
    <td><b>Finish Date{page::mandatory($transactionFinishDate)}</b></td>
    <td><b>Source TF{page::mandatory($fromTfID)}</b></td>
  </tr>
  <tr>
    <td><select name="paymentBasis" value="{$paymentBasis}">{$basisOptions}</select></td>
    <td>{page::calendar("transactionStartDate",$transactionStartDate)}</td>
    <td>{page::calendar("transactionFinishDate",$transactionFinishDate)}</td>
   	<td><select name="fromTfID"><option value="">{$fromTfOptions}</select></td>
  </tr>
	<tr>
	  <td><b>Product/Service{page::mandatory($product)}</b></td>
 	  <td><b>Amount{page::mandatory($amount)}</b></td>
 	  <td><b>Type</b></td>
    <td><b>Destination TF{page::mandatory($tfID)}</b></td>
	</tr>
	<tr>
 	  <td><input type="text" size="20" name="product" value="{$product}"></td>
 	  <td><input type="text" size="9" name="amount" value="{page::money($currencyTypeID,$amount,"%mo")}">
   	    <select name="currencyTypeID">{$currencyTypeOptions}</select></td>
   	<td><select name="transactionType">{$transactionTypeOptions}</select></td>
   	<td><select name="tfID"><option value="">{$tfOptions}</select></td>
 	</tr>

  <tr>
    <td colspan="2" rowspan="3" valign="top"><b>Company Details{page::mandatory($companyDetails)}</b><br>{page::textarea("companyDetails",$companyDetails, array("cols"=>40))}</td>
    <td rowspan="3" colspan="3">
      <nobr><b>Reminder email</b><br><input type="text" size="40" name="emailOne" value="{$emailOne}"></nobr><br>
      <nobr><b>Reminder email</b><br><input type="text" size="40" name="emailTwo" value="{$emailTwo}"></nobr>
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
    <td colspan="4"><b>Created By:</b> {=$user}</td>
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
    <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
    <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
    {$adminButtons}
  </tr>
</table>

<input type="hidden" name="transactionRepeatID" value="{$transactionRepeatID}">
<input type="hidden" name="sessID" value="{$sessID}">
</form>

{page::footer()}
