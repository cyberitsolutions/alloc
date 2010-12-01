<form action="{$url_alloc_timeSheetItem}" method="post">
<table width="100%" cellspacing="0" border="0" class="{$timeSheetItem_class}">
<tr>
  <td valign="top" class="nobr" width="10%">{$timeSheetItem_dateTimeSheetItem}</td>
  <td valign="top" width="30%">{$timeSheetItem_timeSheetItemDuration} {$unit} @ {$currency}{$timeSheetItem_rate} x {$timeSheetItem_multiplier}</td>
  <td valign="top" width="10%"><b>{$currency}{$timeSheetItem_unit_times_rate}</b></td>
  <td valign="top">{$timeSheetItem_taskID} {$timeSheetItem_description}{$timeSheetItem_comment}</td>
  <td valign="top" align="right" width="12%">{$timeSheetItem_status}<nobr>{$timeSheetItem_buttons}</nobr></td>
</tr>
</table>
<input type="hidden" name="timeSheetItem_timeSheetItemID" value="{$timeSheetItem_timeSheetItemID}">
<input type="hidden" name="timeSheetID" value="{$timeSheetItem_timeSheetID}">
</form>

