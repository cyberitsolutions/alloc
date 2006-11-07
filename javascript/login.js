function focus_field() {
  if (document.getElementById("login_form").username.value != '') {
      document.getElementById("login_form").password.focus();
  } else {
      document.getElementById("login_form").username.focus();
  }
}

