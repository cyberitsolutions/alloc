
<table class="box">
  <tr>
    <th colspan="4">Attachments</th>
  </tr>
  <tr>
    <td>File</td>
    <td class="nobr">Date Modified</td>
    <td>Size</td>
    <td align="right"></td>
  </tr>
{$attachments}
  <tr>
    <td colspan="1" class="left" style="padding:5px;">
      {$bottom_button}
    </td>
    <td colspan="3" class="right nobr" style="padding:5px;">
      <form enctype="multipart/form-data" action="{$entity_url}" method="post">
      <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
      <input type="hidden" name="{$entity_key_name}" value="{$entity_key_value}">
      <input type="file" name="attachment" />
      <input type="submit" value="Upload Attachment" name="save_attachment">
      </form>
    </td>
  </tr>
</table>

