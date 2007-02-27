<html>
  <head>
    <title>{$main_alloc_title}</title>
    <style>
      h1,h2,h3,h4,h5,h6 \{ 
        display:inline; 
        font-weight:normal;
      \}
      div.container \{ 
        clear:both; padding-top:20px;
      \}
      .nobr \{ 
        white-space:nowrap 
      \}
    </style>
  </head>
  <body>

    
    <div class="container">
      <div style="float:right">
        ID: <b>{$timeSheetID}</b>
      </div>
    </div>


    <div class="container" style="text-align:center;">
      <h2>{$companyName}</h2><br/><h4>{$companyNos1}<br/>{$companyNos2}</h4>  
    </div>

    <div class="container">
      <div style="float:left">
          <i>{echo config::get_config_item("companyName")}</i><br/>
          <i>{echo config::get_config_item("companyContactAddress")}</i><br/>
          <i>{echo config::get_config_item("companyContactAddress2")}</i><br/>
          <i>{echo config::get_config_item("companyContactAddress3")}</i>
      </div>
      <div style="float:right">
          <i>Email: {echo config::get_config_item("companyContactEmail")}</i><br/>
          <i>Web: {echo config::get_config_item("companyContactHomePage")}</i><br/>
          <i>{$phone}</i><br/>
          <i>{$fax}</i><br/>
      </div>
    </div>

    
    <div class="container">
      <div style="float:left">
        {$period}
      </div>
    </div>


    <div class="container">
      Client: {$clientName}<br/>
      Project: {$timeSheet_projectName}<br/>
      Contractor: {$timeSheet_personName}
    </div>

    <br/>

    {if $_GET["timeSheetPrintMode"] == "money"}
    <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th>Description</th>
        <th>Charges</th>
      </tr>
      {list($rows,$info) = get_timeSheetItem_list_money($TPL["timeSheetID"])}
      {foreach $rows as $r}
      <tr>
        <td>{$r.desc}</td>
        <td align="right" class="nobr">{$r.money}</td>
      </tr>
      {/}
      <tr>
        <th align="left">TOTAL</th>
        <th align="right">{$info.total}</th>
      </tr>
    </table>
    {else if $_GET["timeSheetPrintMode"] == "units"}
    <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th>Description</th>
        <th>Units</th>
      </tr>
      {list($rows,$info) = get_timeSheetItem_list_units($TPL["timeSheetID"])}
      {foreach $rows as $r}
      <tr>
        <td>{$r.desc}</td>
        <td align="right" class="nobr">{$r.units}</td>
      </tr>
      {/}
      <tr>
        <th align="left">TOTAL</th>
        <th align="right">{$info.total}</th>
      </tr>
    </table>
    {else if $_GET["timeSheetPrintMode"] == "items"}
     <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th>Date</th>
        <th>Units</th>
        <th>Description</th>
      </tr>
      {list($rows,$info) = get_timeSheetItem_list_items($TPL["timeSheetID"])}
      {foreach $rows as $r}
      <tr>
        <td class="nobr">{$r.date}</td>
        <td align="right" class="nobr">{$r.units}</td>
        <td>{$r.desc}</td>
      </tr>
      {/}
      <tr>
        <th align="left">TOTAL</th>
        <th align="right">{$info.total}</th>
        <th>&nbsp;</th>
      </tr>
    </table>
    {/}
 
    <br/><br/>

    {echo stripslashes(config::get_config_item("timeSheetPrintFooter"))}

    <div class="container">
      <div style="float:right">
        ID: <b>{$timeSheetID}</b>
      </div>
    </div>

  </body>
</html>
