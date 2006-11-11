<html>
  <head>
    <link rel="stylesheet" href="{$url_alloc_stylesheets}install.css" type="text/css" />
  </head>
  <body>

  <div style="text-align:center">

  <div id="header_image">
    <img style="float:right; right:0px; top:7px; position:absolute;" src="{$url_alloc_images}/alloc_med.png" alt="allocPSA logo">
    <br/>
    <h1>allocPSA Installation Helper</h1>
  </div>

  <div id="main">        

  <!-- Tabs -->
  <div class="tab_line_bg">
    <div class="tab{$tab1}" style="left:-1px;">
      <a href="{$url_alloc_installation}?tab=1{$get}">Input</a>
    </div>
    <div class="tab{$tab2}" style="left:80px;">
      <a href="{$url_alloc_installation}?tab=2{$get}">DB Setup</a>
    </div>
    <div class="tab{$tab3}" style="left:161px;">
      <a href="{$url_alloc_installation}?tab=3{$get}">DB Install</a>
    </div>
    <div class="tab{$tab4}" style="left:242px;">
      <a href="{$url_alloc_installation}?tab=4{$get}">Launch</a>
    </div>
 
    <div style="display:inline; position:absolute; right:-5px;">
      <img src="../images/tab_line_bg_white_corners.gif" width="11px" height="27px" alt="-">
    </div>
  </div>


<form action="{$url_alloc_installation}" method="post">
  <div style="padding:10px;">

{if check_optional_step_1()}
<br/>

Fill in the fields below and click the Save Settings button. If you have an
existing database and database user, then enter those credentials, otherwise
this installer will guide you through the creation of a database and database
user.

<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Settings</th><th>Values</th>
</tr>
{$text_tab_1}
</table>

<input type='hidden' name='tab' value='2'>
<div class="buttons">
  <input type='submit' name='submit_stage_1' value='Save Settings'>
</div>
{/}


{if check_optional_step_2()}
<br/>

If you need to create the allocPSA database and database user, run the
following commands on your MySQL server, ensure you are logged in as a
MySQL administrator user when you run them.

<br/><br/>

Note, you do not need to run these commands if the database and user
permissions are already setup like eg: in a hosted environment.

<br/>
<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Database Administrator Commands</th>
</tr>
<tr>
  <td><pre>{$text_tab_2a}</pre></td>
</tr>
</table>

<br/>
Once that is done, you should test that everything worked ok by clicking the Test Database Connection button.
<div class="buttons">
  <input type='submit' name='test_db_credentials' value='Test Database Connection'>
  <input type='submit' name='submit_stage_2' value='Next &gt;'>
</div>

    {if check_optional_step_2b()}
    <table class="nice" cellspacing="0" border="0">
    <tr>
      <th>Database Connection Status</th>
    </tr>
    <tr>
      <td>{$text_tab_2b}&nbsp;{$img_tab_2b}</td>
    </tr>
    </table>
    {$msg_test_db_result}
    {$img_test_db_result}
    {/}

{$hidden}
{/}


{if check_optional_step_3()}
<br/>
Click the Install Database button to install the tables into the allocPSA database.
<div class="buttons">
  <input type='submit' name='install_db' value='Install Database'>&nbsp;&nbsp;
  <!-- <input type='submit' name='patch_db' value='Patch Existing Database'> -->
  <input type='submit' name='submit_stage_3' value='Next &gt;'>
</div>

{$text_tab_3}
{$hidden}
{/}


{if check_optional_step_4()}
<br/>
Verify that all the tests succeeded below, and click the Complete Installation button.
<br/>
<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Test</th>
  <th>Value</th>
  <th>Status</th>
  <th>&nbsp;</th>
</tr>
<tr>
  <td width="10%"><nobr>DB Connect</nobr></td>
  <td width="20%">User:{$ALLOC_DB_USER}<br/>Password:{$ALLOC_DB_PASS}<br/>Host:{$ALLOC_DB_HOST}</td>
  <td>{$remedy_DB_CONNECTIVITY}&nbsp;</td>
  <td width="1%" align="center">{$img_result_DB_CONNECTIVITY}&nbsp;</td>
</tr>
<tr>
  <td>DB Install</td>
  <td>{$ALLOC_DB_NAME}&nbsp;</td>
  <td>{$remedy_DB_SELECT}&nbsp;</td>
  <td align="center">{$img_result_DB_SELECT}&nbsp;</td>
</tr>
<tr>
  <td>DB Tables</td>
  <td>{$num_tables}&nbsp;</td>
  <td>{$remedy_DB_TABLES}&nbsp;</td>
  <td align="center">{$img_result_DB_TABLES}&nbsp;</td>
</tr>
<tr>
  <td>Upload Dir</td>
  <td>{$ATTACHMENTS_DIR}&nbsp;</td>
  <td>{$remedy_ATTACHMENTS_DIR}&nbsp;</td>
  <td align="center">{$img_result_ATTACHMENTS_DIR}&nbsp;</td>
</tr>
<tr>
  <td>Config File</td>
  <td>alloc_config.php &nbsp;</td>
  <td>{$remedy_ALLOC_CONFIG}&nbsp;</td>
  <td align="center">{$img_result_ALLOC_CONFIG}&nbsp;</td>
</tr>


</table>
<div class="buttons">
  <input type='submit' name='submit_stage_3' value='Refresh Page'>
  <input type='submit' name='submit_stage_4' value='Complete Installation'>
</div>

    {if check_optional_step_4b()}
    <table class="nice" cellspacing="0" border="0">
      <tr>
        <th>Installation Results</th>
      </tr>
      <tr>
        <td>
          {$text_tab_4}
        </td>
      </tr>
    </table>
    <br/>
    {$msg_install_result}
    {$img_install_result}
    {/}

{$hidden}
{/}



  </div>
</form>


      </div>
    </div>

    <div style="text-align:center; font-size:70%; color:#666666;">
      allocPSA {$ALLOC_VERSION} &copy; 2006 <a style="color:#666666" href="http://www.cybersource.com.au">Cybersource</a>
    </div>

  </body>
</html>
