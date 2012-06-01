{page::header()}
{page::toolbar()}

<style>

.search_header {
  margin-top:30px;
  margin-left:auto;
  margin-right:auto;
  width:90%;
}

.search {
  margin-left:auto;
  margin-right:auto;
  padding-top:0px;
  padding-bottom:0px;
  width:90%;
  clear:both;
}

.search .title {
  font-size:140%;
}
.search .score {
  float:right;
  clear:right;
  margin-top:6px;
}
.search .related {
  float:right;
}
.search .desc {
  margin-top:6px;
  display:block;
  width:80%;
}

.toggler {
  font-weight:bold;
  text-decoration:underline;
}

</style>


<form action="{$url_alloc_search}" method="get">
<table class="box">
  <tr>
    <th>Search</th>
  </tr>
  <tr>
    <td align="center" class="noprint">
      <!-- We put display:table, because the default is for .filter's to be hidden -->
      <table class="filter corner" align="center" style="display:table">
        <tr>
          <td><input type="text" size="70" name="needle" value="{=$needle2}"></td>
          <td><select size="1" name="category">{$search_category_options}</select></td>
          <td>
          <button type="submit" name="search" value="1" class="filter_button">Search<i class="icon-info-sign"></i></button>
          </td>
	        <td><input type="checkbox" name="idRedirect" {$redir} />Disable redirection by ID</td>
	        <td><a href="{$url_alloc_helpfile}#search">Help</a></td>
        </tr>
      </table>
    </td>
  </tr>
  <tr>
    <td>
      <div class="search_header">
        <div style="float:left; font-size:140%;">{$search_title}</div>
        <div style="float:right">{if $search_title}Searching {echo sprintf("%d",$index_count)} documents, returned {echo sprintf("%d",$hits_count)} hits.{/}</div>
      </div>

      {foreach (array)$search_results as $r}
        {if is_numeric($r["idx"])}
        <div class="search">
          <hr>
          <span class="title">{$r.title}</span>
          <span class="related">{$r.related}</span>
          <span class="desc" onClick='return set_grow_shrink("longText_{$r.idx}","shortText_{$r.idx}")'>
            <div id='shortText_{$r.idx}'>{echo substr($r["desc"],0,150)}</div>
            <div style='display:none' id='longText_{$r.idx}'>{echo nl2br(trim($r["desc"]))}{echo format_display_fields(nl2br(trim($r["desc2"])))}</div>
          </span>
        </div>
        {/}
      {/}

    </td>
  </tr>
  </table>

<input type="hidden" name="sessID" value="{$sessID}">
</form>



{page::footer()}

