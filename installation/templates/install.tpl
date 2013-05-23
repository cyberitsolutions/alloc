<style>
  .results b {
    color:#e50d0d !important;
  }
</style>
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
      <a href="{$url_alloc_installation}?tab=1{$get}">System</a>
    </div>
    <div class="tab{$tab2}" style="left:78px;">
      <a href="{$url_alloc_installation}?tab=2{$get}">Settings</a>
    </div>
    <div class="tab{$tab3}" style="left:158px;">
      <a href="{$url_alloc_installation}?tab=3{$get}">Database</a>
    </div>
    <div class="tab{$tab4}" style="left:238px;">
      <a href="{$url_alloc_installation}?tab=4{$get}">Config file</a>
    </div>
  </div>


  <div id="main">        


<form action="{$url_alloc_installation}" method="post">
  <div style="padding:10px;">

{if show_tab_1()}
<br>

1). Verify that the system is configured correctly and has all the necessary components installed.

<br><br>

allocPSA is written in PHP, uses MySQL and runs best on a Linux server. If you
want to install allocPSA on a Windows server, see
<a href="http://www.allocpsa.org/installing_alloc_under_windows/">Installing allocPSA Under Windows</a> or 
<a href="https://sourceforge.net/apps/mediawiki/allocpsa/index.php?title=Installing_allocPSA_on_Windows">Installing allocPSA Under Windows</a>. 


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
  <input type='hidden' name='tab' value='2'>
  <input type='submit' name='submit_stage_1' value='Next &gt;'>
</div>
{/}

{if show_tab_2()}
<br>
2). Setup the database and database user.
<br><br>
If you want to use an existing (empty) database and
existing database user, then enter those credentials.

<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Settings</th><th>Values</th>
</tr>
{$text_tab_1}
</table>

<div class="buttons">
  <input type='hidden' name='tab' value='2'>
  <input type='submit' name='submit_stage_2' value='Begin installation'>
</div>
{/}

{if $text_tab_2a}
<table class="nice" cellspacing="0" border="0">
<tr>
  <th colspan="2">Settings Status</th>
</tr>
<tr>
  <td colspan="2" class="results">{$text_tab_2a}&nbsp;</td>
</tr>
<tr>
  <td><b>{$msg_install_result}</b></td><td width="1%" align="center">{$img_install_result}</td>
</tr>
</table>
{/}

{if show_tab_3()}
<br>

3). Run the following commands on your MySQL server.

<br><br>

Ensure you are logged in as a <b>MySQL administrator user</b>.

<br>
<table class="nice" cellspacing="0" border="0">
<tr>
  <th>Database Administrator Commands</th>
</tr>
<tr>
  <td><pre>{$text_tab_3}</pre></td>
</tr>
</table>

<div class="buttons">
  <input type='submit' name='test_db' value='Test everything is ok'>
  <input type='hidden' name='tab' value='4'>
  <input type='submit' name='submit_stage_3' value='Next &gt;'>
</div>

{if show_tab_3b()}
<table class="nice" cellspacing="0" border="0">
<tr>
  <th colspan="2">Database Status</th>
</tr>
<tr>
  <td colspan="2" class="results">{$text_tab_3b}&nbsp;</td>
</tr>
<tr>
  <td><b>{$msg_test_db_result}</b></td><td width="1%" align="center">{$img_test_db_result}</td>
</tr>
</table>
{/}

{$hidden}
{/}


{if show_tab_4()}
<br>
4). The final step is to manually go and create an <b>alloc_config.php</b> file as shown below: 
<br><br>

This file contains the database password, so be mindful of the permissions on this file.

<table class="nice" cellspacing="0" border="0">
<tr>
  <th>{echo ALLOC_CONFIG_PATH}</th>
</tr>
<tr>
  <td><pre>&lt;?php
{foreach $config_vars as $name => $arr}
  {if $name != "allocURL" && $name != "currency"}
define("{$name}","{echo $_FORM[$name]}");
{/}
{/}
</pre></td>
</tr>
</table>


And then <a href="{$url_alloc_login}?message_help=Default+login+username/password:+alloc<br>You+should+change+both+the+username+and+password+of+this+administrator+account+ASAP">click here</a> and login with the username and password of 'alloc'.

{/}

</form>


      </div>
    </div>

    <div style="text-align:center; font-size:70%; color:#666666;">
      allocPSA {get_alloc_version()} &copy; {echo date("Y")} <a style="color:#666666" href="http://www.cyber.com.au">Cyber IT Solutions</a>
    </div>

  </body>
</html>
