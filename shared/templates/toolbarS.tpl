    <div id="header">
      <a href="http://www.allocpsa.org"><img src="{$url_alloc_images}alloc_tiny.png" alt="allocPSA" border="0"></a>
      <p>{echo config::get_config_item("companyName")}</p>
    </div>

    <div id="menu_top_right">
      <form action="{$url_alloc_menuSubmit}" method="post" id="form_search">
      <table cellpadding="0" cellspacing="0" align="center">
        <tr>
          <td><select name="historyID" class="menu_form menu_form_dropdown" onChange="this.form.submit();">{show_history()}</select></td>
          <td>&nbsp;&nbsp;or&nbsp;&nbsp;</td>
          <td><input size="14" type="text" name="needle" value="{$needle}" class="menu_form_text" onFocus="document.getElementById('form_search').needle.value='';">&nbsp;</td>
          <td><select size="1" name="category" class="menu_form menu_form_dropdown">{$category_options}</select>&nbsp;</td>
          <td><input type="submit" name="search" class="menu_form_button" value="Go">&nbsp;&nbsp;</td>
          <td>{help_button("quicklist_and_search")}</td>
        </tr>
      </table></form></div>

    <div id="tabs">
      {show_tabs()}
      <p id="extra_links">{get_config_link()}&nbsp;&nbsp;{get_help_link()}&nbsp;&nbsp;<a href="{$url_alloc_logout}">Logout</a>&nbsp;</p>
    </div>

    <div id="main">


{show_messages()}
