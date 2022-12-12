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

class module
{
    public $module = '';
    public $db_entities = array();   // A list of db_entity class names implemented by this module
    public $home_items = array();    // A list of all the home page items implemented by this module

    public function __construct()
    {
        spl_autoload_register(array($this, 'autoloader'));
    }

    public function autoloader($class)
    {
        $s = DIRECTORY_SEPARATOR;
        $p = dirname(__FILE__).$s.'..'.$s.'..'.$s.$this->module.$s.'lib'.$s.$class.'.inc.php';
        if (file_exists($p)) {
            require_once($p);
        }
    }
}
