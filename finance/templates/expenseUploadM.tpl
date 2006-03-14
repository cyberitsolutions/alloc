{:show_header}
  {:show_toolbar}
{table_box}
  <tr>
    <th>Expenses Upload</th>
  </tr>
  <tr>
    <td>
      <form action="{url_alloc_expenseUpload}" method="post" enctype="multipart/form-data">
      Expenses File: <input type="file" name="expenses_file">
      <input type="submit" name="upload" value="Upload File">
      </form>
    </td>
  </tr>
  <tr>

  <tr><td>{msg}</td></td>
</table>
{:show_footer}
