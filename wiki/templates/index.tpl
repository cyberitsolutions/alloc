{page::header()}
{page::toolbar()}
<script type="text/javascript" language="javascript">

// This function refreshes the document pane, and the documents history
function loadFileAndHistory(path) \{
  if (path) \{
    makeAjaxRequest('{$url_alloc_fileGet}','jftFile',        \{ file: path \});
    makeAjaxRequest('{$url_alloc_fileHistory}','fileHistory',\{ file: path \});
  \}
\}

$(document).ready(function() \{

// Load the dynamic file tree
var ops = \{ script:'{$url_alloc_fileTree}'
           , folderEvent:'click'
           , expandSpeed:1
           , collapseSpeed:1
           , target:'{$target}' \};

$('#jftTree').fileTree(ops, function(path, isFile) \{ 
  if (isFile) \{
    $.history.load(path); // this will eventually call loadFileAndHistory(path)
  \}
\});

// this allows the web browsers back button to function happily with the ajax
$.history.init(loadFileAndHistory);


\});
</script>

<table class="box" id="wiki">
  <tr>
    <th colspan="3" class="noprint">Wiki</th>
  </tr>
  <tr>
    <td id="jftTreeTD" class="noprint"><h6 style="margin-top:0px;">Wiki Tree</h6><div id="jftTree"></div></td>
    <td id="jftFileTD"><div id="jftFile"></div></td>
    <td id="jftInfoTD" class="noprint"><h6 style="margin-top:0px;">History</h6><div id="fileHistory"></div></td>
  </tr>
</table>


{page::footer()}
