{page::header()}
  {page::toolbar()}
<table class="box">
  <tr>
    <th>Expenses Upload</th>
  </tr>
  <tr>
    <td>
      <form action="{$url_alloc_expenseUpload}" method="post" enctype="multipart/form-data">
      Expenses File: <input type="file" name="expenses_file">
      <input type="submit" name="upload" value="Upload File">
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
    </td>
  </tr>
  <tr>

  <tr><td>{$msg}</td></td>
</table>
{page::footer()}
