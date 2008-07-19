{$table_box}

<tr>
{foreach $TPL["productSale_rowtitles"] as $title}
<th>{$title}</th>
{/}
</tr>

{foreach $TPL["productSale_rows"] as $row}
<tr>
{foreach $row as $field}
<td>{$field}</td>
{/}
</tr>
{/}

</table>
