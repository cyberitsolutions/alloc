<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="language" content="English-AU">

    <title>allocPSA Login</title>
    <style type="text/css">
      <!--
      body                    { background-color:#eeeeee; font-family:arial,helvetica,sans-serif;  }
      table.login             { position:relative; background: transparent; font-size:15px; font-weight:bold; clear:left; color:#ffffff; }
      table.login td          { padding:6px 0px; }

      table.login td.options  { font-weight:normal; font-size:12px;}
      table.login td.message  { vertical-align:top; letter-spacing: 0.00em; }
      table.login td.right    { text-align:right; letter-spacing: 0.00em;}
      table.login td.left     { text-align:left;  letter-spacing: 0.00em;}
      table.login td.center   { color:#ffffff; text-align:center; font-size:10px; }
      table.login td.center a { color:#eeeeee; font-size:10px; }

      form                    { display:inline;}
      a                       { color:#183c6d; white-space:nowrap; }
      .link                   { color:#5276a7; text-align:right; font-size:12px;  white-space:nowrap; vertical-align:top;}
      img                     { position:relative; float:left; left:-14px;}
      p.error                 { color: red; display:inline; font-weight:bold; } 
      .right input            { border:1px solid #0f4287; }

      .cssbox, .cssbox_body, .cssbox_head, .cssbox_head h2 {
          background: transparent url(../images/roundbg.png) no-repeat bottom right; 
      } 
      .cssbox { 
          width: 395px !important; 
          width: 380px; 
          padding-right: 15px; 
          margin: 20px auto; 
      } 
      .cssbox_head { 
          background-position: top right; margin-right: -15px;
          padding-right: 40px; 
      } 
      .cssbox_head h2 { 
          background-position: top left; 
          margin: 0; 
          border: 0; 
          padding: 25px 0 15px 40px; 
          height: auto !important; height: 1%; 
      } 
      .cssbox_body { 
          background-position: bottom left; 
          margin-right: 25px; 
          padding: 15px 0 15px 40px; 
      } 
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
  <body onLoad="javascript:focus_field();">


  <form action="{url_alloc_login}" method="post" id="login_form">

  {ALLOC_SHOOER}

  <div class="cssbox">
    <div class="cssbox_head">
      <h2 class="link"><img src="{script_path}images/icon_alloc.png" alt="AllocPSA Icon"/>{links}</h2>
    </div>
    <div class="cssbox_body">

      <table cellpadding="0" cellspacing="0" class="login">
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>   
          <td colspan="2" class="message">
            {error}<br>&nbsp;
          </td>
        </tr>
        <tr>
          <td class="right" style="width:100%">Username&nbsp;&nbsp;</td>
          <td class="right">
            <input type="text" name="username" value="{username}" size="20" maxlength="32">
          </td>
        </tr>
        <tr>
          {password_or_email_address_field}
        </tr>
        <tr><td colspan="2">&nbsp;</td></tr>
        <tr>
          <td class="options">{use_cookies}</td>
          <td style="text-align:right;">{login_or_send_pass_button}</td>
        </tr>
        <tr><td colspan="2" style="border-bottom:0px solid #e0e0e0;">&nbsp;</td></tr>
        <tr>
          <td class="center" colspan="2">{status_line}</td>
          <td><input type="hidden" name="account" value="{account}"></td>
        </tr>
      </table>

    </div>
  </div> 

  </form>

  </body>
</html>
