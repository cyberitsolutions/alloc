{show_header()}
{show_toolbar()}
<form action="{$url_alloc_config}" method="post">
{$table_box}
  <tr>
    <th colspan="2">Configuration</th>
  </tr>
  <tr>
    <td width="20%"><nobr>Alloc Base URL</nobr></td>
    <td><input type="text" size="70" value="{$allocURL}" name="allocURL"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Alloc Email From Address</nobr></td>
    <td><input type="text" size="70" value="{$AllocFromEmailAddress}" name="AllocFromEmailAddress"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Main TF (as used by time sheets and upload wages/invoices)</nobr></td>
    <td><select name="cybersourceTfID">{$tfOptions}</select></td>
  </tr>
  <tr>
    <td>Main Time Sheet Admin (emails get sent to this person)</td>
    <td><select name="timeSheetAdminEmail">{$timeSheetAdminEmailOptions}</select></td> 
  </tr>
  <tr>
    <td>Hours In A Day (as used by time sheets)</td>
    <td><input type="text" size="70" value="{$hoursInDay}" name="hoursInDay"></td> 
  </tr>
  <tr>
    <td>Company Name</td>
    <td><input type="text" size="70" value="{$companyName}" name="companyName"></td> 
  </tr>
  <tr>
    <td>Company Phone</td>
    <td><input type="text" size="70" value="{$companyContactPhone}" name="companyContactPhone"></td> 
  </tr>
  <tr>
    <td>Company Fax</td>
    <td><input type="text" size="70" value="{$companyContactFax}" name="companyContactFax"></td> 
  </tr>
  <tr>
    <td>Company Email</td>
    <td><input type="text" size="70" value="{$companyContactEmail}" name="companyContactEmail"></td> 
  </tr>
  <tr>
    <td>Company Home Page</td>
    <td><input type="text" size="70" value="{$companyContactHomePage}" name="companyContactHomePage"></td> 
  </tr>
  <tr>
    <td>Company Address (line 1)</td>
    <td><input type="text" size="70" value="{$companyContactAddress}" name="companyContactAddress"></td> 
  </tr>
  <tr>
    <td>Company Address (line 2)</td>
    <td><input type="text" size="70" value="{$companyContactAddress2}" name="companyContactAddress2"></td> 
  </tr>
  <tr>
    <td>Company Address (line 3)</td>
    <td><input type="text" size="70" value="{$companyContactAddress3}" name="companyContactAddress3"></td> 
  </tr>
  <tr>
    <td>Company ACN</td>
    <td><input type="text" size="70" value="{$companyACN}" name="companyACN"></td> 
  </tr>
  <tr>
    <td>Company ABN</td>
    <td><input type="text" size="70" value="{$companyABN}" name="companyABN"></td> 
  </tr>
  <tr>
    <td>Company Time Sheet Image</td>
    <td><input type="text" size="70" value="{$companyImage}" name="companyImage"></td> 
  </tr>
  <tr>
    <td>Time Sheet Printout Footer (html allowed)</td>
    <td><input type="text" size="70" value="{$timeSheetPrintFooter}" name="timeSheetPrintFooter"></td> 
  </tr>
  <tr>  
    <td colspan="2" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>





</form>

  
{show_footer()}
