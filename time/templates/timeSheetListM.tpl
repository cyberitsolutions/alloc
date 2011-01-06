{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Time Sheets
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href="{$url_alloc_timeSheet}userID={$userID}">New Time Sheet</a>
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">
      {show_filter()}
    </td>
  </tr>
  <tr>
    <td>
      {show_timeSheet_list()}
    </td>
  </tr>
</table>
{page::footer()}
