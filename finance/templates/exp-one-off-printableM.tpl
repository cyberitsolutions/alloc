{:show_header}





<table border="1" cellpadding="9" align="left" cellspacing=0 bgcolor="white">





<tr>
 <td colspan="6"><h2><u>Cybersource Expense Form</u></h2></td>
</tr>




<!--************THE SUPPOSED CONTENT ARENA******************-->

 {:show_all_exp templates/exp-one-off-printableR.tpl}


<!--************THE SUPPOSED CONTENT ARENA******************-->


<tr>
  <td colspan="3">Expense form ID: <b>{expenseFormID}</b></td>
  <td colspan="3" align="right"><strong style="font-size: large">TOTAL: &nbsp;${formTotal}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</strong></td>
</tr>

<tr>
  <td colspan="6">Payment Method: <b>{paymentMethod}</b></td>
</tr>

<tr>
  <td colspan="6">Requested By: <b>{user}</b></td>
</tr>

<tr valign="bottom">
  <td colspan="3">Reimbursement Required:____ (tick)</td>
  <td colspan="3" rowspan="2" align="right">Signature:_______________________</td>
</tr>


<tr>
  <td colspan="6">&nbsp;</td>
</tr>





</table>


{:show_footer}

