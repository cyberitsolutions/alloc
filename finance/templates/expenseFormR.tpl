<table cellspacing="0" border="1" class="panel {$status} {$expense_class}">
<tr>
  <td colspan="2">{=$companyDetails}</td>
  <td colspan="2">{if $projectID}<a href="{$url_alloc_project}projectID={$projectID}">{=$projectName}</a>{/}</td>
  <td width="1%" class="right nobr"><b>[{echo ucwords($status)}]</b></td>
</tr>
<tr>
  <td>{=$product}</td>
  <td class="nobr">{$quantity}pcs. @ {page::money($currencyTypeID,$amount,"%s%mo")} each  &nbsp;&nbsp;&nbsp;<b>{page::money($currencyTypeID,$lineTotal,"%s%mo")}</b></td>
  <td class="nobr">Source TF:{$fromTfIDLink} Dest TF: {$tfIDLink}</td>
  <td class="nobr" width="1%">{$transactionDate}</td> 
  <td width="1%" class="right nobr">
	  {if check_optional_allow_edit()}
    <form method="post" action="{$url_alloc_expenseForm}">
    <input type="hidden" name="expenseFormID" value="{$expenseFormID}">
    <input type="hidden" name="transactionID" value="{$transactionID}">
    <input type="submit" name="edit" value="Edit">
    <input type="submit" name="delete" value="Delete" class="delete_button">
    </form>
    {/}
  </td>
</tr>
</table>

