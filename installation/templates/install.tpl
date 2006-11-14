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

{if show_tab_1()}
<br/>
Verify that the system is configured correctly and has all the necessary components installed.


{$tests = array("php_version"  =>"PHP &gt;= 4.3.0"           
               ,"php_memory"   =>"PHP memory_limit &gt;= 32M"
               ,"php_gd"       =>"PHP GD image library"      
               ,"mysql_version"=>"MySQL &gt;= 3.23"          
               ,"mail_exists"  =>"Mail")}

<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Test</th>
  <th>Value</th>
  <th>Status</th>
  <th width="1%" align="center">&nbsp;</th>
</tr>
{foreach $tests as $test => $name}
  {$t = perform_test($test)}
  <tr>
    <td>{$name}</td>
    <td>{$t.value}&nbsp;</td>
    <td>{$t.remedy}&nbsp;</td>
    <td align="center">{$t.status}&nbsp;</td>
  </tr>
{/}
</table>


<div class="buttons">
  <input type='submit' name='refresh_tab_1' value='Refresh Page'>
</div>

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


{if show_tab_2()}
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

    {if show_tab_2b()}
    <table class="nice" cellspacing="0" border="0">
    <tr>
      <th colspan="2">Database Connection Status</th>
    </tr>
    <tr>
      <td colspan="2">{$text_tab_2b}&nbsp;{$img_tab_2b}</td>
    </tr>
    <tr>
      <td><b>{$msg_test_db_result}</b></td><td width="1%" align="center">{$img_test_db_result}</td>
    </tr>
    </table>
    {/}

{$hidden}
{/}


{if show_tab_3()}
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


{if show_tab_4()}
<br/>
Verify that all the tests succeeded below, and click the Complete Installation button.
<br/>

{$tests = array("db_connect"     =>"DB Connect" 
               ,"db_select"      =>"DB Install" 
               ,"db_tables"      =>"DB Tables"  
               ,"attachments_dir"=>"Upload Dir" 
               ,"alloc_config"   =>"Config File")}


<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Test</th>
  <th>Value</th>
  <th>Status</th>
  <th align="center" width="1%">&nbsp;</th>
</tr>
{foreach $tests as $test => $name}
  {$t = perform_test($test)}
  <tr>
    <td>{$name}</td>
    <td>{$t.value}&nbsp;</td>
    <td>{$t.remedy}&nbsp;</td>
    <td align="center">{$t.status}&nbsp;</td>
  </tr>
{/}
</table>

<div class="buttons">
  <input type='submit' name='submit_stage_3' value='Refresh Page'>
  <input type='submit' name='submit_stage_4' value='Complete Installation'>
</div>

    {if show_tab_4b()}
    <table class="nice" cellspacing="0" border="0">
      <tr>
        <th colspan="2">Installation Results</th>
      </tr>
      <tr>
        <td colspan="2">
          {$text_tab_4}
        </td>
      </tr>
      <tr>
        <td><b>{$msg_install_result}</b></td>
        <td width="1%" align="center">{$img_install_result}</td>
      </tr>
    </table>
    {/}

    {if show_tab_4c()}
    <b>Once last thing...</b><br/>
    You can enable further functionality of allocPSA by installing these
    cronjobs onto the server:

    <table class="nice" cellspacing="0" border="0">
      <tr>
        <th>Cronjobs</th>
      </tr>
      <tr>
        <td>
          <pre>
# Check every 10 minutes for any allocPSA Reminders to send
*/10 * * * * wget -q -O /dev/null {$allocURL}notification/sendReminders.php

# Send allocPSA Daily Digest emails once a day at 4:35am
35 4 * * * wget -q -O /dev/null {$allocURL}person/sendEmail.php

# Check for allocPSA Repeating Expenses once a day at 4:45am
40 4 * * * wget -q -O /dev/null {$allocURL}finance/checkRepeat.php</pre>
        </td>
      </tr>
    </table>
    These cronjobs will enable the Reminders, the Daily Task Digest
    emails and the Repeating Expenses functionality to work. They rely on
    you already having wget installed.
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
