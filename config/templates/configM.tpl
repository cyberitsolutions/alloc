{:show_header}
{:show_toolbar}
<form action="{url_alloc_config}" method="post">
{table_box}
  <tr>
    <th colspan="2">Configuration</th>
  </tr>
  <tr>
    <td width="20%"><nobr>Alloc Email From Address</nobr></td>
    <td><input type="text" size="70" value="{AllocFromEmailAddress}" name="AllocFromEmailAddress"></td> 
  </tr>
  <tr>
    <td width="20%"><nobr>Main TF as used by timesheets and upload wages/invoices</nobr></td>
    <td><select name="cybersourceTfID">{tfOptions}</select></td>
  </tr>
  <tr>
    <td>Main Time Sheet Admin, emails get sent to this person</td>
    <td><select name="timeSheetAdminEmail">{timeSheetAdminEmailOptions}</select></td> 
  </tr>
  <tr>
    <td>Company Name</td>
    <td><input type="text" size="70" value="{companyName}" name="companyName"></td> 
  </tr>
  <tr>
    <td>Company Phone</td>
    <td><input type="text" size="70" value="{companyContactPhone}" name="companyContactPhone"></td> 
  </tr>
  <tr>
    <td>Company Fax</td>
    <td><input type="text" size="70" value="{companyContactFax}" name="companyContactFax"></td> 
  </tr>
  <tr>
    <td>Company Email</td>
    <td><input type="text" size="70" value="{companyContactEmail}" name="companyContactEmail"></td> 
  </tr>
  <tr>
    <td>Company Home Page</td>
    <td><input type="text" size="70" value="{companyContactHomePage}" name="companyContactHomePage"></td> 
  </tr>
  <tr>
    <td>Company Address</td>
    <td><input type="text" size="70" value="{companyContactAddress}" name="companyContactAddress"></td> 
  </tr>
  <tr>
    <td>Company ACN</td>
    <td><input type="text" size="70" value="{companyACN}" name="companyACN"></td> 
  </tr>
  <tr>  
    <td colspan="2" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>





</form>

  
{:show_footer}
