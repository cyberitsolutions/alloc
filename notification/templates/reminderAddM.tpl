{:show_header}
{:show_toolbar}

<form action="{url_alloc_reminderAdd}" method="post">
{table_box}
    <tr>
      <th colspan="4">{reminder_title}</th>
    </tr>
    <tr>
      <td>Date:</td>
      <td>
        <select name="reminder_month">{reminder_months}</select>
        <select name="reminder_day">{reminder_days}</select>
        <select name="reminder_year">{reminder_years}</select>
      </td>
      <td>Time:</td>
      <td>
        <select name="reminder_hour">{reminder_hours}</select>
        <select name="reminder_minute">{reminder_minutes}</select>
        <select name="reminder_meridian">{reminder_meridians}</select>
      </td>
    </tr>
    <tr>
      <td>Recuring:</td>
      <td>
        <input type="checkbox" name="reminder_recuring" {reminder_recuring}>Yes, every
        <input type="text" size="4" name="reminder_recuring_value" value="{reminder_recuring_value}">
        <select name="reminder_recuring_interval">{reminder_recuring_intervals}</select>
      </td>
      <td>Advanced<br>notice:</td>
      <td>
        <input type="checkbox" name="reminder_advnotice" {reminder_advnotice}>Yes
        <input type="text" size="4" name="reminder_advnotice_value" value="{reminder_advnotice_value}">
        <select name="reminder_advnotice_interval">{reminder_advnotice_intervals}</select>
        in advance
      </td>
    </tr>
    <tr>
      <td>Recipient:</td>
      <td colspan="3">
        <select name="reminder_recipient">
          {reminder_recipients}
        </select>
      </td>
    </tr>
    <tr>
      <td>Subject:</td>
      <td colspan="3">
        <input name="reminder_subject" type="text" size="60" value="{reminder_default_subject}">
      </td>
    </tr>
    <tr>
      <td valign="top">Content:</td>
      <td colspan="3">
        <textarea name="reminder_content" cols="60" rows="6" wrap="virtual">{reminder_default_content}</textarea>
      </td>
    </tr>
    <tr>
      <td colspan="4" align="center">{reminder_buttons}&nbsp;&nbsp;&nbsp;{reminder_goto_parent}</td>
    </tr>
  </table>

  <input type="hidden" name="parentType" value="{parentType}">
  <input type="hidden" name="parentID" value="{parentID}">
  <input type="hidden" name="returnToParent" value="{returnToParent}">
  <input type="hidden" name="step" value="4">

  </form>

{:show_footer}
