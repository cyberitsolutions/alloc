{page::header()}
{page::toolbar()}

{$table_box}
  <tr>
    <th>Calendar: {$username}</th>
  </tr>
  <tr>
    <td>
      {show_task_calendar_recursive()}
    </td>
  </tr>
</table>



{page::footer()}
