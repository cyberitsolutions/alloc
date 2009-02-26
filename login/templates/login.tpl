<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="English-AU">
    <title>allocPSA Login</title>
    <link rel="stylesheet" href="{$url_alloc_stylesheets}login.css" type="text/css" />
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.curvycorners.js"></script>
  </head>
  <body>
  <script>
    // When the document has loaded...
    $(document).ready(function() {
      if (!$("#username").val()) {
        $("#username").focus();
      } else {
        $("#password").focus();
      }
      $("div.message").corner();
    });
  </script>

  <form action="{$url_alloc_login}" method="post" id="login_form">

  <div style="margin-top:40px; text-align:center;">
    {$ALLOC_SHOOER}{echo stripslashes(urldecode($_GET["msg"]))}

    <div class="cssbox">
      <div class="cssbox_head">
        <h2 class="link"><b style="position:relative; top:-27px">{$links}</b></h2>
      </div>
      <div class="cssbox_body">

        <table cellpadding="0" cellspacing="0" class="login">
          <tr>   
            <td colspan="2" class="message">
              {$error}
            </td>
          </tr>
          <tr>
            <td class="right" style="width:100%">Username&nbsp;&nbsp;</td>
            <td class="right"><input type="text" name="username" id="username" value="{$username}" size="20" maxlength="32"></td>
          </tr>
          <tr>
            {$password_or_email_address_field}
          </tr>
          <tr>
            <td></td>
            <td style="text-align:right; padding:35px 0px 35px 0px;">{$login_or_send_pass_button}</td>
          </tr>
          <tr>
            <td class="center" colspan="2">{$status_line}</td>
            <td><input type="hidden" name="account" value="{$account}"></td>
          </tr>
        </table>

      </div>
    </div> 
  </div>

  </form>

  {if $TPL["latest_changes"]}
  <div style="width:40%; margin-top:50px; margin-left:auto; margin-right:auto">
    <div class="message help">
      {$latest_changes}
    </div>
  </div>
  {/}

  </body>
</html>
