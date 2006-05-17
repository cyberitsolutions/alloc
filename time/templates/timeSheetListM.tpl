{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th>Time Sheets</th>
    <th class="right" colspan="6">
<!-- <a href="{url_alloc_weeklyTime}">Weekly Time View</a>&nbsp;&nbsp; -->
     <a href="{url_alloc_timeSheet}userID={userID}">New Time Sheet</a></th>
  </tr>
  <tr>
    <td colspan="6" align="center">

      <form action="{url_alloc_timeSheetList}" method="post">
      <table class="filter" align="center">
        <tr>
          <td>Project</td>
          <td>User Name</td>
          <td colspan="2">Date From</td>
          <td colspan="3">Status</td>
        </tr>
        <tr>
          <td>
            <select name="projectID">
              <option value=""> -- ALL -- </option>
              {show_project_options}
            </select>
          </td>
          <td>
            <select name="personID">
              {show_userID_options}
            </select>
          </td>
          <td><input type="text" size="10" name="dateFrom" value="{dateFrom}"></td>
          <td><input type="button" value="Today" onClick="dateFrom.value='{today}'"></td>
          <td>
            <select name="status">
              <option value="">  -- ALL -- </option>
              {show_status_options}
            </select>
          </td>
          <td rowspan="1">&nbsp;&nbsp;<input type="submit" name="search" value="Filter"></td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <td>Time Sheet</td>
    <td align="right">Amount</td>
    <td>Duration</td>
    <td>User Name</td>
    <td align="right">Start Date</td>
    <td>End Date</td>
    <td>Status</td>
  </tr>
  {:show_timeSheets templates/timeSheetListR.tpl}
  <tr>
    <td>&nbsp;</td>
    <td align="right" class="grand_total">${grand_total}&nbsp;</td>
    <td colspan="4">&nbsp;</td>
  </tr>
</table>
{:show_footer}
