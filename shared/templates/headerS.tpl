<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta http-equiv="Expires" content="Tue, 27 Jul 1997 05:00:00 GMT"> 
    <meta http-equiv="Pragma" content="no-cache">
    <title>{=$main_alloc_title}</title>
    <style type="text/css" media="screen">body { font-size:{page::default_font_size()}px }</style>
    <link rel="StyleSheet" href="{$url_alloc_stylesheets}{page::stylesheet()}" type="text/css" media="screen">
    <link rel="StyleSheet" href="{$url_alloc_stylesheets}calendar.css" type="text/css" media="screen">
    <link rel="StyleSheet" href="{$url_alloc_stylesheets}print.css" type="text/css" media="print">
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.textarearesizer.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.livequery.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}calendar.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}calendar-en.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}calendar-setup.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}sorttable.js"></script>
    <script language="javascript" type="text/javascript" src="{$url_alloc_javascript}alloc.js"></script>
    <script language="javascript" type="text/javascript">
      // return a value that is populated from PHP
      function get_alloc_var(key) {
      var values = {
                    "side_by_side_link" : "{$_REQUEST.sbs_link}"
                   ,"show_filters"      : "{print is_object($current_user) ? $current_user->prefs["showFilters"] : ""}"
                   }
      return values[key];
    }
    </script>
  </head>
  <body id="{$body_id}" class="{$body_class}">
