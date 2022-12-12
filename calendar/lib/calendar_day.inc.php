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


class calendar_day
{
    public $date;          // Y-m-d
    public $day;           // Mon
    public $display_date;  // m-Y
    public $links;
    public $class;
    public $absences = array();
    public $start_tasks = array();
    public $complete_tasks = array();
    public $reminders = array();

    public function __construct()
    {
    }

    public function set_date($date)
    {
        $this->date = $date;
        $this->day = format_date("D", $date);
        $this->display_date = format_date("j M", $date);

        if ($this->date == date("Y-m-d")) {
            $this->class = "today";

        // Toggle every second month to have slightly different coloured shading
        } elseif (date("n", format_date("U", $this->date)) % 2 == 0) {
            $this->class = "even";
        }
    }

    public function set_links($links)
    {
        $this->links = $links;
    }

    public function draw_day_html()
    {
        global $TPL;

        if ($this->absences) {
            $rows[] = "<br>Absent:";
            $rows[] = implode("<br>", $this->absences);
        }

        if ($this->start_tasks) {
            $rows[] = "<br>To be started:";
            $rows[] = implode("<br>", $this->start_tasks);
        }

        if ($this->complete_tasks) {
            $rows[] = "<br>To be complete:";
            $rows[] = implode("<br>", $this->complete_tasks);
        }
        if ($this->reminders) {
            $rows[] = "<br>Reminders:";
            $rows[] = implode("<br>", $this->reminders);
        }

        echo "\n<td class=\"calendar_day ".$this->class."\">";
        echo "<h1>".$this->links.$this->display_date."</h1>";

        if (count($rows)) {
            echo implode("<br>", $rows);
        }

        echo "</td>";
    }
}
