<?php

/*
 * Copyright (C) 2006-2020 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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

class tsiHintHomeItem extends home_item
{
    public function __construct()
    {
        parent::__construct("tsiHint_edit", "Time Sheet Item Hint", "time", "tsiHintH.tpl", "narrow", 25);
    }

    public function visible()
    {
        $current_user = &singleton("current_user");
        if ($current_user->have_role("manage") && $current_user->prefs["showTimeSheetItemHintHome"]) {
            return true;
        }
    }

    public function render()
    {
        return true;
    }
}
