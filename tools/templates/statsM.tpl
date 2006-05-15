{:show_header}
{:show_toolbar}

{table_box}
  <tr>
    <th>Alloc Statistics</th>
  </tr>
  <tr>
    <td>
      <table border="1" cellspacing="0" cellpadding="2">
        <tr>
          <td rowspan="2">username</td>
          <td colspan="2">projects</td>
          <td colspan="2">tasks</td>
          <td rowspan="2">comments</td>
          <td>usage graph for the last month</td>
        </tr>
        <tr>
          <td align="center">current</td>
          <td align="center">total</td>

          <td align="center">current</td>
          <td align="center">total</td>

          <td align="center"><font color="#0000FF">projects</font>,
            <font color="#007700">tasks</font>,
            <font color="#FF0000">comments</font></td>
        </tr>
        <tr>
          <td align="left"><a href="{global_graph_big}">total</a></td>
          <td align="center">{global_projects_current}</td>
          <td align="center">{global_projects_total}</td>
          <td align="center">{global_tasks_current}</td>
          <td align="center">{global_tasks_total}</td>
          <td align="center">{global_comments_total}</td>
          <td align="center" valign="bottom"><a href="{global_graph_big}"><img src="{global_graph}" border=0></a></td>
        </tr>
        {:show_users_stats templates/statsR.tpl}
      </table>

    </td>
  </tr>
</table>
{:show_footer}
