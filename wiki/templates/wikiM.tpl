{page::header()}
{page::toolbar()}

<link rel="StyleSheet" href="{$url_alloc_stylesheets}wiki.css" type="text/css" />
<script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.filetree.js"></script>
<script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.markitupsettings.js"></script>
<script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.markitup.js"></script>

<script type="text/javascript" language="javascript">


$(document).ready(function() {

  // Load the dynamic file tree
  var ops = { script:'{$url_alloc_fileTree}'
             , folderEvent:'click'
             , expandSpeed:1
             , collapseSpeed:1
             , target:'{$target}' };

  $('#jftTree').fileTree(ops, function(path, isFile) { 
    if (isFile) {
      makeAjaxRequest('{$url_alloc_file}','jftFile',           { file: path, rev: '{$rev}' });
    }
    makeAjaxRequest('{$url_alloc_fileHistory}','fileHistory',{ file: path, rev: '{$rev}' });
  });


  // Menu links: New File, New Directory
  $("#newDirectory").bind("click",function() {
    makeAjaxRequest('{$url_alloc_file}','jftFile', { newDirectory: true });
    return false;
  });

  $("#newFile").click(function() {
    makeAjaxRequest('{$url_alloc_file}','jftFile', { newFile: true });
    return false;
  });

  $("#wikiform").livequery("submit",function() {
    $.post('{$url_alloc_file}',{ save       : true, 
                                 commit_msg : $("#commit_msg").val(), 
                                 wikitext   : $("#wikitext").val(), 
                                 editName   : $("#editName").val(), 
                                 file       : $("#file").val() }, function(data) {
      $("#jftFile").html(data);
      makeAjaxRequest('{$url_alloc_fileHistory}','fileHistory',{ file: $("#editName").val() });
    });
    return false;
  });

});
</script>

<table class="box">
  <tr>
    <th colspan="2" class="noprint">Wiki</th>
    <th colspan="1" class="right noprint">
      <a id="newFile" href="{$url_alloc_wiki}">New File</a>
      <a id="newDirectory" href="{$url_alloc_wiki}">New Directory</a>
    </th>
  </tr>
  <tr>
    <td id="jftTreeTD" class="noprint"><h6 style="margin-top:0px;">Wiki Tree</h6><div id="jftTree"></div></td>
    <td id="jftFileTD"><div id="jftFile"></div></td>
    <td id="jftInfoTD" class="noprint"><h6 style="margin-top:0px;">History</h6><div id="fileHistory"></div></td>
  </tr>
</table>


{page::footer()}
