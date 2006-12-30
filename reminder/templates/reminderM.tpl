      <table width="100%">
        <tr>
          <td><h2>Reminders</h2></td>
        </tr>
      </table>
      <table border="1" cellcpacing="0" cellpadding="2" width="100%">
        <tr>
          <th>Recipient</th>
          <th>Date / Time</th>
          <th>Subject</th>
          <th>Repeat</th>
        </tr>
        {show_reminders("../reminder/templates/reminderR.tpl")}
      </table>
      <br>
      <table>
        <tr>
          <td colspan="4">{$reminder_title}</td>
        </tr>
        <tr>
          <th>Date:</th>
          <td>
            <select name="reminder_month">{$reminder_months}</select>
            <select name="reminder_day">{$reminder_days}</select>
            <select name="reminder_year">{$reminder_years}</select>
          </td>
          <th>Time:</th>
          <td>
            <select name="reminder_hour">{$reminder_hours}</select>
            <select name="reminder_minute">{$reminder_minutes}</select>
            <select name="reminder_meridian">{$reminder_meridians}</select>
          </td>
        </tr>
        <tr>
          <th>Recuring:</th>
          <td>
            <input type="checkbox" name="reminder_recuring" {$reminder_recuring}>Yes, every
            <input type="text" size="4" name="reminder_recuring_value" value="{$reminder_recuring_value}">
            <select name="reminder_recuring_interval">{$reminder_recuring_intervals}</select>
          </td>
          <th>Advanced<br>notice:</th>
          <td>
            <input type="checkbox" name="reminder_advnotice" {$reminder_advnotice}>Yes
            <input type="text" size="4" name="reminder_advnotice_value" value="{$reminder_advnotice_value}">
            <select name="reminder_advnotice_interval">{$reminder_advnotice_intervals}</select>
            in advance
          </td>
        </tr>
        <tr>
          <th>Recipient:</th>
          <td colspan="3">
            <select name="reminder_recipient">
              {$reminder_recipients}
            </select>
          </td>
        </tr>
        <tr>
          <th>Subject:</th>
          <td colspan="3">
            <input name="reminder_subject" type="text" size="60" value="{$reminder_default_subject}">
          </td>
        </tr>
        <tr>
          <th valign="top">Content:</th>
          <td colspan="3">
            <textarea name="reminder_content" cols="60" rows="6" wrap="virtual">{$reminder_default_content}</textarea>
          </td>
        </tr>
        <tr>
          <td colspan="4" align="center">{$reminder_buttons}</td>
        </tr>
      </table>
