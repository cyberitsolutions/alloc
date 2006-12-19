    <div id="header">
      <a href="http://www.allocpsa.org"><img src="{$url_alloc_images}alloc_tiny.png" alt="allocPSA" border="0"></a>
      <p>{echo config::get_config_item("companyName")}</p>
    </div>

    <div id="menu_top_right">
      <form action="{$url_alloc_history}" method="post" name="history">
        <select name="historyID" onChange="this.form.submit();">{show_history()}</select>&nbsp;&nbsp;or&nbsp;
      </form>
      <form action="{$url_alloc_search}" method="post" id="form_search">
        <input size="14" name="needle" value="{$needle}" onFocus="document.getElementById('form_search').needle.value='';">
        <select size="1" name="category">{$category_options}</select>
        <input type="submit" name="search" value="Go">&nbsp;&nbsp;{help_button("quicklist_and_search")}
      </form>
    </div>

    <div id="main">

      <!-- Tabs -->
      <div class="tab_line_bg">
        {show_tabs()}
        <p id="extra_links">{get_config_link()}&nbsp;&nbsp;{get_help_link()}&nbsp;&nbsp;<a href="{$url_alloc_logout}">Logout</a></p>
        <div id="blocker"><img src="../images/tab_line_bg_white_corners.gif" width="11px" height="27px" alt="-"></div>
      </div>
{show_messages()}
