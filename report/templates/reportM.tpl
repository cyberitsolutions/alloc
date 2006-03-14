{:show_header}
  {:show_toolbar}

{table_box}
  <tr>
    <th>Reports</th>
  </tr>
  <tr>
    <td>

{optional:step_1}
<form action="{url_alloc_report}" method="post">
<select name="mod" size="1">
{module_options}
</select>
<input type="submit" value="Show Fields" name="do_step_2">
</form>
{/optional}

{optional:step_2}
<form action="{url_alloc_report}" method="post">
<br>
	<table width="100%">
	    {table_fields}
	</table>
<input type="hidden" value="{do_step_2}" name="do_step_2">
<input type="hidden" value="{mod}" name="mod">
<br>
{dump_options}
<br>
<br>
<input type="submit" value="Dump Fields" name="do_step_3">
</form>
{/optional}


{optional:step_3}
<b>{filelink}</b>
<table border="1" cellpadding="4" cellspacing="0">
{result_row}
</table>
{/optional}

    </td>
  </tr>
</table>

{:show_footer}

