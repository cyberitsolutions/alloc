{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th>Alloc Statistics</th>
  </tr>
  <tr>
    <td>
      <table border="1" cellspacing="0" cellpadding="2">
        <tr>
          <td rowspan="2">User</td>
          <td colspan="2">Projects</td>
          <td colspan="2">Tasks</td>
          <td rowspan="2">Comments</td>
          <td>Usage Graph For The Last Month</td>
        </tr>
        <tr>
          <td align="center">Current</td>
          <td align="center">Total</td>

          <td align="center">Current</td>
          <td align="center">Total</td>

          <td align="center"><font color="#0000FF">Projects</font>,
            <font color="#007700">Tasks</font>,
            <font color="#FF0000">Comments</font></td>
        </tr>
        <tr>
          <td align="left">total</td>
          <td align="center">{$global_projects_current}</td>
          <td align="center">{$global_projects_total}</td>
          <td align="center">{$global_tasks_current}</td>
          <td align="center">{$global_tasks_total}</td>
          <td align="center">{$global_comments_total}</td>
          <td align="center" valign="bottom">{$global_graph}</td>
        </tr>
        {show_users_stats("templates/statsR.tpl")}
      </table>

    </td>
  </tr>
</table>
{page::footer()}
