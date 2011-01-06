{page::header()}
  {page::toolbar()}
  <table class="box">
    <tr>
      <th class="header">Announcements
        <span>
          <a href="{$url_alloc_announcement}">New Announcement</a>
        </span>
      </th>
    </tr>
    <tr>
      <td>
        <table class="list sortable"> 
          <tr>
            <th>Heading</th>
            <th>Posted By</th>
            <th>Display From</th>
            <th>Display To</th>
            <th>Action</th>
          </tr>
          {show_announcements("templates/announcementListR.tpl")}
        </table>
      </td>
    </tr>
  </table>
{page::footer()}
