{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Task Comment Templates</th>
    <th class="right"><a href="{$url_alloc_taskCommentTemplate}">New Comment Template</a></th>
  </tr>
  <tr>
    <td colspan="2">
      {$table_list}
        <tr>
          <th>Template</th>
        </tr>
      {show_taskCommentTemplate("templates/taskCommentTemplateListR.tpl")}
      </table>
    </td>
  </tr>
</table>
{show_footer()}
