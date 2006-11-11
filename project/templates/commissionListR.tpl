<form action="{$url_alloc_project}" method="post">
<tr>
  <td width="30%">
    <select name="commission_tfID">
      <option></option>
      {show_tf_options("commission_tfID")}
    </select>
  </td>
  <td width="50%"><input type="text" size="5" name="commission_commissionPercent" value="{$commission_commissionPercent}">%<td>
  <td>
    {$commission_list_buttons}
  </td>
</tr>
<input type="hidden" name="commission_projectCommissionPersonID" value ="{$commission_projectCommissionPersonID}">
<input type="hidden" name="projectID" value="{$commission_projectID}">
</form>
