<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="language" content="English-AU">
    <title>allocPSA Login</title>
    <link rel="stylesheet" href="{url_alloc_stylesheets}login.css" type="text/css" />
    <script language="javascript" type="text/javascript" src="{url_alloc_javascript}login.js"></script>
  </head>

  <body onLoad="javascript:focus_field();">

  <form action="{url_alloc_login}" method="post" id="login_form">

  <div style="text-align:center">

    {ALLOC_SHOOER}

    <div class="cssbox">
      <div class="cssbox_head">
        <h2 class="link"><img src="{script_path}images/alloc_med.png" alt="allocPSA"/>{links}</h2>
      </div>
      <div class="cssbox_body">

        <table cellpadding="0" cellspacing="0" class="login">
          <tr>   
            <td colspan="2" class="message">
              {error}
            </td>
          </tr>
          <tr>
            <td class="right" style="width:100%">Username&nbsp;&nbsp;</td>
            <td class="right"><input type="text" name="username" value="{username}" size="20" maxlength="32"></td>
          </tr>
          <tr>
            {password_or_email_address_field}
          </tr>
          <tr>
            <td></td>
            <td style="text-align:right; padding-top:30px;">{login_or_send_pass_button}</td>
          </tr>
          <tr><td colspan="2" style="border-bottom:1px solid #e0e0e0;">&nbsp;</td></tr>
          <tr>
            <td class="center" colspan="2">{status_line}</td>
            <td><input type="hidden" name="account" value="{account}"></td>
          </tr>
        </table>

      </div>
    </div> 

  </div>

  </form>

  </body>
</html>
