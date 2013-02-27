{page::header()}
{page::toolbar()}

<link rel="StyleSheet" href="{$url_alloc_stylesheets}wiki.css" type="text/css" />
<script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.filetree.js"></script>
<script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.markitupsettings.js"></script>
<script language="javascript" type="text/javascript" src="{$url_alloc_javascript}jquery.markitup.js"></script>

<script type="text/javascript" language="javascript">

function refresh_wiki(target,revision) {
  // Draw the dynamic file tree and specify a function to get called when a node is clicked
  var ops = { script:"{$url_alloc_fileTree}"
             , folderEvent:'click'
             , expandSpeed:1
             , collapseSpeed:1
             , target:target };

  $('#jftTree').fileTree(ops, function(path, isFile) { 
    if (isFile) {
      makeAjaxRequest("{$url_alloc_file}",'jftFile', { file: path, rev: revision });
    }
    $("#newFile").attr("data-p",path);
    $("#newDirectory").attr("data-p",path);
    makeAjaxRequest("{$url_alloc_fileHistory}",'fileHistory', { file: path, rev: revision });
  });
}

$(document).ready(function() {

  // If the page has loaded, then load the tree
  refresh_wiki("{$target}","{$rev}");

  // If the user clicked a new page link...
  if ("{$newFile}") {
    makeAjaxRequest("{$url_alloc_file}",'jftFile', { newFile: true, file: "{$target}" });
  }

  // If there was an error saving we need to manually load in the values that were submitted.
  if ("{$loadErrorPage}") {
    makeAjaxRequest("{$url_alloc_file}",'jftFile', { loadErrorPage: "{$loadErrorPage}"
                                                   , str: "{$str}" 
                                                   , commit_msg: "{$commit_msg}" 
                                                   , file: "{$file}" 
                                                   , msg: "{$msg}" 
                              
    });
  }
  if ("{$loadErrorPageDir}") {
    makeAjaxRequest("{$url_alloc_directory}",'jftDir', { loadErrorPageDir: "{$loadErrorPageDir}"
                                                        , dirName: "{$dirName}" 
                                                        , msg: "{$msg}" 
    });
  }

  // Menu links: New File, New Directory
  $("#newDirectory").click(function() {
    makeAjaxRequest("{$url_alloc_directory}",'jftFile', { newDirectory: true, p: $(this).attr("data-p") });
    return false;
  });

  $("#newFile").click(function() {
    makeAjaxRequest("{$url_alloc_file}",'jftFile', { newFile: true, p: $(this).attr("data-p") });
    return false;
  });

});
</script>

<table class="box">
  <tr>
    <th colspan="3" class="header noprint">Wiki
      <span>
        <a id="newFile" href="{$url_alloc_wiki}">New File</a>
        <a id="newDirectory" href="{$url_alloc_wiki}">New Folder</a>
      </span>
    </th>
  </tr>
  <tr>
    <td id="jftTreeTD" class="noprint"><h6 style="margin-top:0px;">Wiki Tree</h6><div id="jftTree"></div></td>
    <td id="jftFileTD"><div id="jftFile"></div><div id="jftDir"></div></td>
    <td id="jftInfoTD" class="noprint"><h6 style="margin-top:0px;">History</h6><div id="fileHistory"></div></td>
  </tr>
</table>


{page::footer()}
