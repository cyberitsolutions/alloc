{show_header()}
{show_toolbar()}
{$table_box}
  <tr>
    <th>Invoices Upload</th>
  </tr>
  <tr>
    <td>
      <br>
       Expecting a tab separated file from quickbooks where the fields are in the following order:<br><br>
       &nbsp;&nbsp;&nbsp;&nbsp;Type
       &nbsp;&nbsp;&nbsp;&nbsp;Date
       &nbsp;&nbsp;&nbsp;&nbsp;Num     
       &nbsp;&nbsp;&nbsp;&nbsp;Name    
       &nbsp;&nbsp;&nbsp;&nbsp;Memo    
       &nbsp;&nbsp;&nbsp;&nbsp;Qty     
       &nbsp;&nbsp;&nbsp;&nbsp;Sales Price     
       &nbsp;&nbsp;&nbsp;&nbsp;Amount  
       <br><br>
       <ul>
       <li>Note the leading tab.</li>
       <li>The Name field should match up with the name of an existing Client in alloc.</li>
       <li>The Type field should just be the word "Invoice" or "Credit Memo".</li>
       <li>Invoices will be imported as Invoice Status "allocate".</li>
       </ul>
       <br><br>

      <form action="{$url_alloc_invoicesUpload}" method="post" enctype="multipart/form-data">
      Invoices File: <input type="file" name="invoices_file">
      <input type="submit" name="upload" value="Upload File">
      </form>
    </tr>
    <tr>
      <td>{$msg}</td>
    </tr>
</table>
{show_footer()}
