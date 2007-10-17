<table width="100%" cellspacing="0" border="0" class="comments">
<tr>
  <td colspan="7">{$companyDetails}</td>
</tr>
<tr>
  <td>{$transactionDate}</td> 
  <td>{$product}</td>
  <td>TF: {$tfID}</td>
  <td>Project: {$projectID}</td>
  <td>{$quantity}pcs. @ ${$amount} each  &nbsp;&nbsp;&nbsp;<b>${$lineTotal}</b></td>
  <td width="1%">{$status}</td>
  <td width="1%" align="right" class="nobr">
	  &nbsp;&nbsp;{if check_optional_allow_edit()}
    <form method="post" action="{$url_alloc_expenseForm}">
    <input type="hidden" name="expenseFormID" value="{$expenseFormID}">
    <input type="hidden" name="transactionID" value="{$transactionID}">
    <input type="submit" name="edit" value="Edit">
    <input type="submit" name="delete" value="Delete">
    </form>
    {/}
  </td>
</tr>
</table>

