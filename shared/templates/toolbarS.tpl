
    <table id="menu" cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <table cellpadding="0" cellspacing="0" align="left" class="menu_top mtl">
            <tr>
              <th width="55px"><a href="http://www.allocpsa.org"><img src="{$url_alloc_images}alloc_tiny.png" alt="allocPSA" border="0"></a></th>
              <th>&nbsp;{echo config::get_config_item("companyName")}</th>
            </tr>
          </table>
        </td>
        <td>
          <form action="{$url_alloc_menuSubmit}" method="post" id="form_search">
          <table cellpadding="0" cellspacing="0" align="right" class="menu_top mtr" width="40%">
            <tr>
              <td width="1%"><select name="historyID" class="menu_form_select" onChange="this.form.submit();">{show_history()}</select></td>
              <td width="6px">&nbsp;&nbsp;or&nbsp;&nbsp;</td>
              <td width="30px"><input size="14" type="text" name="needle" value="{$needle}" class="menu_form_text" onFocus="document.getElementById('form_search').needle.value='';"></td>
              <td width="30px"><select size="1" name="category" class="menu_form_select">{$category_options}</select></td>
              <td width="30px"><input type="submit" name="search" class="menu_form_button" value="Search"></td>
              <td width="18px">{get_help("quicklist_and_search")}</td>
            </tr>
          </table>
          </form>
        </td>
      </tr>
    </table>

    <div id="tabs">
      {show_tabs()}
      <p id="extra_links">{get_print_link()}&nbsp;&nbsp;{get_config_link()}&nbsp;&nbsp;{get_help_link()}&nbsp;&nbsp;<a href="{$url_alloc_logout}">Logout</a>&nbsp;</p>
    </div>

    <div id="main">

{show_messages()}

