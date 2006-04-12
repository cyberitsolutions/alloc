<html>
  <head>
    <title>AllocPSA Login</title>
    <style type="text/css">
      <!--
      body.login              { background-color:#eeeeee; font-family:arial,helvetica,sans-serif;  }
      table.toolbar_logo      { border:2px solid #444444; background-color:#000000}
      table.login             { background-color:#ffffff; border:1px solid #888888; padding:5px;}
      table.login a           { color:#183c6d;  }
      table.login td.link     { color:#888888; text-align:right; font-size:10px; font-weight:bold;}
      table.login td.message  { color:#333333; border-top:0px solid #c0c0c0; font-size:12px; font-weight:bold;}
      table.login td.right    { color:333333; text-align:right;  font-weight:bold; font-size:12px; }
      table.login td.left     { color:333333; text-align:left;   font-size:12px; }
      table.login td.center   { color:#888888; text-align:center; font-weight:bold; font-size:10px; }
      table.login td.header   { color:#38629b; text-align:left;   font-weight:bold; font-size:18px; border-bottom:2px solid #e0e0e0; padding-left:10px;}
      table.outer             { border:3px solid #ffffff; }
      -->
    </style>
  </head>
  <body class="login">

  <br><br><br>

  <form action="{url_alloc_login}" method="post">

  {ALLOC_SHOOER}

  <input type="hidden" name="account" value="{account}">

  <table width="30%" align="center" class="outer">
    <tr>
      <td>

        <table class="login" align="center" cellspacing="0" cellpadding="0" width="100%">
          <tr>
            <td valign="top">
  
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td><img src="{script_path}images/icon_alloc.png"/></td> 
                  <td class="link" valign="top">
                    <nobr>{links}&nbsp;</nobr>
                  </td>
                </tr>
                <tr>
                  <td class="header" colspan="2"><nobr></nobr></td>
                </tr>
              </table>
              <br>      
              <br>      
              <table align="center" cellspacing="0" cellpadding="6">
                <tr>   
                  <td colspan="2" class="message" valign="top">
                    <nobr>{error}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</nobr><br>&nbsp;
                  </td>
                </tr>
                <tr>
                  <td class="right" width="100%">Username</td>
                  <td class="right">
                    <input type="text" name="username" value="{username}" size="25" maxlength="32">
                  </td>
                </tr>
                <tr>
                  {password_or_email_address_field}
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>
                <tr>
                  <td class="left"><nobr>{use_cookies}</nobr></td>
                  <td class="right">{login_or_send_pass_button}</td>
                </tr>
                <tr>
                  <td class="center" colspan="2"><nobr>{status_line}</nobr></td>
                  <td></td>
                </tr>
              </table>

            </td>
          </tr>
        </table>

      </td>
    </tr>
  </table>
</form>

</body>
<script language="JavaScript">
<!--
  if (document.forms[0][0].value != '') {
      document.forms[0][1].focus();
  } else {
      document.forms[0][0].focus();
  }
// -->
</script>
</html>
