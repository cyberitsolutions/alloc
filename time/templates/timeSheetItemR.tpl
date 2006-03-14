<tr>
  <td colspan="5"><hr width="100%"></td>
</tr>
<tr>
  <td valign="top" width="1%"><nobr>{timeSheetItem_dateTimeSheetItem}</nobr></td>
  <td valign="top" width="1%"><nobr>${timeSheetItem_unit_times_rate}  ({timeSheetItem_timeSheetItemDuration} * ${timeSheetItem_rate})</nobr></td>
  <td valign="top">{timeSheetItem_description}{timeSheetItem_comment}</td>
  <td valign="top" align="right"><nobr>
<form action="{url_alloc_timeSheet}" method="post">
{timeSheetItem_buttons}
<input type="hidden" name="timeSheetItem_timeSheetItemID" value="{timeSheetItem_timeSheetItemID}">
<input type="hidden" name="timeSheetID" value="{timeSheetItem_timeSheetID}">
</form>
</nobr></td>
</tr>
