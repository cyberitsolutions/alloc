<table class="toolbar_logo" width="100%" cellpadding="3" border="0" cellspacing="0" align="center">
<tr>
<td bgcolor="black"><img src="../images/alloc_logo.png"></td>	        
<td align="right" valign="center">
  <form action="{url_alloc_search}" method="post">
    &nbsp;<nobr><input size="23" name="needle" value="{needle}"><select size="1" name="category">{:get_category_options}</select>
    <input type="submit" name="search" value="Search"></nobr>
  </form>
</td>  
</tr>
</table>
<br>
<table class="toolbar" width="100%" cellpadding="0" border="0" cellspacing="0" align="center">
  <tr>
    <td>

	    <table width="100%" align="center" cellpadding="3" cellspacing="0" border="0">
  	    <tr>
          <td width="16%"><nobr>{toolbar_item0}</nobr></td>
          <td width="16%"><nobr>{toolbar_item2}</nobr></td>
          <td width="16%"><nobr>{toolbar_item1}</nobr></td>
          <td width="16%"><nobr>{toolbar_item5}</nobr></td>
          <td width="16%"><nobr>{toolbar_item4}</nobr></td>
          <td width="16%"><nobr>{toolbar_item3}</nobr></td>
  	    </tr>
  	    <tr>
          <td width="16%"><nobr>{toolbar_item6}</nobr></td>
          <td width="16%"><nobr>{toolbar_item7}</nobr></td>
          <td width="16%"><nobr>{toolbar_item8}</nobr></td>
          <td width="16%"><nobr>{toolbar_item10}</nobr></td>
          <td width="16%"><nobr>{toolbar_item9}</nobr></td>
          <td width="16%"><nobr>{:get_help_link} {toolbar_item11}</nobr></td>
  	    </tr>
	    </table>

    </td>
    <td>

	    <table cellpadding="2" align="right" cellspacing="0" border="0">
	      <tr>
       </tr>
  	    <tr>
   	      <td>
	          <form action="{url_alloc_history}" method="post" name="history">
	          &nbsp;<nobr><select name="historyID" onChange="this.form.submit();">
            <option value="">Quick Action</option>
            {default_history_item_1}
            {default_history_item_9} 
            {default_history_item_2}
            {default_history_item_3}
            {default_history_item_4}
            {default_history_item_5}
            {default_history_item_6}
            {default_history_item_7}
            {default_history_item_8} 
            {:show_history}</select>
	          <input type="submit" value="Go"></nobr>
            <!-- <input type="image" src="../images/go.png" alt="Go!" border="0" /> -->
            </form>
          </td>
  	    </tr>
	    </table>

    </td>
  </tr>
</table>
<br>
