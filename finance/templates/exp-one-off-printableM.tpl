{show_header()}





<table border="1" cellpadding="9" align="left" cellspacing=0 bgcolor="white">





<tr>
  <td colspan="5"><h2><u>Cybersource Expense Form</u></h2></td>
  <td align="right">ID: <b>{$expenseFormID}</b></td>
</tr>

 {show_all_exp("templates/exp-one-off-printableR.tpl")}

<tr>
  <td colspan="6" align="right"><b>${$formTotal}</b></td>
</tr>

<tr>
  <td colspan="6">Payment Method: <b>{$paymentMethod}</b></td>
</tr>

<tr>
  <td colspan="6">Requested By: <b>{$user}</b></td>
</tr>

<tr valign="bottom">
  <td colspan="3">Reimbursement Required: <b>{$reimbursementRequiredOption}</b></td>
  <td colspan="3" rowspan="2" align="right">Signature:_______________________</td>
</tr>


<tr>
  <td colspan="6">&nbsp;</td>
</tr>





</table>


{show_footer()}

