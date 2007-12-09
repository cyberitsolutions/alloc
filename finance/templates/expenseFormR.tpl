<table width="100%" cellspacing="0" border="0" class="comments {$status}">
<tr>
  <td colspan="2">{$companyDetails}</td>
  <td colspan="2">{if $TPL["projectID"]}<a href="{$url_alloc_project}projectID={$projectID}">{$projectName}</a>{/}</td>
  <td width="1%" class="right nobr"><b>[{echo ucwords($TPL["status"])}]</b></td>
</tr>
<tr>
  <td width="40%">{$product}</td>
  <td width="25%" class="nobr">{$quantity}pcs. @ ${$amount} each  &nbsp;&nbsp;&nbsp;<b>${$lineTotal}</b></td>
  <td class="nobr">TF:{$tfID}</td>
  <td class="nobr" width="1%">{$transactionDate}</td> 
  <td width="1%" align="right" class="nobr">
	  &nbsp;&nbsp;{if check_optional_allow_edit()}
    <form method="post" action="{$url_alloc_expenseForm}">
    <input type="hidden" name="expenseFormID" value="{$expenseFormID}">
    <input type="hidden" name="transactionID" value="{$transactionID}">
    <input type="submit" name="edit" value="Edit">
    <input type="submit" name="delete" value="Delete" onClick="return confirm('Are you sure you want to delete this record?');">
    </form>
    {/}
  </td>
</tr>
</table>

