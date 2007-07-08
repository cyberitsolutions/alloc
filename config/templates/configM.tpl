{show_header()}
{show_toolbar()}

<form action="{$url_alloc_config}" method="post">
{$table_box}
  <tr>
    <th colspan="3">Basic Setup</th>
  </tr>
  <tr>
    <td width="20%"><nobr>allocPSA Base URL</nobr></td>
    <td><input type="text" size="70" value="{$allocURL}" name="allocURL"></td> 
    <td width="1%">{get_help("config_allocURL")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Calendar 1st Day</nobr></td>
    <td><select name="calendarFirstDay">{$calendarFirstDayOptions}</select></td>
    <td width="1%">{get_help("config_calendarFirstDay")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
</form>


<form action="{$url_alloc_config}" method="post">
{$table_box}
  <tr>
    <th colspan="3">Email Setup</th>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Adminstrator Email Address</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailAdmin}" name="allocEmailAdmin"></td> 
    <td width="1%">{get_help("config_allocEmailAdmin")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Default Email From Address</nobr></td>
    <td><input type="text" size="70" value="{$AllocFromEmailAddress}" name="AllocFromEmailAddress"></td> 
    <td width="1%">{get_help("config_AllocFromEmailAddress")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Hostname/IP</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailHost}" name="allocEmailHost"></td> 
    <td width="1%">{get_help("config_allocEmailHost")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Port</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailPort}" name="allocEmailPort"></td> 
    <td width="1%">{get_help("config_allocEmailPort")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Username</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailUsername}" name="allocEmailUsername"></td> 
    <td width="1%">{get_help("config_allocEmailUsername")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Server Password</nobr></td>
    <td><input type="password" size="70" value="{$allocEmailPassword}" name="allocEmailPassword"></td> 
    <td width="1%">{get_help("config_allocEmailPassword")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Protocol</nobr></td>
    <td><select name="allocEmailProtocol">{echo get_select_options(array("imap"=>"IMAP","pop3"=>"POP3"),$TPL["allocEmailProtocol"])}</select></td> 
    <td width="1%">{get_help("config_allocEmailProtocol")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Box Name</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailFolder}" name="allocEmailFolder"></td> 
    <td width="1%">{get_help("config_allocEmailFolder")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Mail Key Method</nobr></td>
    <td><select name="allocEmailKeyMethod">{echo get_select_options(array("headers"=>"Email Headers","subject"=>"Email Subject"),$TPL["allocEmailKeyMethod"])}</select></td>
    <td width="1%">{get_help("config_allocEmailKeyMethod")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
</form>


<form action="{$url_alloc_config}" method="post">
{$table_box}
  <tr>
    <th colspan="3">Time Sheets Setup</th>
  </tr>
  <tr>
    <td width="20%"><nobr>Finance Tagged Fund</nobr></td>
    <td><select name="cybersourceTfID">{$tfOptions}</select></td>
    <td width="1%">{get_help("config_cybersourceTfID")}</td>
  </tr>
  <tr>
    <td>Time Sheet Administrator</td>
    <td><select name="timeSheetAdminEmail">{$timeSheetAdminEmailOptions}</select></td> 
    <td width="1%">{get_help("config_timeSheetAdminEmail")}</td>
  </tr>
  <tr>
    <td>Hours in a Working Day</td>
    <td><input type="text" size="70" value="{$hoursInDay}" name="hoursInDay"></td> 
    <td width="1%">{get_help("config_hoursInDay")}</td>
  </tr>
  <tr>
    <td>Time Sheet Printout Footer</td>
    <td><input type="text" size="70" value="{$timeSheetPrintFooter}" name="timeSheetPrintFooter"></td> 
    <td width="1%">{get_help("config_timeSheetPrintFooter")}</td>
  </tr>
  <tr>
    <td>Services Tax Name</td>
    <td><input type="text" size="70" value="{$taxName}" name="taxName"></td> 
    <td width="1%">{get_help("config_taxName")}</td>
  </tr>
  <tr>
    <td>Services Tax Percent</td>
    <td><input type="text" size="70" value="{$taxPercent}" name="taxPercent"></td> 
    <td width="1%">{get_help("config_taxPercent")}</td>
  </tr>
  <tr>
    <td>Payroll Tax Percent</td>
    <td><input type="text" size="70" value="{$payrollTaxPercent}" name="payrollTaxPercent"></td> 
    <td width="1%">{get_help("config_payrollTaxPercent")}</td>
  </tr>
  <tr>
    <td>Company Percent</td>
    <td><input type="text" size="70" value="{$companyPercent}" name="companyPercent"></td> 
    <td width="1%">{get_help("config_companyPercent")}</td>
  </tr>
  <tr>
    <td>Time Sheet Payment Insurance Percent</td>
    <td><input type="text" size="70" value="{$paymentInsurancePercent}" name="paymentInsurancePercent"></td> 
    <td width="1%">{get_help("config_paymentInsurancePercent")}</td>
  </tr>
  <tr>
    <td valign="top">Time Sheet Print Options</td>
    <td><select size="9" name="timeSheetPrint[]" multiple><option value="">{$timeSheetPrintOptions}</select><a href="{$url_alloc_configEdit}configName=timeSheetPrintOptions">Advanced Options Edit</a></td>
    <td width="1%" valign="top">{get_help("config_timeSheetPrint")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
</form>

<form action="{$url_alloc_config}" method="post">
{$table_box}
  <tr>
    <th colspan="2">Company Information</th>
    <th width="1%">{get_help("config_companyInfo")}</th>
  </tr>
  <tr>
    <td width="20%">Company Name</td>
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
    <td>Time Sheet PDF (line 2)</td>
    <td><input type="text" size="70" value="{$companyACN}" name="companyACN"></td> 
  </tr>
  <tr>
    <td>Time Sheet PDF (line 3)</td>
    <td><input type="text" size="70" value="{$companyABN}" name="companyABN"></td> 
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>

</table>
</form>

  
{show_footer()}
