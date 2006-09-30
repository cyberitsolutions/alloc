{table_box}
  <tr>
    <th>{ALLOC_TITLE}&nbsp;{ALLOC_VERSION}</th>
    <th class="right">{toolbar_item11}&nbsp;</th>
  </tr>
  <tr>
    <td class="menu_pane_left">

	    <table class="menu" cellpadding="3" cellspacing="0" border="0">
  	    <tr>
          <td>{toolbar_item0}</td>
          <td>{toolbar_item2}</td>
          <td>{toolbar_item1}</td>
          <td>{toolbar_item3}</td>
  	    </tr>
  	    <tr>
          <td>{toolbar_item4}</td>
          <td>{toolbar_item5}</td>
          <td>{toolbar_item6}</td>
          <td>{toolbar_item7}</td>
  	    </tr>
  	    <tr>
          <td>{toolbar_item8}</td>
          <td>{toolbar_item10}</td>
          <td>{toolbar_item9}</td>
          <td>{:get_help_link}</td>
  	    </tr>
	    </table>

    </td>
    <td class="menu_pane_right">

	    <table cellpadding="2" cellspacing="0" border="0">
	      <tr>
	        <td>
	          <form action="{url_alloc_search}" method="post" id="form_search">
              <input size="18" name="needle" value="{needle}" onFocus="document.getElementById('form_search').needle.value='';" style="width:200px;">
              <select size="1" name="category" style="width:100px;">{category_options}</select>
              <input type="submit" name="search" value="Go">
	          </form>
          </td>  
        </tr>
  	    <tr>
   	      <td>
	          <form action="{url_alloc_history}" method="post" name="history">
              <select name="historyID" onChange="this.form.submit();" style="width:304px;"> 
              <option value="">Quick List</option>
              {default_history_item_1}
              {default_history_item_10}
              {default_history_item_11}
              {default_history_item_9} 
              {default_history_item_2}
              {default_history_item_3}
              {default_history_item_4}
              {default_history_item_5}
              {default_history_item_6}
              {default_history_item_7}
              {default_history_item_8} 
              {:show_history}</select>
              <input type="submit" value="Go">
            </form>
          </td>
  	    </tr>
	    </table>

    </td>
  </tr>
</table>
{:show_messages}
