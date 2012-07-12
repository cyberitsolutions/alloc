<form action="{$url_alloc_project}" method="post">
<tr>
  <td width="30%">
    <select name="commission_tfID">
      <option></option>
      {show_tf_options("commission_tfID")}
    </select>
  </td>
  <td width="50%"><input type="text" size="5" name="commission_commissionPercent" value="{$commission_commissionPercent}">%<td>
  <td class="right">
    <button type="submit" name="commission_save" value="1" class="save_button">{$save_label}<i class="icon-plus-sign"></i></button>
    {if !$commission_new}
      <button type="submit" name="commission_delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
    {/}
  </td>
</tr>
<input type="hidden" name="commission_projectCommissionPersonID" value="{$commission_projectCommissionPersonID}">
<input type="hidden" name="projectID" value="{$commission_projectID}">
<input type="hidden" name="sbs_link" value="commissions">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
