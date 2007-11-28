<table width="100%" cellspacing="0" border="0" class="comments">
<tr>
  <td colspan="7">{$companyDetails}</td>
</tr>
<tr>
  <td width="40%">{$product}</td>
  <td width="25%" class="nobr">{$quantity}pcs. @ ${$amount} each  &nbsp;&nbsp;&nbsp;<b>${$lineTotal}</b></td>
  <td class="nobr" width="1%">{$transactionDate}</td> 
  <td>TF: {$tfID}</td>
  {if $TPL["projectID"]}<td>Project: <a href="{$url_alloc_project}projectID={$projectID}">{$projectID}</a></td>{/}
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

