{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th colspan="3">Reminders</th>
    <th class="right"><a href="{$url_alloc_reminderAdd}">Add Reminder</a></th>
  </tr>
  <tr>
    <td align="center" colspan="4">{show_reminder_filter("../reminder/templates/reminderFilter.tpl")}</td>
  </tr>  
  <tr>
    <td colspan="4">
      {$table_list}
        <tr>
          <th>Recipient</th>
          <th>Date / Time</th>
          <th>Subject</th>
          <th>Repeat</th>
        </tr>
        {show_reminders("../reminder/templates/reminderR.tpl")}
      </table>
    </td>
  </tr>
</table>
{show_footer()}
