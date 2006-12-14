<table width="100%" cellspacing="0" border="0" class="comments">
<tr>
  <td valign="top" width="10%"><nobr>{$timeSheetItem_dateTimeSheetItem}</nobr></td>
  <td valign="top" width="19%"><nobr>{$timeSheetItem_timeSheetItemDuration} {$unit} * ${$timeSheetItem_rate}</nobr></td>
  <td valign="top" width="8%"><b>${$timeSheetItem_unit_times_rate}</b></td>
  <td valign="top">{$timeSheetItem_description}{$timeSheetItem_comment}</td>
  <td valign="top" width="1%" align="right"><nobr>
<form action="{$url_alloc_timeSheet}" method="post">
{$timeSheetItem_buttons}
<input type="hidden" name="timeSheetItem_timeSheetItemID" value="{$timeSheetItem_timeSheetItemID}">
<input type="hidden" name="timeSheetID" value="{$timeSheetItem_timeSheetID}">
</form>
</nobr></td>
</tr>
</table>
