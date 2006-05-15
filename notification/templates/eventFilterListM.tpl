{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th colspan="3">Reminders</th>
    <th class="right"><a href="{url_alloc_reminderAdd}">Add Reminder</a></th>
  </tr>
  <tr>
    <td align="center" colspan="4">{:show_reminder_filter ../notification/templates/reminderFilter.tpl}</td>
  </tr>  
  <tr>
    <td>Recipient</td>
    <td>Date / Time</td>
    <td>Subject</td>
    <td>Repeat</td>
  </tr>
  {:show_reminders ../notification/templates/reminderR.tpl}
</table>


{:show_footer}
