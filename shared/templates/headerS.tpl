<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    <meta name="language" content="English-AU">

    <title>{ALLOC_TITLE}: {main_alloc_title}</title>
    <link rel="StyleSheet" href="{url_alloc_stylesheets}{:get_stylesheet_name}" type="text/css" media="screen">

    <script type="text/javascript">
    <!--
      //var http_request = false;
      var http_request = new Array();

      function makeAjaxRequest(url,actionFunction,number) {
          http_request[number] = false;

          if (window.XMLHttpRequest) { // Mozilla, Safari,...
              http_request[number] = new XMLHttpRequest();
              if (http_request[number].overrideMimeType) { http_request[number].overrideMimeType('text/xml'); }

          } else if (window.ActiveXObject) { // IE
              try { 
                http_request[number] = new ActiveXObject("Msxml2.XMLHTTP");
              } catch (e) {
                  try { 
                    http_request[number] = new ActiveXObject("Microsoft.XMLHTTP");
                  } catch (e) {
                  }
              }
          }

          if (!http_request[number]) {
              // alert('Giving up :( Cannot create an XMLHTTP instance');
              return false;
          }
          // Here's how to be a bit crafty object.onreadystatechange=Function("yourfunction("+yourargumentlist+");");
          http_request[number].onreadystatechange = Function(actionFunction+"("+number+");");
          http_request[number].open('GET', url, true);
          http_request[number].send(null);

      }
     -->
    </script>
  </head>
  <body>
  <table width="99%" cellpadding="0" cellspacing="0">
    <tr>
      <td style="vertical-align:top;">
