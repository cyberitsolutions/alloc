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

class home_item
{
    public $name;
    public $label;
    public $module;
    public $template;
    public $library;
    public $width = "standard";
    public $help_topic;
    public $seq;
    public $print;

    public function __construct($name, $label, $module, $template, $width = "standard", $seq = 0, $print = true)
    {
        $this->name = $name;
        $this->label = $label;
        $this->module = $module;
        $this->template = $template;
        $this->width = $width;
        $this->seq = $seq;
        $this->print = $print;
    }

    public function get_template_dir()
    {
        return ALLOC_MOD_DIR.$this->module."/templates/";
    }

    public function get_seq()
    {
        return $this->seq;
    }

    public function show()
    {
        global $TPL;
        if ($this->template) {
            $TPL[$this->module] = $this;
            include_template($this->get_template_dir().$this->template);
        }
    }

    public function visible()
    {
        return true;
    }

    public function render()
    {
        return false;
    }

    public function get_label()
    {
        return $this->label;
    }

    public function get_title()
    {
        return $this->get_label();
    }

    public function get_width()
    {
        return $this->width;
    }

    public function get_help()
    {
        if ($this->help_topic) {
            page::help($this->help_topic);
        }
    }
}
