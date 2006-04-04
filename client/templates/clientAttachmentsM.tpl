<form enctype="multipart/form-data" action="{url_alloc_client}" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
<input type="hidden" name="clientID" value="{clientID}">

{table_box}
  <tr>
    <th colspan="2">Files</th>
  </tr>
  <tr>
    <td width="3%">Size</td>
    <td>File</td>
  </tr>
  {:list_attachments templates/clientAttachmentsR.tpl}
  <tr>
    <td align="right" valign="middle" colspan="2">
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
