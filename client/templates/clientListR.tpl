
{$clientContactPhone or $clientContactPhone = $clientContactMobile}
{$clientContactEmail and $clientContactEmail = "<a href=\"mailto:".page::htmlentities($clientName." <".$clientContactEmail.">")."\">".page::htmlentities($clientContactEmail)."</a>"}

<tr>
{if $_FORM["showClientName"]}          <td>{$clientName}</td>{/}
{if $_FORM["showClientLink"]}          <td>{$clientLink}</td>{/}
{if $_FORM["showPrimaryContactName"]}  <td>{=$clientContactName}</td>{/}
{if $_FORM["showPrimaryContactPhone"]} <td>{=$clientContactPhone}</td>{/}
{if $_FORM["showPrimaryContactEmail"]} <td>{$clientContactEmail}</td>{/}
{if $_FORM["showClientStatus"]}        <td>{$clientStatus}</td>{/}
{if $_FORM["showClientCategory"]}      <td>{$clientCategoryLabel}</td>{/}
</tr>

