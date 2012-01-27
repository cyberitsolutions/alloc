<tr> 
  <td>Comments for management<br>viewing only</td>
  <td colspan="3">{page::textarea("managementComments",$person_managementComments)}</td>
</tr>
<tr>
  <td>Default rate</td>
  <td><input type="text" name="defaultTimeSheetRate" value={page::money(config::get_config_item('currency'),$person_defaultTimeSheetRate,"%mo")}></td>
  <td>Default Rate Units</td>
  <td><select name="defaultTimeSheetRateUnitID">{$timeSheetRateUnit_select}</select></td>
</tr>
