{show_header()}
{show_toolbar()}

{$table_box}
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
          <td>Username</td>
          <td><input type="text" name="username" value="{$person_username}"></td>
		      <td>Enabled</td>
		      <td><input type="checkbox" name="personActive" value="1"{$personActive}></td>
        </tr>
        <tr>
          <td>Password</td>
          <td><input type="password" name="password1" value=""></td>
          <td>Confirm Password</td>
          <td><input type="password" name="password2" value=""></td>
        </tr>
        <tr>
          <td>First Name</td>
          <td><input type="text" name="firstName" value="{$person_firstName}"></td>
          <td>Surname</td>
          <td><input type="text" name="surname" value="{$person_surname}"></td>
        </tr>
        <tr>
          <td>Preferred Payment TF</td>
          <td><select name="preferred_tfID"><option value="">&nbsp;</option>{$preferred_tfID_options}</select></td>
        </tr>       
        <tr>
          <td>Email Address</td>
          <td><input type="text" name="emailAddress" value="{$person_emailAddress}"></td>
        </tr>
        <tr>
		      <td>Daily Email</td>
		      <td><select name="dailyTaskEmail">{$dailyTaskEmailOptions}</select></td>
        </tr>
        <tr>
          <td>Phone No</td>
          <td><input type="text" name="phoneNo1" value="{$person_phoneNo1}"></td>
          <td>Mobile No</td>
          <td><input type="text" name="phoneNo2" value="{$person_phoneNo2}"></td>
        </tr>
        <tr>
          <td>Special Permissions
		          <br>Super User: Access everything
		          <br>Finance Admin: Access all transactions, TF's
		          <br>Project Manager: Access all projects
		      </td>
          <td>{show_perm_select()}</td>
          <td>Last Login</td>
          <td>{$person_lastLoginDate}</td>
        </tr>
        {include_employee_fields()}
        {include_management_fields()}
        <tr>
          <td align="center" colspan="2">
            {show_action_buttons()}
          </td>
        </tr>
      </table>
      </form>
    </td>
  </tr>
</table>

{include_employee_skill_fields()}
  
{$table_box}
  <tr>
    <th colspan="2">Absence Forms</th>
    <th class="right"><a href="{$absence_url}">New absence form</a></th>
  </tr>
  <tr>
    <td>Start Date</td>
    <td>End Date</td>
  </tr>
  {show_absence_forms("templates/personAbsenceR.tpl")}
</table>
{show_footer()}
