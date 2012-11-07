{page::header()}
{page::toolbar()}
<form action="{$url_alloc_commentTemplate}" method="post">
<table class="box">
  <tr>
    <th class="header" colspan="3">Comment Template
      <span>
        <a href="{$url_alloc_commentTemplateList}">Comment Template List</a>
      </span>
    </th>
  </tr>
  <tr>
    <td colspan="3">&nbsp;</td>
  </tr>
  <tr>
    <td>Name</td>
    <td>
      <input type="text" name="commentTemplateName" size="60" value="{$commentTemplateName}">
      <span style="float:right">
        <select name="commentTemplateType">{$commentTemplateTypeOptions}</select>
      </span>
    </td>
    <td> </td>
  </tr>
  <tr>
    <td class="top">Text</td>
    <td class="top center">{page::textarea("commentTemplateText",$commentTemplateText,array("height"=>"jumbo"))}
      <br>
      <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
      <button type="submit" name="save" value="1" class="save_button default">Save<i class="icon-ok-sign"></i></button>
    </td>
    <td class="top">
      Placeholder variables may be inserted into the template text.
      The following is a list of the available variables:<br>

      <table>
        <tr>
          <td class="top">
          <b>Task Comment Variables</b><br>
          %ti = ID<br>
          %to = Creator<br>
          %ta = Assignee<br>
          %tm = Manager<br>
          %tc = Closer<br>
          %tu = URL<br>
          %ts = Status<br>
          %pn = Project Name<br>
          %tn = Name<br>
          %td = Description<br>
          %tp = Priority<br>
          %teb = Best estimate<br>
          %tem = Most likely estimate<br>
          %tew = Worst estimate<br>
          %tep = Estimator<br>
          </td>
        </tr>
        <tr>
          <td class="top" style="width:50%">
          <b>Time Sheet Comment Variables</b><br>
          %ti = ID<br>
          %to = Creator<br>
          %ta = Creator<br>
          %tm = Manager<br>
          %tc = Admin<br>
          %tu = URL<br>
          %ts = Status<br>
          %pn = Project Name<br>
          %tf = Date From<br>
          %tt = Date To<br>
          %project_rate = Persons project rate<br>
          %total_dollars = Employee amount<br>
          %total_customerBilledDollars = Client billed<br>
          %summary_unit_totals = Duration of work<br>
          </td>
        </tr>
        <tr>
          <td class="top">
          <b>Other Variables</b><br>
          %cu = Current User<br>
          %cd = Company Contact Details (c1,c2,c3,cp,cf,ce,cw)<br>
          %cn = Company Name<br>
          %c1 = Company Address (line 1)<br>
          %c2 = Company Address (line 2)<br>
          %c3 = Company Address (line 3)<br>
          %ce = Company Email<br>
          %cp = Company Phone No<br>
          %cf = Company Fax No<br>
          %cw = Company Home Page<br>
          </td>
        </tr>
      </table>


    </td>
  </tr>
  <tr>
    <td colspan="3" align="center">
    </td>
  </tr>
</table>

<input type="hidden" name="commentTemplateID" value="{$commentTemplateID}">
<input type="hidden" name="commentTemplateModifiedTime" value="{$displayFromDate}">
<input type="hidden" name="sessID" value="{$sessID}">
</form>
{page::footer()}
										    
