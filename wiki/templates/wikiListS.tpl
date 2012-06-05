{if $wikiListRows}
<table class="list sortable">
  <tr>
    <th>File</th>
    <th width="1%" style="font-size:120%"><i class="icon-star"></i></th>
  </tr>
  {foreach $wikiListRows as $r}
  <tr>
    <td><a href="{$url_alloc_wiki}target={echo urlencode($r["filename"])}">{$r.filename}</a></td>
    <td width="1%">
      {page::star("wiki",base64_encode($r["filename"]))}
    </td>
  </tr>
  {/}
</table>
{/}
