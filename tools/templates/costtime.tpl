{page::header()}
{page::toolbar()}
<form method="post" action="{$url_alloc_costtime}">
<table class="box">
  <tr>
    <th>Simple Cost & Time Estimater</th>
  </tr>
  <tr>
    <td colspan="4">
  <p>Fill in the details below to get an estimate.</p>
  <p><b>Languages</b> - used to specify the multiplier. If you select custom
  multiplier, make sure you place a number in the box that follows the pull
  down list.  If you do not want a custom multiplier then you do not need to
  put anything in this box<br>
  <b>Pages</b> - is the number of pages that need to be produced<br>
  <b>Databases</b> - is the number of databases that need to be produced<br>
  <b>Complexity</b> - is the average complexity of the overall project (ie.
  10=small, 25=medium, 50=hard <- this is an idea, you dont have to use just
  these values)</p>
  <p><em>NOTE: please stay within the boundarys of: 12 &lt;= Pages +
  Databases &lt;= 120 and 10 &lt;= Complexity &lt;= 50 as there is no code yet
  to handle these cases</em></p>
    </td>
  </tr>
  <tr>
  <tr>
    <td><b>Languages: </b>
      <select name="multiplier">
        <option value=0>-- Select One --
        <option value=3>Java Frontend Client/Server
        <option value=2>Java Servlet/JSP
        <option value=2>Java Servlet/PHP
        <option value=1.2>PHP/Oracle
        <option value=1>PHP/SQL
        <option value=1>Python Frontend Client/Server
        <option value=-1>(Custom Multiplier)
      </select>
<!--
      <select name="multiplier">{$multiplier_options}</select>
-->
      <input type="text" name="custom" size="4">
    </td>
  </tr>
  <tr>
    <td>
      <b>Pages: </b><input type="text" name="pages" size="4">
      <b>Databases: </b><input type="text" name="databases" size="4">
      <b>Complexity: </b><input type="text" name="complexity" size="4">
      <input type=submit value="Submit">
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>
      <table>
        {makeEstimate()}
      </table>
    </td>
  </tr>
</table>
<input type="hidden" name="sessID" value="{$sessID}">
</form>
{page::footer()}
