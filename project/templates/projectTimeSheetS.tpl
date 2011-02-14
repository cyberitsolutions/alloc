<table class="box">
  <tr>
    <th class="header">Time Sheets
      <b> - {print count($timeSheetListRows)} records</b>
      <span>
        <a href="{$url_alloc_timeSheet}newTimeSheet_projectID={$project_projectID}">Time Sheet</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      {timeSheet::get_list_html($timeSheetListRows,$timeSheetListExtra)}
    </td>
  </tr>
</table>




