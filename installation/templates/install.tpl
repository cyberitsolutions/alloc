<html>
  <head>
    <link rel="stylesheet" href="{$url_alloc_cache}install.css" type="text/css" />
  </head>
  <body>

  <div style="text-align:center">

  <div id="header_image">
    <img style="float:right; right:0px; top:7px; position:absolute;" src="{$url_alloc_images}/alloc_med.png" alt="allocPSA logo">
    <br>
    <h1>allocPSA Installer</h1>
  </div>

  <div id="tabs">
    <div class="tab{$tab1}" style="left:-2px;">
      <a href="{$url_alloc_installation}?tab=1{$get}">Setup</a>
    </div>
    <div class="tab{$tab2}" style="left:78px;">
      <a href="{$url_alloc_installation}?tab=2{$get}">Test</a>
    </div>
    <div class="tab{$tab3}" style="left:158px;">
      <a href="{$url_alloc_installation}?tab=3{$get}">DB</a>
    </div>
    <div class="tab{$tab4}" style="left:238px;">
      <a href="{$url_alloc_installation}?tab=4{$get}">Install</a>
    </div>
  </div>


  <div id="main">        


<form action="{$url_alloc_installation}" method="post">
  <div style="padding:10px;">

{if show_tab_1()}
<br>

This will help you install allocPSA on your webserver. Please note that
allocPSA is written in PHP, uses MySQL and runs best on a Linux server. If you
want to install allocPSA on a Windows server, see <a
href="http://www.allocpsa.org/installing_alloc_under_windows/">Installing allocPSA Under Windows</a>. 

<br><br>

1). Verify that the system is configured correctly and has all the necessary components installed.


{$tests = array("php_version"  =>"PHP &gt;= 5.2.6"           
               ,"php_memory"   =>"PHP memory_limit &gt;= 32M"
               ,"php_gd"       =>"PHP GD image library"      
               ,"php_mbstring"     =>"PHP Multibyte string" 
               ,"mysql_version"=>"MySQL &gt;= 5"          
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

2). Fill in the fields below and click the Save Settings button. <b>If you
already have an existing database and database user, then enter those
credentials</b>, otherwise this installer will guide you through the creation
of a database and database user.

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
<br>

3). <b>Run the following commands on your MySQL server</b>.
    <br><b style="color:blue">Ensure you are logged in as a
    MySQL administrator user when you import the final file, db_triggers.sql.</b>

<br>
<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Database Administrator Commands</th>
</tr>
<tr>
  <td><pre>{$text_tab_2a}</pre></td>
</tr>
</table>

<br>
4). Once that is done, you should test that everything worked ok by clicking the Test Database Connection button.
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
      <td colspan="2">{$text_tab_2b}&nbsp;</td>
    </tr>
    <tr>
      <td><b>{$msg_test_db_result}</b></td><td width="1%" align="center">{$img_test_db_result}</td>
    </tr>
    </table>
    {/}

{$hidden}
{/}


{if show_tab_3()}
<br>
5). Click the Install Database button to complete the database installation.
<div class="buttons">
  <input type='submit' name='install_db' value='Install Database'>&nbsp;&nbsp;
  <!-- <input type='submit' name='patch_db' value='Patch Existing Database'> -->
  <input type='submit' name='submit_stage_3' value='Next &gt;'>
</div>

   {if show_tab_3b()}
    <table class="nice" cellspacing="0" border="0">
    <tr>
      <th colspan="2">Database Installation</th>
    </tr>
    <tr>
      <td colspan="2">{$text_tab_3b}&nbsp;</td>
    </tr>
    <tr>
      <td><b>{$msg_install_db_result}</b></td><td width="1%" align="center">{$img_install_db_result}</td>
    </tr>
    </table>
    {/}

{$hidden}
{/}


{if show_tab_4()}
<br>
6). Verify that all the tests succeeded below, and click the Complete Installation button.
<br>

{$tests = array("db_connect"     =>"DB Connect" 
               ,"db_select"      =>"DB Install" 
               ,"db_tables"      =>"DB Tables"  
               ,"attachments_dir"=>"Upload Dir" 
               ,"valid_currency" =>"Valid Currency" 
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

    {if !show_tab_4c()}
<div class="buttons">
  <input type='submit' name='submit_stage_3' value='Refresh Page'>
  <input type='submit' name='submit_stage_4' value='Complete Installation'>
</div>
    {/}

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
    <b>One last thing...</b><br>
    7). You can enable further functionality of allocPSA by installing these
    cron jobs onto the server:

    <table class="nice" cellspacing="0" border="0">
      <tr>
        <th>Cron Jobs</th>
      </tr>
      <tr>
        <td>
{$rand = sprintf("%02d",rand(0,59))}
{$rand2 = sprintf("%d",rand(1,5))}
          <pre>
# Check every day in the early hours for the exchange rates
{$rand} {$rand2} * * * wget -q -O /dev/null {$allocURL}finance/updateExchangeRates.php

# Check every 10 minutes for any allocPSA Reminders to send
*/10 * * * * wget -q -O /dev/null {$allocURL}reminder/sendReminders.php

# Check every 5 minutes to update the search index
*/5 * * * * wget -q -O /dev/null {$allocURL}search/updateIndex.php

# Check every 5 minutes for any new emails to import into allocPSA
*/5 * * * * wget -q -O /dev/null {$allocURL}email/receiveEmail.php

# Send allocPSA Daily Digest emails once a day at 4:35am
35 4 * * * wget -q -O /dev/null {$allocURL}person/sendEmail.php

# Check for allocPSA Repeating Expenses once a day at 4:40am
40 4 * * * wget -q -O /dev/null {$allocURL}finance/checkRepeat.php</pre>
        </td>
      </tr>
    </table>
    These cronjobs will enable the automatic Reminders, the Email Gateway,
    the Daily Task Digests and the Repeating Expenses functionality to work.
    They rely on you already having wget installed.
    {/}

{$hidden}
{/}



  </div>
<input type="hidden" name="sessID" value="{$sessID}">
</form>


      </div>
    </div>

    <div style="text-align:center; font-size:70%; color:#666666;">
      allocPSA {get_alloc_version()} &copy; {echo date("Y")} <a style="color:#666666" href="http://www.cyber.com.au">Cyber IT Solutions</a>
    </div>

  </body>
</html>
