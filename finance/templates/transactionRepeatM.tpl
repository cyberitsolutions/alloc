{:show_header}
  {:show_toolbar}

<br>

<form action="{url_alloc_transactionRepeat}john={john}" method="post">

<h1>Create an Expense that Repeats on a Fixed Basis</h1>

{error}
<br><br>

<table border="0" cellspacing=0 width="100%" bgcolor="#eeeeee" cellpadding="5">






	<tr>
  	  <td><b>Product / Service</td>
  	  <td colspan="2"><b>Amount
	  &nbsp;&nbsp;&nbsp;
	  &nbsp;&nbsp;&nbsp;
	  &nbsp;&nbsp;&nbsp;
	  &nbsp;&nbsp;&nbsp;
	  &nbsp;&nbsp;&nbsp;
	  <b>TF</td>
  	  <td rowspan="2">
	    <table width="70%" height="100%">
	      <tr>
	        <td align="center"><b>Basis</td>
	        <td align="center"><b>Start Date</td>
	  	<td align="center"><b>Finish Date</td>
	      <tr>
     	      <tr>
 	        <td align="center"><select name="paymentBasis" value="{paymentBasis}">{basisOptions}</select></td>
  	        <td align="center">
	        <input type="text" name="transactionStartDate" size="11" value="{transactionStartDate}"></td>
  	  	<td align="center">
    	  	<input type="text" name="transactionFinishDate" size="11" value="{transactionFinishDate}">
  	  	</td>
	      </tr>

	    </table>
  	  </td>
  
	</tr>



	<tr>
  	  <td><input type="text" size="20" name="product" value="{product}"></td>
  	  <td colspan="2"><input type="text" size="9" name="amount" value="{amount}">
  	  &nbsp;&nbsp;&nbsp;
   	  <select name="tfID" value="{tfID}">{tfOptions}</select>
	  </td>
 	</tr>








<tr>
  <td rowspan="3"><b>Company Details</td>
  <td colspan="2" rowspan="3"><textarea rows="4" cols="30" name="companyDetails" wrap="virtual">{companyDetails}</textarea></td>
  <td rwospan="3"><nobr>Reminder email (optional) to 
  <input type="text" size="30" name="emailOne" value="{emailOne}"></nobr><br>
  <nobr>Reminder email (optional) to 
  <input type="text" size="30" name="emailTwo" value="{emailTwo}"></nobr></td> 
</tr>

<tr><td>&nbsp;</td></tr>
<tr><td>&nbsp;</td></tr>

<tr>
<td><b>Date Incurred</b></td>
<td colspan="3"><input type="text" size="11" name="dateEntered" value="{dateEntered}">
<input type="button" onClick="dateEntered.value='{today}'" value="Today"></td>
</tr>


<tr>
  <td colspan="4">Form ID: {transactionRepeatID}</td>
</tr>


<tr>
  <td colspan="4">Created By: {user}</td>
</tr>


<tr>
  <td colspan="4">Reimbursement required?
  <input type="checkbox" name="reimbursementRequired" value="1"{reimbursementRequired_checked}></td>
</tr>


<tr>
  <td colspan="4"><hr width="100%"></td>
</tr>



<tr>
  <td><input type="submit" name="save" value="&nbsp;&nbsp;&nbsp;Save&nbsp;&nbsp;&nbsp;"></td>
  <td colspan="3"><input type="submit" name="delete" value="Delete Record" onClick="return confirm('Are you sure you want to delete this record?')"></td>
</tr>

</table>

<br>
<a href="{url_alloc_transactionRepeatList}tfID={tfID}">Return to Repeating Expenses List</a>

<input type="hidden" name="status" value="pending">
<input type="hidden" name="transactionRepeatID" value="{transactionRepeatID}">


<br><br>
</form>
{:show_footer}
