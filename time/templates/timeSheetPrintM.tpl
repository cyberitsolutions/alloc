<html>
  <head>
    <title>{$main_alloc_title}</title>
  </head>
  <body>
  <div align="center">
    <img src="{echo config::get_config_item("companyImage")}">
    <h2>{$companyName}<br/>Time Sheet </h2>  
  </div>

    <table border="0" cellspacing="3" width="100%">
      <tr>
        <th width="10%" align="right">Project:</th>
        <td width="30%">{$timeSheet_projectName}</td>
        <th width="10%">Client:</th>
        <td width="20%">{$clientName}</td>
        <th width="10%">Contractor:</th>
        <td width="20%">{$timeSheet_personName}</td>
      </tr>
	  <tr><td>&nbsp;</td></tr>
    </table>
    <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th>Date</th>
        <th>Duration</th>
        <th>Description</th>
        <th>Comments</th>
      </tr>
      {show_timeSheet_list("templates/timeSheetPrintR.tpl")}
    </table>
    <br>

    <table border="0" cellspacing="3" width="100%">
      <tr>
        <td colspan="7">&nbsp;</td>
      <tr>
        <td colspan="7">Authorisation:__________________________________<br> 
      </tr>
      <tr>
        <td colspan="7">&nbsp;</td>
      </tr>
      <tr>
        <th align="right">Total: {$summary_totals}</td>
        <td width="10%"> </td>
        <th><nobr>Invoice Date: {$timeSheet_invoiceDate}</nobr></td>
        <td width="10%"> </td>
        <th><nobr>Invoice Number: {$timeSheet_invoiceNum}</nobr></td>
        <td width="10%"> </td>
        <th><nobr>Time Sheet ID: {$timeSheetID}</nobr></td>
      </tr>
    </table>
    <div align="center">
      <p><i>{$companyInfoLine1}</i></p>
      <p><i>{$companyInfoLine2}</i></p>
    </div>
      
{show_footer()}


