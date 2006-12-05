<html>
  <head>
    <title>{$main_alloc_title}</title>
    <style>
      h2,h4 \{ 
        display:inline; 
        text-align:center; 
        font-weight:normal;
      \}
      div.container \{ 
        clear:both; padding-top:20px;
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
      <img src="{echo config::get_config_item("companyImage")}"><br/>
      <h2>{$companyName}</h2><br/><h4>{$companyACN}{$companyABN}</h4>  
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

    <table border="1" cellspacing="0" cellpadding="2" width="100%">
      <tr>
        <th>Description</th>
        <th>{$timeSheetPrintUnitLabel}</th>
      </tr>
      {list($rows,$info) = get_timeSheetItem_info()}
      {foreach $rows as $r}
      <tr>
        <td>{$r.desc}</td>
        <td align="right">{$r.unit}</td>
      </tr>
      {/}
      <tr>
        <th align="left">TOTAL</th>
        <th align="right">{$info.total}</th>
      </tr>
    </table>

    <br/><br/>

    <table border="0" cellspacing="0" width="100%">
      <tr>
        <td width="40%" style="border-bottom:1px solid black;">Authorisation:</td>
        <td width="40%" style="border-bottom:1px solid black;">Signature: </td>
        <td style="border-bottom:1px solid black;">Date: </td>
      </tr>
    </table>
 
    <br/><br/>

    <div class="container">
      <div style="float:right">
        ID: <b>{$timeSheetID}</b>
      </div>
    </div>



  </body>
</html>
