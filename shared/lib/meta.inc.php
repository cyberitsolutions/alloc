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

class meta extends db_entity
{
    private $t;

    // This variable contains the definitive list of all the referential
    // integrity tables that the user is allowed to edit.
    public static $tables = array("absenceType"               => "Absence Types",
                                  "clientStatus"              => "Client Statuses",
                                  "itemType"                  => "Item Types",
                                  "projectType"               => "Project Types",
                                  "currencyType"              => "Currency Types",
                                  "projectStatus"             => "Project Statuses",
                                  "taskStatus"                => "Task Statuses",
                                  "skillProficiency"          => "Skill Proficiencies",
                                  "transactionType"           => "Transaction Types",
                                  "timeSheetItemMultiplier"   => "Time Sheet Multipliers",
                                  "taskType"                  => "Task Types");

    public function __construct($table = "")
    {
        $this->classname = $table;
        $this->data_table = $table;
        $this->display_field_name = $table."ID";
        $this->key_field = $table."ID";
        $this->data_fields = array($table."Seq", $table."Active");
        if ($table == "taskStatus") {
            $this->data_fields[] = "taskStatusLabel";
            $this->data_fields[] = "taskStatusColour";
        } elseif ($table == "currencyType") {
            $this->data_fields[] = "currencyTypeLabel";
            $this->data_fields[] = "currencyTypeName";
            $this->data_fields[] = "numberToBasic";
        }
        $this->t = $table; // for internal use
        return parent::__construct();
    }

    public function get_tables()
    {
        return self::$tables;
    }

    public function get_list($include_inactive = false)
    {
        if ($this->data_table) {
            $include_inactive and $where[$this->data_table."Active"] = "all"; // active and inactive
            return $this->get_assoc_array(false, false, false, $where);
        }
    }

    public function get_label()
    {
        if ($this->data_table) {
            return self::$tables[$this->data_table];
        }
    }

    public function validate()
    {
        $this->get_id() or $err[] = "Please enter a Value/ID for the ".$this->get_label();
        $this->get_value($this->t."Seq") or $err[] = "Please enter a Sequence Number for the ".$this->get_label();
        return parent::validate($err);
    }
}
