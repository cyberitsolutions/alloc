{:show_header}
  {:show_toolbar}
  {table_box}
    <tr>
      <th>Announcements</th>
      <th class="right" colspan="4"><a href="{url_alloc_announcement}">New Announcement</a></th>
    </tr>
    <tr>
      <td>Heading</td>
      <td>Posted By</td>
      <td>Display From</td>
      <td>Display To</td>
      <td>Action</td>
    </tr>
    {:show_announcements templates/announcementListR.tpl}
  </table>
{:show_footer}
