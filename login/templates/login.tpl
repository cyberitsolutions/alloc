<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="language" content="English-AU">

    <title>allocPSA Login</title>
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
      table.outer             { border:3px solid #ffffff; margin-top:50px; margin-left:auto; margin-right:auto;}
      p.error                 { color: red; display:inline; }
      form                    { display:inline;}
      -->
    </style>
  
    <script type="text/javascript">
    <!--
      function focus_field() {
        if (document.getElementById("login_form").username.value != '') {
            document.getElementById("login_form").password.focus();
        } else {
            document.getElementById("login_form").username.focus();
        }
      }
    // -->
    </script>


  </head>
  <body class="login" onLoad="javascript:focus_field();">


  <form action="{url_alloc_login}" method="post" id="login_form">

  {ALLOC_SHOOER}


  <table width="30%" class="outer">
    <tr>
      <td>

        <table class="login" cellspacing="0" cellpadding="0" width="100%">
          <tr>
            <td valign="top">
  
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td><img src="{script_path}images/icon_alloc.png" alt="AllocPSA Icon"/></td> 
                  <td class="link" valign="top">
                    {links}&nbsp;
                  </td>
                </tr>
                <tr>
                  <td class="header" colspan="2"></td>
                </tr>
              </table>
              <br>      
              <br>      
              <table cellspacing="0" cellpadding="6">
                <tr>   
                  <td colspan="2" class="message" valign="top">
                    {error}<br>&nbsp;
                  </td>
                </tr>
                <tr>
                  <td class="right" style="width:100%">Username</td>
                  <td class="right">
                    <input type="text" name="username" value="{username}" size="25" maxlength="32">
                  </td>
                </tr>
                <tr>
                  {password_or_email_address_field}
                </tr>
                <tr><td colspan="2">&nbsp;</td></tr>
                <tr>
                  <td class="left">{use_cookies}</td>
                  <td class="right">{login_or_send_pass_button}</td>
                </tr>
                <tr>
                  <td class="center" colspan="2">{status_line}</td>
                  <td><input type="hidden" name="account" value="{account}"></td>
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
</html>
