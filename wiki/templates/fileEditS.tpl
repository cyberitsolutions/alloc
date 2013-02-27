<h6 style="margin-top:0px;">Document</h6>
<form action="{$url_alloc_file}" method="post">
  <input name="editName" type="text" id="editName" style="width:100%;" value="{$editName}">
  <br><br>
  {page::textarea('wikitext',$str,array("height"=>"large","width"=>"100%","class"=>"processed"))}
  {if config::get_config_item("wikiVCS")}
  <input type="text" name="commit_msg" id="commit_msg" value="{$commit_msg}" style="margin-top:20px; width:100%;">
  {/}
  <div style="text-align:center; margin-top:20px;">
    <input type="hidden" id="file" name="file" value="{$file}">
    <button type="submit" name="delete" value="1" class="delete_button">Delete<i class="icon-trash"></i></button>
    <button type="submit" id="save" name="save" value="1" class="save_button">Save Document<i class="icon-ok-sign"></i></button>
  </div>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
<script type="text/javascript" language="javascript">
  preload_field("#editName", "Enter the document's filename eg: path/to/file.txt");
  mySettings.previewParserPath="{$url_alloc_filePreview}"; 
  $("#wikitext").markItUp(mySettings);
  preload_field("#commit_msg", "Enter a brief description of your changes...");
</script>
