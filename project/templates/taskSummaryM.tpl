{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th>Task Summary</th>
    <th class="right"><nobr><a href="{url_alloc_task}">New Task</a></nobr></th>
  </tr>
  <tr>
    <td colspan="2">
      <form action="{url_alloc_taskSummary}" method="post">
      <table border="0" cellspacing="0" cellpadding="3" width="100%">
        <tr>
          <td>{filter_form}</td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <table border="0" cellspacing="0" cellpadding="3" width="100%">
      {:show_task_summary}
      </table>
    </td>
  </tr>
</table>
{:show_footer}
