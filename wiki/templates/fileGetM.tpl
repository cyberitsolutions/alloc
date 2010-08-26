  {$default_one = "view"}
  {$default_two = "edit"}
  {if $loadErrorPage}
    {$default_one = "edit"}
    {$default_two = "view"}
  {/}

  {$msg}
  <div class="{$default_one}">
    <div class="wikidoc">
      <div style="float:right; display:inline; width:30px; margin-top:10px; right:-10px; position:relative;" class="noprint">
        <a target="_blank" href="{$url_alloc_wiki}media=print&target={$file}&rev={$rev}"><img alt="Print" class="noprint" border="0" src="{$url_alloc_images}printer.png"></a>
      </div>
      {$str_html}
    </div>
    <br><br>
    <div class="noprint" style="text-align:center">
      {if is_file(wiki_module::get_wiki_path().$file) && is_writable(wiki_module::get_wiki_path().$file)}
      <input type="button" value="Edit Document" onClick="$('.view').hide();$('.edit').show();">
      {/}
    </div>
  </div>

  {if $loadErrorPage || (is_file(wiki_module::get_wiki_path().$file) && is_writable(wiki_module::get_wiki_path().$file))}
  <div class="{$default_two} noprint">
    {include_template("templates/editFileS.tpl")}
  </div>
  {/}

