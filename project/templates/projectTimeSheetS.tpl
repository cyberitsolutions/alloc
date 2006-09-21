{table_box}
  <tr>
    <th colspan="5">Time Sheets</th>
    <th class="right">
      <a href="{url_alloc_timeSheet}newTimeSheet_projectID={project_projectID}">Time Sheet</a>
    </th>
  </tr>
  <tr>
    <td>User Name</td>
    <td>Start Date</td>
    <td>End Date</td>
    <td>Status</td>
    <td>Amount</td>
  </tr>
  {:show_timeSheet templates/project_timeSheetItemR.tpl}
</table>




