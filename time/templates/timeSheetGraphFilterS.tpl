<form action="{$url_form_action}" method="get">
<table align="center" class="filter corner">
  <tr>
    <td>Assigned To</td> 
    <td>Date From</td> 
    <td>Date To</td> 
  </tr>
  <tr>
    <td valign="top"><select id="personID" name="personID">{$personOptions}</select></td>  
    <td valign="top">{page::calendar("dateFrom",$dateFrom)}</td>  
    <td valign="top">{page::calendar("dateTo",$dateTo)}</td>  
  </tr>
  <tr>
    <td colspan="2">
      Group by:&nbsp;&nbsp;
      <label><input type="radio" name="groupBy" value="day"   {$groupBy == "day"   and print "checked"}> Days</label>
      <label><input type="radio" name="groupBy" value="month" {$groupBy == "month" and print "checked"}> Months</label>
    </td>
    <td class="right" valign="bottom" rowspan="2" colspan="3">
      <button type="submit" name="applyFilter" value="1" class="filter_button">Filter<i class="icon-cogs"></i></button>
      {page::help("timeSheetGraph_filter")}
    </td>
  </tr>
</table>

<input type="hidden" name="sessID" value="{$sessID}">
</form>
