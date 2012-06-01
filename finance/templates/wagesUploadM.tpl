{page::header()}
  {page::toolbar()}
<table class="box">
  <tr>
    <th>Wages Upload</th>
  </tr>
  <tr>
    <td>
      <form action="{$url_alloc_wagesUpload}" method="post" enctype="multipart/form-data">
        Wages File: <input type="file" name="wages_file">
        <button type="submit" name="upload" value="1" class="save_button">Upload File<i class="icon-upload"></i></button>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
    </td>
  </tr>
  <tr>
    <td>{$msg}</td>
  </tr>
</table>
{page::footer()}
