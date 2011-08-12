{page::header()}
{page::toolbar()}

<table class="box">
  <tr>
    <th>Person Details</th>
  </tr>
  <tr>
    <td>
      &nbsp;&nbsp;&nbsp;&nbsp;
      <form action="{$url_alloc_person}" method="post">
      <input type="hidden" name="personID" value="{$person_personID}">
      <table border="0" cellspacing=0 cellpadding=5>
        <tr>
          <td>First Name</td>
          <td><input type="text" name="firstName" value="{$person_firstName}"></td>
          <td>Surname</td>
          <td><input type="text" name="surname" value="{$person_surname}"></td>
        </tr>
        <tr>
          <td>Login Username</td>
          <td><input type="text" name="username" value="{$person_username}"></td>
          <td>User Enabled</td>
          <td><input type="checkbox" name="personActive" value="1"{$personActive}>&nbsp;&nbsp;(last login:{$person_lastLoginDate})</td>
        </tr>
        <tr>
          <td>Password</td>
          <td><input type="password" name="password1" value=""></td>
          <td>Confirm Password</td>
          <td><input type="password" name="password2" value=""></td>
        </tr>
        <tr>
          <td>Email Address</td>
          <td><input type="text" name="emailAddress" value="{$person_emailAddress}"></td>
          <td>Preferred Payment TF</td>
          <td><select name="preferred_tfID"><option value="">&nbsp;</option>{$preferred_tfID_options}</select></td>
        </tr>       
        <tr>
          <td>Phone No</td>
          <td><input type="text" name="phoneNo1" value="{$person_phoneNo1}"></td>
          <td>Mobile No</td>
          <td><input type="text" name="phoneNo2" value="{$person_phoneNo2}"></td>
        </tr>
        <tr>
          <td valign="top">Special Permissions</td>
          <td>{show_perm_select()}</td>
        </tr>
        {include_employee_fields()}
        {include_management_fields()}
        <tr>
          <td align="center" colspan="4">
            {show_action_buttons()}
          </td>
        </tr>
      </table>
      <input type="hidden" name="sessID" value="{$sessID}">
      </form>
    </td>
  </tr>
</table>

{include_employee_skill_fields()}
  
<table class="box">
  <tr>
    <th class="header">Absence Forms
      <span>
        <a href="{$absence_url}">New absence form</a>
      </span>
    </th>
  </tr>
  <tr>
    <td>
      <table class="sortable list">
        <tr>
          <th>Start Date</th>
          <th>End Date</th>
          <th>Absence Type</th>
        </tr>
        {show_absence_forms("templates/personAbsenceR.tpl")}
      </table>
    </td>
  </tr>
</table>
{page::footer()}
