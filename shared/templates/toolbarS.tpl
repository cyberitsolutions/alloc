
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
              <td width="1%"><select name="historyID" class="menu_form_select" onChange="this.form.submit();">{$history_options}</select></td>
              <td width="6px">&nbsp;&nbsp;or&nbsp;&nbsp;</td>
              <td width="30px"><input size="14" type="text" name="needle" value="{$needle}" class="menu_form_text" onFocus="document.getElementById('form_search').needle.value='';"></td>
              <td width="30px"><select size="1" name="category" class="menu_form_select">{$category_options}</select></td>
              <td width="30px"><input type="submit" name="search" class="menu_form_button" value="Search"></td>
              <td width="18px">{page::help("quicklist_and_search")}</td>
            </tr>
          </table>
          </form>
        </td>
      </tr>
    </table>

    <div id="tabs">
      {page::tabs()}
      <p id="extra_links">{page::extra_links()}</p>
    </div>

    <div id="main">
      <div id="main2"><!-- another div nested for padding -->

{page::messages()}

