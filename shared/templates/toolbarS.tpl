<table class="toolbar" width="100%" cellpadding="0" border="0" cellspacing="0" align="center">
  <tr>
    <th><nobr>{ALLOC_TITLE}&nbsp;{ALLOC_VERSION}</nobr></th>
    <td class="logout">{toolbar_item11}&nbsp;</td>
  </tr>
  <tr>
    <td>

	    <table width="100%" align="center" cellpadding="3" cellspacing="0" border="0">
  	    <tr>
          <td width="23%"><nobr>{toolbar_item0}</nobr></td>
          <td width="23%"><nobr>{toolbar_item2}</nobr></td>
          <td width="23%"><nobr>{toolbar_item1}</nobr></td>
          <td width="23%"><nobr>{toolbar_item3}</nobr></td>
  	    </tr>
  	    <tr>
          <td width="23%"><nobr>{toolbar_item4}</nobr></td>
          <td width="23%"><nobr>{toolbar_item5}</nobr></td>
          <td width="23%"><nobr>{toolbar_item6}</nobr></td>
          <td width="23%"><nobr>{toolbar_item7}</nobr></td>
  	    </tr>
  	    <tr>
          <td width="23%"><nobr>{toolbar_item8}</nobr></td>
          <td width="23%"><nobr>{toolbar_item10}</nobr></td>
          <td width="23%"><nobr>{toolbar_item9}</nobr></td>
          <td width="23%"><nobr>{:get_help_link }</nobr></td>
  	    </tr>
	    </table>

    </td>
    <td>

	    <table cellpadding="2" align="right" cellspacing="0" border="0">
	      <tr>
	        <td align="right">
	          <form action="{url_alloc_search}" method="post" id="form_search">
	          &nbsp;<nobr><input size="18" name="needle" value="{needle}" onFocus="document.forms['form_search'].needle.value='';">
            <select size="1" name="category">{category_options}</select>
	            <input type="submit" name="search" value="Go">&nbsp;</nobr>
	          </form>
          </td>  
        </tr>
  	    <tr>
   	      <td align="right">
	          <form action="{url_alloc_history}" method="post" name="history">
	          &nbsp;<nobr><select name="historyID" onChange="this.form.submit();">
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
	          <input type="submit" value="Go">&nbsp;</nobr>
            </form>
          </td>
  	    </tr>
	    </table>

    </td>
  </tr>
</table>
{:show_messages}
