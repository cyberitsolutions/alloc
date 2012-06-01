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
            <button type="submit" name="restore_backup" value="1" class="confirm_button">Restore<i class="icon-refresh"></i></button>
            <button type="submit" name="delete_backup" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
            <input type="hidden" value="{$row.restore_name}" name="file" />
            <input type="hidden" name="sessID" value="{$sessID}">
            </form>
          </td>
        </tr>
        {/}

        <tr>
          <td colspan="1" class="left" style="padding:5px;">
            <form action="{$url_alloc_backup}" method="post">
            <button type="submit" name="create_backup" value="1" class="filter_button">Create New Backup<i class="icon-cogs"></i></button>
            <input type="hidden" name="sessID" value="{$sessID}">
            </form>
          </td>
          <td colspan="3" class="right nobr" style="padding:5px;">
            <form enctype="multipart/form-data" action="{$url_alloc_backup}" method="post">
            <input type="file" name="attachment" />
            <button type="submit" name="save_attachment" value="1" class="save_button">Upload Backup Zip File<i class="icon-upload"></i></button>
            <input type="hidden" name="sessID" value="{$sessID}">
            </form>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
{page::footer()}
