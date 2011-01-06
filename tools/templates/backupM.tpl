{page::header()}
{page::toolbar()}
{$rows = get_attachments("backups","0")}
<table class="box">
  <tr>
    <th colspan="4">Backups</th>
  </tr>
  <tr>
    <td>
      <table class="list">
        <tr>
          <th>Filename</th>
          <th>Created on</th>
          <th>Size</th>
          <th></th>
        </tr>
        {foreach $rows as $row}
        <tr>
          <td>{$row.file}</td>
          <td>{$row.mtime}</td>
          <td>{$row.size}</td>
          <td class="right" style="padding:5px;">
            <form action="{$url_alloc_backup}" method="post">
            <input type="submit" value="Restore" name="restore_backup" class="confirm_button"/>
            <input type="submit" value="Delete"  name="delete_backup" class="delete_button"/>
            <input type="hidden" value="{$row.restore_name}" name="file" />
            </form>
          </td>
        </tr>
        {/}

        <tr>
          <td colspan="1" class="left" style="padding:5px;">
            <form action="{$url_alloc_backup}" method="post">
            <input type="submit" value="Create New Backup" name="create_backup" />
            </form>
          </td>
          <td colspan="3" class="right nobr" style="padding:5px;">
            <form enctype="multipart/form-data" action="{$url_alloc_backup}" method="post">
            <input type="file" name="attachment" />
            <input type="submit" value="Upload Backup Zip File" name="save_attachment" />
            </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
{page::footer()}
