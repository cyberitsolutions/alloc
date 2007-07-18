{show_header()}
{show_toolbar()}
<form action="{$url_alloc_taskCommentTemplate}" method="post">
{$table_box}
  <tr>
    <th>Task Comment Template</th>
    <th class="right" colspan="2"><a href="{$url_alloc_taskCommentTemplateList}">Return to Comment Template List</a></th>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td>Name</td>
    <td><input type="text" name="taskCommentTemplateName" size="80" value="{$taskCommentTemplateName}"></td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top">Text</td>
    <td><textarea name="taskCommentTemplateText" rows="8" cols="80">{$taskCommentTemplateText}</textarea></td>
    <td>
      %cd = Company Contact Details<br/>
      %cn = Company Name<br/>
      %ti = Task ID<br/>
      %to = Task Owner/Creator<br/>
      %ta = Task Assignee (The person the task is assigned to)<br/>
      %pn = Project Name<br/>
      %cc = Client Company Name<br/>
      %tn = Task Name<br/>
    </td>
  </tr>
  <tr>
    <td colspan="3" align="center">
    <input type="submit" value="Save" name="save">
    <input type="submit" value="Delete" name="delete" onClick="return confirm('Are you sure you want to delete this comment/template?">
    </td>
  </tr>
</table>

<input type="hidden" name="taskCommentTemplateID" value="{$taskCommentTemplateID}">
<input type="hidden" name="taskCommentTemplateModifiedTime" value="{$displayFromDate}">
</form>
{show_footer()}
										    
