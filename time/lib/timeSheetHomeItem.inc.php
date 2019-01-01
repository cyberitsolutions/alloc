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

class timeSheetHomeItem extends home_item
{

    function __construct()
    {
        parent::__construct("time_edit", "New Time Sheet Item", "time", "timeSheetH.tpl", "narrow", 24);
    }

    function visible()
    {
        $current_user = &singleton("current_user");

        if (!isset($current_user->prefs["showTimeSheetItemHome"])) {
            $current_user->prefs["showTimeSheetItemHome"] = 1;
        }

        if ($current_user->prefs["showTimeSheetItemHome"]) {
            return true;
        }
    }

    function render()
    {
        $current_user = &singleton("current_user");
        global $TPL;
        return true;
    }
}
