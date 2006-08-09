<form enctype="multipart/form-data" action="{entity_url}" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
<input type="hidden" name="{entity_key_name}" value="{entity_key_value}">

{table_box}
  <tr>
    <th colspan="2">Attachments</th>
  </tr>
  <tr>
    <td width="3%">Size</td>
    <td>File</td>
  </tr>
{attachments}
  <tr>
    <td colspan="2" align="right" valign="middle">
      <table align="right" cellpadding="0" cellspacing="0">
        <tr>
          <td><input type="file" name="attachment"></td>
          <td><input type="submit" value="Save Document" name="save_attachment"></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
</form>

