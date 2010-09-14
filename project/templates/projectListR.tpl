<tr>
{if $_FORM["showProjectName"]}      <td>{=$projectName}</td>{/}
{if $_FORM["showProjectLink"]}      <td>{$projectLink}</td>{/}
{if $_FORM["showProjectShortName"]} <td>{=$projectShortName}</td>{/}
{if $_FORM["showClient"]}           <td>{=$clientName}</td>{/}
{if $_FORM["showProjectType"]}      <td>{=$projectType}</td>{/}
{if $_FORM["showProjectStatus"]}    <td>{=ucwords($projectStatus)}</td>{/}
{if $_FORM["showNavLinks"]}         <td class="nobr noprint" align="right">{$navLinks}</td>{/}
</tr>

