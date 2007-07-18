{show_header()}
{show_toolbar()}

{$table_box}
<tr><th colspan="4">Backups</th></tr>



{$rows = show_backups();}
{if $rows}
<tr><td>Filename</td><td>Created on</td><td>Size</td><td></td></tr>
{/}

{foreach $rows as $row}
<tr>
<td>{$row.filename}</td>
<td>{$row.mtime}</td>
<td>{$row.size}</td>
<td>
<form enctype="multipart/form-data" action="" method="post">
<input type="submit" value="Restore" name="restore_backup" onClick="return confirm('Are you sure you want to restore this backup?')"/>
<input type="submit" value="Delete"  name="delete_backup" onClick="return confirm('Are you sure you want to delete this backup?')"/>
<input type="hidden" name="file" value="{$row.restore_name}" />
</form>
</td>
</tr>
{/}


<tr>

<td colspan="4" class="center">
<form enctype="multipart/form-data" action="" method="post">
<input type="file" name="attachment" />
<input type="submit" value="Upload Backup" name="save_attachment" />
</form>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

<form enctype="multipart/form-data" action="" method="post">
<input type="submit" value="Create Backup" name="create_backup" />
</form>
</td>

</tr>

</table>
{show_footer()}
