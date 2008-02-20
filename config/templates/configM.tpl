{show_header()}
{show_toolbar()}

{get_side_by_side_links(array("basic"=>"Basic Setup"
                             ,"time_sheets"=>"Time Sheets"
                             ,"company_info"=>"Company Info"
                             ,"email_gateway"=>"Email Gateway"
                             ,"misc"=>"Miscellaneous"
                             ),$_POST["sbs_link"])}

<div id="basic">
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
<!-- 
  <tr>
    <td width="20%"><nobr>Time Zone</nobr></td>
    <td><select name="allocTimezone">{echo get_select_options(get_timezone_array(),$TPL["allocTimezone"])}</select></td>
    <td width="1%">{get_help("config_allocTimezone")}</td>
  </tr>
  -->
  <tr>
    <td width="20%"><nobr>Calendar 1st Day</nobr></td>
    <td><select name="calendarFirstDay">{$calendarFirstDayOptions}</select></td>
    <td width="1%">{get_help("config_calendarFirstDay")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Adminstrator Email Address</nobr></td>
    <td><input type="text" size="70" value="{$allocEmailAdmin}" name="allocEmailAdmin"></td> 
    <td width="1%">{get_help("config_allocEmailAdmin")}</td>
  </tr>
  <tr>
    <td width="20%"><nobr>Email Addressing Method</nobr></td>
    <td>
      <label for="eam_to">Use "To:"</label><input id="eam_to" type="radio" name="allocEmailAddressMethod" value="to"{$TPL["allocEmailAddressMethod"] == "to" and print " checked"}>&nbsp;&nbsp;&nbsp;&nbsp;
      <label for="eam_bcc">Use "Bcc:"</label><input id="eam_bcc" type="radio" name="allocEmailAddressMethod" value="bcc"{$TPL["allocEmailAddressMethod"] == "bcc" and print " checked"}>&nbsp;&nbsp;&nbsp;&nbsp;
      <label for="eam_tobcc">Use Both with special "To:"</label><input id="eam_tobcc" type="radio" name="allocEmailAddressMethod" value="tobcc"{$TPL["allocEmailAddressMethod"] == "tobcc" and print " checked"}>
    </td> 
    <td width="1%">{get_help("config_allocEmailAddressMethod")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
</form>
</div>


<div id="email_gateway">
<form action="{$url_alloc_config}" method="post">
{$table_box}
  <tr>
    <th colspan="2">Email Gateway</th>
    <th class="right">{get_help("config_allocEmailGateway")}</th>
  </tr>
  <tr>
    <td width="20%"><nobr>From Address</nobr></td>
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
    <td colspan="3" align="center">
      <input type="submit" name="save" value="Save">
      <input type="submit" name="test_email_gateway" value="Test Connection">
    </td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="email_gateway">
</form>
</div>

<div id="time_sheets">
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
    <td>Time Sheet Manager</td>
    <td><select name="timeSheetManagerEmail"><option value="">{$timeSheetManagerEmailOptions}</select></td> 
    <td width="1%">{get_help("config_timeSheetManagerEmail")}</td>
  </tr>
  <tr>
    <td>Time Sheet Administrator</td>
    <td><select name="timeSheetAdminEmail"><option value="">{$timeSheetAdminEmailOptions}</select></td> 
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
    <td width="20%"><nobr>Tax Tagged Fund</nobr></td>
    <td><select name="taxTfID">{$taxTfOptions}</select></td>
    <td width="1%">{get_help("config_taxTfID")}</td>
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
    <td><select size="9" name="timeSheetPrint[]" multiple><option value="">{$timeSheetPrintOptions}</select><a href="{$url_alloc_configEdit}configName=timeSheetPrintOptions">Edit</a></td>
    <td width="1%" valign="top">{get_help("config_timeSheetPrint")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="time_sheets">
</form>
</div>

<div id="company_info">
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
<input type="hidden" name="sbs_link" value="company_info">
</form>
</div>

<div id="misc">
<form action="{$url_alloc_config}" method="post">
{$table_box}
  <tr>
    <th colspan="2">Miscellaneous Setup</th>
    <th width="1%">{get_help("config_misc_setup")}</th>
  </tr>
  <tr>
    <td valign="top" width="20%"><nobr>Extra Interested Parties Options</nobr></td>
    <td>
      <a href="{$url_alloc_configEdit}configName=defaultInterestedParties">Edit:</a>
      {foreach $TPL["defaultInterestedParties"] as $k => $v}
          {echo $br.$k." ".$v}
          {$br = ", "}
      {/}
    </td> 
    <td width="1%">{get_help("config_defaultInterestedParties.html")}</td>
  </tr>
  <tr>
    <td valign="top" width="20%"><nobr>Project Priorities</nobr></td>
    <td>
      <a href="{$url_alloc_configEdit}configName=projectPriorities">Edit:</a>
      {unset($br)}
      {foreach $TPL["projectPriorities"] as $k => $arr}
          {$br}<span style="color:{echo $arr["colour"]}">{echo $k." ".$arr["label"]}</span>
          {$br = ", "}
      {/}
    </td> 
    <td width="1%">{get_help("config_projectPriorities.html")}</td>
  </tr>
  <tr>
    <td valign="top" width="20%"><nobr>Task Priorities</nobr></td>
    <td>
      <a href="{$url_alloc_configEdit}configName=taskPriorities">Edit:</a>
      {unset($br)}
      {foreach $TPL["taskPriorities"] as $k => $arr}
          {$br}<span style="color:{echo $arr["colour"]}">{echo $k." ".$arr["label"]}</span>
          {$br = ", "}
      {/}
    </td> 
    <td width="1%">{get_help("config_taskPriorities.html")}</td>
  </tr>
  <tr>  
    <td colspan="3" align="center"><input type="submit" name="save" value="Save"></td>
  </tr>
</table>
<input type="hidden" name="sbs_link" value="misc">
</form>
</div>


  
{show_footer()}
