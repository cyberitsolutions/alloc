{:show_header}
  {:show_toolbar}
  {table_box}
  <th>
  <h1>Alloc Statistics</h1>
  </th><tr><td>
  <table border="1" cellspacing="0" cellpadding="2">
    <tr>
      <th rowspan="2">username</th>
      <th colspan="2">projects</th>
      <th colspan="2">tasks</th>
      <th rowspan="2">comments</th>
      <th>usage graph for the last month</th>
    </tr>
    <tr>
      <th align="center">current</th>
      <th align="center">total</th>

      <th align="center">current</th>
      <th align="center">total</th>

      <th align="center"><font color="#0000FF">projects</font>,
        <font color="#007700">tasks</font>,
        <font color="#FF0000">comments</font></th>
    </tr>
    <tr>
      <th align="left"><a href="{global_graph_big}">total</a></th>

      <th align="center">{global_projects_current}</th>
      <th align="center">{global_projects_total}</th>

      <th align="center">{global_tasks_current}</th>
      <th align="center">{global_tasks_total}</th>

      <th align="center">{global_comments_total}</th>

      <th align="center" valign="bottom"><a href="{global_graph_big}"><img src="{global_graph}" border=0></a></th>
    </tr>
    {:show_users_stats templates/statsR.tpl}
  </table>

  </td>
  </tr>
  </table>
{:show_footer}
