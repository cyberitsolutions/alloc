{:show_header}
{:show_toolbar}
{table_box}
  <tr>
    <th>Invoices Upload</th>
  </tr>
  <tr>
    <td>
      <form action="{url_alloc_invoicesUpload}" method="post" enctype="multipart/form-data">
      Invoices File: <input type="file" name="invoices_file">
      <input type="submit" name="upload" value="Upload File">
      </form>
    </tr>
    <tr>
      <td>{msg}</td>
    </tr>
</table>
{:show_footer}
