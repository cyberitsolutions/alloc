
    <table id="menu" cellpadding="0" cellspacing="0">
      <tr>
        <td style="font-size:145%; text-shadow:#fff 1px 1px 1px;">
         {echo config::get_config_logo()}
        </td>
        <td class="nobr bottom" style="width:1%;">
          <form action="{$url_alloc_menuSubmit}" method="get" id="form_search">
            <select name="search_action" id="search_action" style="width:9em;">
              {$category_options}
              <option value="" disabled="disabled">--------------------
              {$history_options}
            </select>
            <input size="40" type="text" name="needle" id="menu_form_needle" value="{$needle}">
            <input type="hidden" name="sessID" value="{$sessID}">
            <input type="submit" value="search" style="display:none"> <!-- for w3m -->
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

