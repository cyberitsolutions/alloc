
<table border="0" cellspacing="0" cellpadding="2" width="100%">
<tr>
  <td colspan="3"><b>Time Sheet Statistics</b></td>
</tr>
<tr>
  <td>Sum Previous Fortnight:</td><td>{$hours_sum}hrs</td><td>${$dollars_sum}</td>
</tr>
<tr>
  <td>Average Per Fortnight:</td><td>{$hours_avg}hrs</td><td>${$dollars_avg}</td>
</tr>
</table>
<br/>
{$fields = $TPL["this"]->time_sheet_items()}
{if count($fields["lines"]) > 0}
<table border="0" cellspacing="0" cellpadding="2" width="100%">
<tr>
  <td colspan="3"><b>Current Time Sheets</b></td>
</tr>
{foreach $fields["lines"] as $line}
<tr>
  <td>{$line.projectName}</td>
  <td class="nobr">{$line.dateFrom}</td>
  <td class="nobr right">{$line.total_dollars}</td>
  <td class="noprint">{$line.status}</td>
</tr>
{/}
<tr>
<td >Total</td>
<td colspan="2" class="grand_total"> ${echo $fields["total"]}</td>
</tr>
{/}
</table>
