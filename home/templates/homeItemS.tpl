<table class="box">
  <tr>
    <th class="header" colspan="3">
      {echo $item->get_title()}
      <span style="position:relative;width:15%;" class="hidden-links">
        {if $item->has_config}
        <a href="#x" class="config-link icon-wrench" id="config_{echo $item->name}"></a>
        {/}
      </span>
    </th>
  </tr>
  <tr>
    <td>{$item->show()}</td>
  </tr>
</table>
