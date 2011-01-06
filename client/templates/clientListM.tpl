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
      {show_client_list()}
    </td>
  </tr>
</table>
{page::footer()}
