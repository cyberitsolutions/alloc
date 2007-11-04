<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="language" content="English-AU">
    <title>{echo config::get_config_item("companyName")." ".APPLICATION_NAME}: {$main_alloc_title}</title>
    <link rel="StyleSheet" href="{$url_alloc_stylesheets}{get_stylesheet_name()}" type="text/css" media="screen" />
    <link rel="StyleSheet" href="{$url_alloc_stylesheets}print.css" type="text/css" media="print" />
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}main.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}help.js"></script>
    <style type="text/css">@import url({$url_alloc_stylesheets}calendar.css);</style>
    <script type="text/javascript" src="{$url_alloc_javascript}calendar.js"></script>
    <script type="text/javascript" src="{$url_alloc_javascript}calendar-en.js"></script>
    <script type="text/javascript" src="{$url_alloc_javascript}calendar-setup.js"></script>
  </head>
  <body>
  <div id="helper"></div>
  <script language="javascript" type="text/javascript">
    var helper;
    var yyy = -1000;
    helper = document.getElementById('helper');
    helper.style.visibility = "visible";
    helper.style.display = "none";
  </script>
  <div id="all">
