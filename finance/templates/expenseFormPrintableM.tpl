<table border="1" cellpadding="9" cellspacing="0" bgcolor="white">
<tr>
  <td colspan="5"><h2><u>Expense Form</u></h2></td>
  <td align="right">ID: <b>{$expenseFormID}</b></td>
</tr>
 {show_all_exp("templates/expenseFormPrintableR.tpl")}
<tr>
  <td colspan="6" align="right"><b>${$formTotal}</b></td>
</tr>
<tr>
  <td colspan="6" align="left"><b>{$rr_label}</b></td>
</tr>
<tr>
  <td colspan="6" align="left">Seek Client Reimbursement: <b>{$seekClientReimbursementLabel}</b></td>
</tr>
<tr>
  <td><b>Comment</b></td>
  <td colspan="5">{$expenseFormComment}</td>
</tr>
<tr>
  <td colspan="3" align="left">User: <b>{$user}</b></td>
  <td colspan="3" align="left">Signature:</td>
</tr>
</table>

