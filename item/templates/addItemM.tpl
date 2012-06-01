{page::header()}
  {page::toolbar()}
<table class="box">
<th>Add Single Item</th>
<tr>
<td>
  <form method="post" action="{$url_alloc_addItem}">
  <table>
  <input type="hidden" name="personID" value="{$personID}" />
    <tr>
      <td>Item Name</td>
      <td>Notes</td>
      <td>Type</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
      <td><input size="20" type="text" name="itemName" value="{$itemName}"></td>
      <td><input size="40" type="text" name="itemNotes" value="{$itemNotes}"></td>
      <td><select name="itemType" value="{$itemType}">{$itemTypes}</select></td>
      <td>
        <button type="submit" name="save" value="1" class="save_button">Save<i class="icon-ok-sign"></i></button>
      </td>
    </tr>
  </table>
  <input type="hidden" name="sessID" value="{$sessID}">
  </form>

  <br><br>
  <h2>Import Multiple Items from File</h2>
  Expecting tab delimited csv file.  
  <br> - Will skip first line. 
  <br> - One entry per line. 
  <br> - Each entry has 2 fields: title and author, with optional third: publisher.  

  {$import_results}

  <form enctype="multipart/form-data" method="post" action="{$url_alloc_addItem}">
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
      <td><select name="itemType" value="{$itemType}">{$itemTypes}</select></td>
      <td>
        <button type="submit" name="import_from_file" value="1" class="save_button">Import From File<i class="icon-upload"></i></button>
      <td>
    </tr>
  </table>
  <input type="hidden" name="sessID" value="{$sessID}">
  </form>

  <br><hr><br>

  <h2>Edit / Remove Items</h2>

  <form method="post" action="{$url_alloc_addItem}">
  <table>
    <tr>
      <td>Items</td>
      <td>&nbsp;</td>
    <tr>
      <td><select name="itemID[]" multiple size="8">{$item_list}</select></td>
      <td>
        {$edit_options}
        <button type="submit" name="edit_items" value="1">Edit<i class="icon-edit"></i></button>
        <button type="submit" name="remove_items" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
      </td>
    </tr>
  </table>
  <input type="hidden" name="sessID" value="{$sessID}">
  </form>
</td>
</tr>
</table>
{page::footer()}

