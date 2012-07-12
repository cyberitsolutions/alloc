{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Reminders
      <span>
        {if $current_user->have_role("admin") || $current_user->have_role("manage")}
        <a class='magic toggleFilter' href=''>Show Filter</a>
        {/}
        <a href="{$url_alloc_reminder}">New Reminder</a>
      </span>
    </th>
  </tr>
  <tr>
    <td align="center">{show_reminder_filter("../reminder/templates/reminderFilter.tpl")}</td>
  </tr>  
  <tr>
    <td>
      {reminder::get_list_html()}
    </td>
  </tr>
</table>
{page::footer()}
