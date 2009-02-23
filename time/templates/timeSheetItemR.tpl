<form action="{$url_alloc_timeSheetItem}" method="post">
<table width="100%" cellspacing="0" border="0" class="{$timeSheetItem_class}">
<tr>
  <td valign="top" width="10%"><nobr>{$timeSheetItem_dateTimeSheetItem}</nobr></td>
  <td valign="top" width="19%"><nobr>{$timeSheetItem_timeSheetItemDuration} {$unit} @ ${$timeSheetItem_rate} x {$timeSheetItem_multiplier}</nobr><br /></td>
  <td valign="top" width="8%"><b>${$timeSheetItem_unit_times_rate}</b></td>
  <td valign="top">{$timeSheetItem_description}{$timeSheetItem_comment}</td>
  <td valign="top" align="right">{$timeSheetItem_status}<nobr>{$timeSheetItem_buttons}</nobr></td>
</tr>
</table>
<input type="hidden" name="timeSheetItem_timeSheetItemID" value="{$timeSheetItem_timeSheetItemID}">
<input type="hidden" name="timeSheetID" value="{$timeSheetItem_timeSheetID}">
</form>

