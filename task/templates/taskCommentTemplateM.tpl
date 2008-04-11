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
    <td valign="top">{get_textarea("taskCommentTemplateText",$TPL["taskCommentTemplateText"],array("height"=>"jumbo"))}</td>
    <td valign="top">
      Placeholder variables may be inserted into the template text.<br>
      The following is a list of the available variables:<br><br>
      %cu = Current User<br/>
      <br/>
      %ti = Task ID<br/>
      %to = Task Creator<br/>
      %tm = Task Manager<br/>
      %ta = Task Assignee<br/>
      %tc = Task Closer<br/>
      %tn = Task Name<br/>
      %td = Task Description<br/>
      %tu = Task URL<br/>
      %pn = Task Project Name<br/>
      <br/>
      %cd = Company Contact Details (c1,c2,c3,cp,cf,ce,cw)<br/>
      %cn = Company Name<br/>
      %c1 = Company Address (line 1)<br/>
      %c2 = Company Address (line 2)<br/>
      %c3 = Company Address (line 3)<br/>
      %ce = Company Email<br/>
      %cp = Company Phone No<br/>
      %cf = Company Fax No<br/>
      %cw = Company Home Page<br/>
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
										    
