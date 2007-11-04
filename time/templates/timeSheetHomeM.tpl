
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
<table border="0" cellspacing="0" cellpadding="2" width="100%">
<tr>
  <td colspan="3"><b>Current Time Sheets</b></td>
</tr>
  {$TPL["this"]->show_time_sheets("timeSheetHomeR.tpl")}
</table>
