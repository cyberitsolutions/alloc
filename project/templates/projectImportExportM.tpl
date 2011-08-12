
<form enctype="multipart/form-data" action="{$entity_url}&sbs_link=importexport" method="post">
<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
<input type="hidden" name="{$entity_key_name}" value="{$entity_key_value}">
<table class="box">
  <tr>
    <th colspan="4">Import</th>
  </tr>
  <tr>
    <td colspan="4">
      Importing into a project will automatically create alloc tasks for the tasks specified in the file, and will automatically assign those tasks to the people specified in the imported file.
    </td>
  </tr>
  <tr>
    <td colspan="4" style="padding: 5px">{$import_result}</td>
  </tr>
  <tr>
    <td colspan="1" class="right" style="padding:5px;">
      Import file:
    </td>
    <td colspan="1" style="padding:5px;">
      <input type="file" name="import" />
    </td>
    <td colspan="1" class="right" style="padding:5px;">
      File Type:
    </td>
    <td colspan="1" style="padding:5px;">
      <select name="import_type">
        <option value="planner">GNOME Planner</option>
        <option value="csv">Comma Separated Values</option>
      </select>
      {page::help("projectImport_types")}
    </td>
  </tr>
  <tr>
    <td colspan="4">
      <input type="submit" value="Import File" name="do_import">
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>


<table class="box">
  <tr>
    <th colspan="4">Export</th>
  </tr>
  <tr>
    <td colspan="4">This project as a <a href="{$url_alloc_exportDoc}entity=project&amp;id={$project_projectID}&amp;format=planner">GNOME Planner</a> format XML file.</td>
  </tr>
  <tr>
    <td colspan="4">This project as a <a href="{$url_alloc_exportDoc}entity=project&amp;id={$project_projectID}&amp;format=csv">comma separated values (task names, estimated hours, engineers)</a> file.</td>
  </tr>
</table>

