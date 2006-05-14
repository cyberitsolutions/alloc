{:show_header}
  {:show_toolbar}
{table_box}
<th>Add Single Item</th>
<tr>
<td>
  <form method="post" action="{url_alloc_addItem}">
  <table>
  <input type="hidden" name="personID" value="{personID}" />
    <tr>
      <td>Item Name</td>
      <td>Notes</td>
      <td>Type</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><input size="20" type="text" name="itemName" value="{itemName}"></td>
      <td><input size="40" type="text" name="itemNotes" value="{itemNotes}"></td>
      <td><select name="itemType" value="{itemType}">{itemTypes}</select></td>
      <td>
        <input size="1" type="hidden" name="itemModifiedUser" value="joe">
        <input type="submit" name="save" value="save">
      </td>
    </tr>
  </table>
  </form>

  <br><br>
  <h2>Import Multiple Items from File</h2>
  Expecting tab delimited csv file.  
  <br> - Will skip first line. 
  <br> - One entry per line. 
  <br> - Each entry has 2 fields: title and author, with optional third: publisher.  

  {import_results}

  <form enctype="multipart/form-data" method="post" action="{url_alloc_addItem}">
  <table>
    <tr>
      <td>File</td>
      <td>Type</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td>
        <input type="hidden" name="MAX_FILE_SIZE" value="10000000">
        <input size="40" type="file" name="import_file">
      </td>
      <td><select name="itemType" value="{itemType}">{itemTypes}</select></td>
      <td><input type="submit" name="import_from_file" value="Import From File"><td>
    </tr>
  </table>
  </form>

  <br><hr><br>

  <h2>Edit / Remove Items</h2>

  <form method="post" action="{url_alloc_addItem}">
  <table>
    <tr>
      <td>Items</td>
      <td>&nbsp;</td>
    <tr>
      <td><select name="itemID[]" multiple size="8">{item_list}</select></td>
      <td>
        {edit_options}
        <input type="submit" name="edit_items" value="Edit">
        <input type="submit" name="remove_items" value="Remove">
      </td>
    </tr>
  </table>
  </form>
</td>
</tr>
</table>
{:show_footer}

