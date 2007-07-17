{show_header()}
{show_toolbar()}

{$table_box}
<tr><th colspan="4">Backups</th></tr>
<tr>
<td colspan="2">
<form enctype="multipart/form-data" action="" method="post">
<input size="14" type="text" name="backup_name" value="{$default_filename}" />
<input type="submit" value="Create Backup" name="create_backup" />
</form>
</td>

<td colspan="2">
<form enctype="multipart/form-data" action="" method="post">
<input type="file" name="attachment" />
<input type="submit" value="Upload Backup" name="save_attachment" />
</form>
</td></tr>

<tr><td>Filename</td><td>Created on</td><td>Size</td><td></td></tr>

{show_backups()}

</table>
{show_footer()}
