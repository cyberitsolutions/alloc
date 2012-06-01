<div class="panel {$status} {$expense_class} corner">
<table cellspacing="0" border="0">
<tr>
  <td colspan="2">{=$companyDetails}</td>
  <td colspan="2">{if $projectID}<a href="{$url_alloc_project}projectID={$projectID}">{=$projectName}</a>{/}</td>
  <td width="1%" class="right nobr"><b>[{echo ucwords($status)}]</b></td>
</tr>
<tr>
  <td>{=$product}</td>
  <td class="nobr" width="20%">{$quantity}pcs. @ {page::money($currencyTypeID,$amount,"%s%mo")} each  &nbsp;&nbsp;&nbsp;<b>{page::money($currencyTypeID,$lineTotal,"%s%mo %C")}</b></td>
  <td class="nobr" width="20%">Source TF:{$fromTfIDLink} Dest TF: {$tfIDLink}</td>
  <td class="nobr" width="10%">{$transactionDate}</td> 
  <td width="1%" class="right nobr">
	  {if check_optional_allow_edit()}
    <form method="post" action="{$url_alloc_expenseForm}">
    <input type="hidden" name="expenseFormID" value="{$expenseFormID}">
    <input type="hidden" name="transactionID" value="{$transactionID}">
    <button type="submit" name="edit" value="1">Edit<i class="icon-edit"></i></button>
    <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
    <input type="hidden" name="sessID" value="{$sessID}">
    </form>
    {/}
  </td>
</tr>
</table>
</div>

