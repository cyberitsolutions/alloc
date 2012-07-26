<?php

/*
 * Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
 * Pty. Ltd.
 * 
 * This file is part of the allocPSA application <info@cyber.com.au>.
 * 
 * allocPSA is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or (at
 * your option) any later version.
 * 
 * allocPSA is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
 * License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with allocPSA. If not, see <http://www.gnu.org/licenses/>.
*/


class pendingAdminExpenseForm extends home_item {

  function __construct() {
    parent::__construct("pending_admin_expense_form", "Expense Forms Pending Admin Approval", "finance", "pendingAdminExpenseFormM.tpl", "narrow", 42);
  }

  function visible() {
    $current_user = &singleton("current_user");
    if (isset($current_user) && $current_user->have_role("admin")) {
      return true;
    }
  }

  function render() {
    global $TPL;
    $ops["status"] = "pending";
    $ops["finalised"] = 1;
    $TPL["expenseFormRows"] = expenseForm::get_list($ops);
    if (count($TPL["expenseFormRows"])) {
      return true;
    }
  }

}

?>
