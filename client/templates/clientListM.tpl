{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Clients
      <span>
        <a class='magic toggleFilter' href=''>Show Filter</a>
        <a href="{$url_alloc_client}">New Client</a>
      </span>  
    </th>
  </tr>
  <tr>
    <td align="center">{show_filter()}</td>
  </tr>
  <tr>
    <td>
      {if $clientListRows}
      <table class="list sortable">
        <tr>
          <th>Client</th>
          <th>Contact Name</th>
          <th>Contact Phone</th>
          <th>Contact Email</th>
          <th>Status</th>
          <th>Category</th>
        </tr>
        {foreach $clientListRows as $r}
        <tr>
          <td>{$r.clientLink}</td>
          <td>{=$r.clientContactName}</td>
          <td>{=$r.clientContactPhone}</td>
          <td>{$r.clientContactEmail}</td>
          <td>{$r.clientStatus}</td>
          <td>{$r.clientCategoryLabel}</td>
        </tr>
        {/}
      </table>
      {else}
        <b>No Clients Found.</b>
      {/}
    </td>
  </tr>
</table>
{page::footer()}
