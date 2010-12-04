{page::header()}
<style>
  body {
    background-color:white; background-image:none;
  }
</style>
<table border="1" cellpadding="9" cellspacing="0" bgcolor="white">
<tr>
  <td colspan="5"><h2><u>Expense Form</u></h2></td>
  <td align="right">ID: {$expenseFormID}</td>
</tr>
 {show_all_exp("templates/expenseFormPrintableR.tpl")}
<tr>
  <td colspan="6" align="right"><b>{$formTotal}</b></td>
</tr>
<tr>
  <td colspan="6" align="left">{$rr_label}</td>
</tr>
<tr>
  <td colspan="1" align="left">Seek Client Reimbursement: {$seekClientReimbursementLabel}</td>
  <td colspan="5">{$printer_clientID}&nbsp;</td>
</tr>
<tr>
  <td>Comment</td>
  <td colspan="5">{$expenseFormComment}&nbsp;</td>
</tr>
<tr>
  <td colspan="1" align="left">{$user}</td>
  <td colspan="5" align="left">Signature:</td>
</tr>
</table>
{page::footer()}
