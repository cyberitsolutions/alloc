{$msg}
<h6 style="margin-top:0px;">New Directory</h6>
<form action="{$url_alloc_directory}" method="post">
  <input name="dirName" type="text" id="dirName" style="width:100%;" value="{$dirName}">
  <br><br>
  <div style="text-align:center; margin-top:20px;">
    <input type="submit" id="save" name="save" value="Create New Directory">
  </div>
</form>
<script type="text/javascript" language="javascript">
  preload_field("#dirName", "Enter the directory's name eg: path/to/new/dir");
</script>
