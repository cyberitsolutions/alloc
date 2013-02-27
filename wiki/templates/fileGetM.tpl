  {$default_one = "view"}
  {$default_two = "edit"}
  {if $loadErrorPage}
    {$default_one = "edit"}
    {$default_two = "view"}
  {/}

  {$msg}
  <div class="{$default_one}">
    <div class="wikidoc">
      <span style="float:right; margin-top:10px;" class="noprint">
        {page::star("wiki",base64_encode($file))}
      </span>
      <span style="float:right; margin-top:10px;" class="noprint">
        <a target="_blank" href="{$url_alloc_wiki}media=print&target={$file}&rev={$rev}"><img alt="Print" class="noprint" border="0" src="{$url_alloc_images}printer.png"></a>
      </span>
      {$str_html}
    </div>
    <br><br>
    <div class="noprint" style="text-align:center">
      {if is_file(wiki_module::get_wiki_path().$file) && is_writable(wiki_module::get_wiki_path().$file)}
      <button type="button" value="1" onClick="toggle_view_edit();">Edit Document<i class="icon-edit"></i></button>
      {/}
    </div>
  </div>

  {if $loadErrorPage || (is_file(wiki_module::get_wiki_path().$file) && is_writable(wiki_module::get_wiki_path().$file))}
  <div class="{$default_two} noprint">
    {include_template("templates/fileEditS.tpl")}
  </div>
  {/}

