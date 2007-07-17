<tr>
<td>{$filename}</td>
<td>{$mtime}</td>
<td>{$size}</td>
<td>
<form enctype="multipart/form-data" action="" method="post">
<input type="submit" value="Restore" name="restore_backup" onClick="return confirm('Are you sure you want to restore this backup?')"/>
<input type="submit" value="Delete"  name="delete_backup" onClick="return confirm('Are you sure you want to delete this backup?')"/>
<input type="hidden" name="file" value="{$restore_name}" />
</form>
</td>
</tr>

