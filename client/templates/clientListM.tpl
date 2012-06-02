{page::header()}
{page::toolbar()}
<table class="box">
  <tr>
    <th class="header">Clients 
      <b> - {print count($clientListRows)} records</b>
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
      {client::get_list_html($clientListRows,$_FORM)}
    </td>
  </tr>
</table>
{page::footer()}
