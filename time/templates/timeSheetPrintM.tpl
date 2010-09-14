<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{=$main_alloc_title}</title>
    <style>
      h1,h2,h3,h4,h5,h6 { 
        display:inline; 
        font-weight:normal;
      }
      div.container { 
        clear:both; padding-top:20px;
      }
      .nobr { 
        white-space:nowrap 
      }
    </style>
  </head>
  <body>

    
    <div class="container">
      <div style="float:right">
        ID: <b>{$timeSheetID}</b>
      </div>
    </div>


    <div class="container" style="text-align:center;">
      <h2>{$companyName}</h2><br><h4>{$companyNos1}<br>{$companyNos2}</h4>  
    </div>

    <div class="container">
      <div style="float:left">
          <i>{echo config::get_config_item("companyName")}</i><br>
          <i>{echo config::get_config_item("companyContactAddress")}</i><br>
          <i>{echo config::get_config_item("companyContactAddress2")}</i><br>
          <i>{echo config::get_config_item("companyContactAddress3")}</i>
      </div>
      <div style="float:right">
          <i>Email: {echo config::get_config_item("companyContactEmail")}</i><br>
          <i>Web: {echo config::get_config_item("companyContactHomePage")}</i><br>
          <i>{$phone}</i><br>
          <i>{$fax}</i><br>
      </div>
    </div>

    
    <div class="container">
      <div style="float:left">
        {$period}
      </div>
    </div>


    <div class="container">
      Client: {$clientName}<br>
      Project: {$timeSheet_projectName}<br>
      Contractor: {$timeSheet_personName}
    </div>

    <br>

    {if $timeSheetPrintMode == "money"}
    <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th>Description</th>
        <th>Charges</th>
      </tr>
      {list($rows,$info) = $this_tsp->get_timeSheetItem_list_money($timeSheetID)}
      {foreach $rows as $r}
      <tr>
        <td>{echo nl2br($r["desc"])}</td>
        <td align="right" class="nobr">{$r.money}</td>
      </tr>
      {/}
      <tr>
        <th align="left">TOTAL</th>
        <th align="right">{$info.total}</th>
      </tr>
    </table>
    {else if $timeSheetPrintMode == "units"}
    <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th>Description</th>
        <th>Units</th>
      </tr>
      {list($rows,$info) = $this_tsp->get_timeSheetItem_list_units($timeSheetID)}
      {foreach $rows as $r}
      <tr>
        <td>{echo nl2br($r["desc"])}</td>
        <td align="right" class="nobr">{$r.units}</td>
      </tr>
      {/}
      <tr>
        <th align="left">TOTAL</th>
        <th align="right">{$info.total}</th>
      </tr>
    </table>
    {else if $timeSheetPrintMode == "items"}
     <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th class="nobr" width="10%" valign="top">Date</th>
        <th class="nobr" width="11%" valign="top">Units</th>
	      <th class="nobr" width="1%" valign="top">Multiplier</th>
        <th valign="top">Description</th>
      </tr>
      {list($rows,$info) = $this_tsp->get_timeSheetItem_list_items($timeSheetID)}
      {foreach $rows as $r}
      <tr>
        <td class="nobr" valign="top">{$r.date}&nbsp;</td>
        <td align="right" class="nobr" valign="top">{$r.units}&nbsp;</td>
        <td class="nobr" valign="top">{$r.multiplier_string}&nbsp;</td>
        <td valign="top">{echo nl2br($r["desc"])}&nbsp;</td>
      </tr>
      {/}
      <tr>
        <th align="left">TOTAL</th>
        <th align="right">{$info.total}</th>
        <th>&nbsp;</th>
        <th>&nbsp;</th>
      </tr>
    </table>
    {/}
 
    <br><br>

    {echo config::get_config_item("timeSheetPrintFooter")}

    <div class="container">
      <div style="float:right">
        ID: <b>{$timeSheetID}</b>
      </div>
    </div>

  </body>
</html>
