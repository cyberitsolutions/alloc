
    <table id="menu" cellpadding="0" cellspacing="0">
      <tr>
        <td>
          <table cellpadding="0" cellspacing="0" align="left" style="left:0px;">
            <tr>
              <td style="font-size:17px;">{echo config::get_config_item("companyName")}</td>
            </tr>
          </table>
        </td>
        <td>
          <form action="{$url_alloc_menuSubmit}" method="post" id="form_search">
          <table cellpadding="0" cellspacing="1" align="right" width="40%" style="right:0px !important; right:-32px;">
            <tr>
              <td width="1%"><select name="historyID" onChange="this.form.submit();">{$history_options}</select></td>
              <td width="6px">&nbsp;&nbsp;or&nbsp;&nbsp;</td>
              <td width="30px"><input size="18" type="text" name="needle" id="menu_form_needle" value="{$needle}"></td>
              <td width="30px"><select size="1" name="category">{$category_options}</select></td>
              <td width="30px"><input type="submit" name="search" value="Search"></td>
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

