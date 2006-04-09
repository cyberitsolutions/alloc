
<tr>
  <td colspan="7">{companyDetails}</td>
</tr>
<tr>
  <td>{transactionDate}</td> 
  <td>{product}</td>
  <td>TF: {tfID}</td>
  <td>Project: {projectID}</td>
  <td>{quantity}pcs. @ ${amount} each  &nbsp;&nbsp;&nbsp;<b>${lineTotal}</b></td>
  <td>{status}</td>
  <td align="right">
	  &nbsp;&nbsp;{optional:allow_edit}
    <form method="post" action="{url_alloc_expOneOff}">
    <input type="hidden" name="expenseFormID" value="{expenseFormID}">
    <input type="hidden" name="transactionID" value="{transactionID}">
    <input type="submit" name="edit" value="Edit">
    <input type="submit" name="delete" value="Delete">
    </form>
    {/optional}
  </td>
</tr>









