
<tr>
  <td colspan="6">{companyDetails}</td>
</tr>
<tr>
  <td>{transactionDate}</td> 
  <td>{product}</td>
  <td>TF: {tfID}</td>
  <td>Project: {projectID}</td>
  <td>{quantity}pcs. @ ${amount} each</td>
  <td>
	  &nbsp;&nbsp;{optional:allow_edit}
   	<a href="{url_alloc_expOneOff}&transactionID={transactionID}&expenseFormID={expenseFormID}&edit=true">edit</a>  
   	&nbsp;|&nbsp;  
   	<a href="{url_alloc_expOneOff}&transactionID={transactionID}&delete=true&expenseFormID={expenseFormID}">delete</a>
    {/optional}
  </td>
</tr>
<tr>
  <td colspan="6"><hr></td>
</tr>









