<?php

/*
 * Copyright (C) 2006-2019 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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

// DB abstraction
class db
{

    var $username;
    var $password;
    var $hostname;
    var $database;
    var $pdo;
    var $pdo_statement;
    var $row = array();
    var $error;
    public static $started_transaction = false;
    public static $stop_doing_queries = false;

    function __construct($username = "", $password = "", $hostname = "", $database = "")
    {
        // Constructor
        $this->username = $username;
        $this->password = $password;
        $this->hostname = $hostname;
        $this->database = $database;
    }

    function connect($force = false)
    {
        if ($force || !isset($this->pdo)) {
            $this->hostname and $h = "host=".$this->hostname.";";
            $this->database and $d = "dbname=".$this->database.";";
            try {
                $this->pdo = new PDO(sprintf('mysql:%s%scharset=UTF8', $h, $d), $this->username, $this->password);
                $this->pdo->exec("SET CHARACTER SET utf8");
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return true;
            } catch (PDOException $e) {
                $this->error("Unable to connect to database: ".$e->getMessage());
            }
        }
    }

    function start_transaction()
    {
        $this->connect();
        $this->pdo->beginTransaction();
        self::$started_transaction = true;
    }

    function commit()
    {
        if (self::$started_transaction && is_object($this->pdo)) {
            $rtn = $this->pdo->commit();
            if (!$rtn) {
                $this->error("Couldn't commit db transaction.");
            }
        }
    }

    function rollback()
    {
        if (self::$started_transaction) {
            self::$started_transaction = false;
            try {
                $this->pdo->rollBack();
            } catch (Exception $e) {
                return false;
            }
        }
    }

    function error($msg = false, $errno = false)
    {
        if ($errno == 1451 || $errno == 1217) {
            $m = "Error: ".$errno." There are other records in the database that depend on the item you just tried to delete.
            Remove those other records first and then try to delete this item again.
            <br><br>".$msg;
        } else if ($errno == 1216) {
            $m = "Error: ".$errno." The parent record of the item you just tried to create does not exist in the database.
            Create that other record first and then try to create this item again.
            <br><br>".$msg;
        } else if (preg_match("/(ALLOC ERROR:([^']*)')/m", $msg, $matches)) {
            $m = "Error: ".$matches[2];
        } else if ($msg) {
            $m = "Error: ".$msg;
        }

        if ($m) {
            alloc_error($m);
        }

        $this->error = $msg;
    }

    function get_error()
    {
        return trim($this->error);
    }

    function get_insert_id()
    {
        return $this->pdo->lastInsertId();
    }

    function esc($str)
    {
        if (is_numeric($str)) {
            return $str;
        }
        if (!isset($this->pdo)) {
            $this->connect();
        }
        $v = $this->pdo->quote($str);
        substr($v, -1) == "'" and $v = substr($v, 0, -1);
        substr($v, 0, 1) == "'" and $v = substr($v, 1);
        return $v;
    }

    function select_db($db = "")
    {
        // Select a database
        $this->database = $db;
        return $this->connect(true);
    }

    function qr()
    {
        // Quick Row run it like this:
        // $row = $db->qr("SELECT * FROM hey WHERE heyID = %d",$heyID);
        // arguments will be automatically escaped
        $args = func_get_args();
        $query = $this->get_escaped_query_str($args);
        $id = $this->query($query);
        return $this->row($id);
    }

    private function _query($query)
    {
        if (!self::$stop_doing_queries || $query == "ROLLBACK") {
            try {
                return $this->pdo->query($query);
            } catch (PDOException $e) {
                $this->error("Error executing query: ".$e->getMessage());
            }
        }
    }

    function query()
    {
        global $TPL;
        $current_user = &singleton("current_user");
        $start = microtime();
        $this->connect();
        $args = func_get_args();
        $query = $this->get_escaped_query_str($args);

        if ($query && !self::$stop_doing_queries) {
            if (is_object($current_user) && method_exists($current_user, "get_id") && $current_user->get_id()) {
                $this->_query(prepare("SET @personID = %d", $current_user->get_id()));
            } else {
                $this->_query("SET @personID = NULL");
            }

            $rtn = $this->_query($query);

            if (!$rtn) {
                $info = $this->pdo->errorInfo();
                $this->error("Query failed: ".$info[0]." ".$info[1]."\n".$query, $info[2]);
                $this->rollback();
                unset($this->pdo_statement);
            } else {
                $this->pdo_statement = $rtn;
                $this->error();
            }
        }

        $result = timetook($start, false);
        if ($result > $TPL["slowest_query_time"]) {
            $TPL["slowest_query"] = $query;
            $TPL["slowest_query_time"] = $result;
        }
        $TPL["all_page_queries"][] = array("time"=>$result, "query"=>$query);
        return $rtn;
    }

    function num($pdo_statement = "")
    {
        $pdo_statement or $pdo_statement = $this->pdo_statement;
        return $pdo_statement->rowCount();
    }

    function num_rows($pdo_statement = "")
    {
        return $this->num($pdo_statement);
    }

    function row($pdo_statement = "", $method = PDO::FETCH_ASSOC)
    {
        if (!self::$stop_doing_queries) {
            $pdo_statement or $pdo_statement = $this->pdo_statement;
            if ($pdo_statement) {
                unset($this->row);
                if (isset($this->pos)) {
                    $this->row = $pdo_statement->fetch($method, PDO::FETCH_ORI_ABS, $this->pos);
                    unset($this->pos);
                } else {
                    $this->row = $pdo_statement->fetch($method, PDO::FETCH_ORI_NEXT);
                }
                return $this->row;
            }
        }
    }

    // DEPRECATED
    function next_record()
    {
        return $this->row();
    }

    function f($name)
    {
        return $this->row[$name];
    }

    // Return true if a particular table exists
    function table_exists($table, $db = "")
    {
        $db or $db = $this->database;
        $prev_db = $this->database;
        $this->select_db($db);
        $query = prepare('SHOW TABLES LIKE "%s"', $table);
        $this->query($query);
        while ($row = $this->row($this->pdo_statement, PDO::FETCH_NUM)) {
            if ($row[0] == $table) {
                $yep = true;
            }
        }
        $this->select_db($prev_db);
        return $yep;
    }

    function get_table_fields($table)
    {
        static $fields;

        if ($fields[$table]) {
            return $fields[$table];
        }
        $database = $this->database;
        if (strstr($table, ".")) {
            list($database,$table) = explode(".", $table);
        }
        $this->query("SHOW COLUMNS FROM ".$table);
        while ($row = $this->row()) {
            $fields[$table][] = $row["Field"];
        }
        $fields[$table] or $fields[$table] = array();
        return $fields[$table];
    }

    function get_table_keys($table)
    {
        static $keys;
        if ($keys[$table]) {
            return $keys[$table];
        }

        $this->query("SHOW KEYS FROM %s", $table);
        while ($row = $this->row()) {
            if (!$row["Non_unique"]) {
                $keys[$table][] = $row["Column_name"];
            }
        }
        return $keys[$table];
    }

    function save($table, $row = array(), $debug = 0)
    {
        $table_keys = $this->get_table_keys($table) or $table_keys = array();
        foreach ($table_keys as $k) {
            $row[$k] and $do_update = true;
            $keys[$k] = $row[$k];
        }
        $row = $this->unset_invalid_field_names($table, $row, $keys);

        if ($do_update) {
            $q = sprintf(
                "UPDATE %s SET %s WHERE %s",
                $table,
                $this->get_update_str($row),
                $this->get_update_str($keys, " AND ")
            );
            $debug &&  sizeof($row) and print ("<br>SAVE -> UPDATE -> Would have executed this query: <br>".$q);
            $debug && !sizeof($row) and print ("<br>SAVE -> UPDATE -> Would NOT have executed this query: <br>".$q);
            !$debug && sizeof($row) and $this->query($q);
            reset($keys);
            return current($keys);
        } else {
            $q = sprintf(
                "INSERT INTO %s (%s) VALUES (%s)",
                $table,
                $this->get_insert_str_fields($row),
                $this->get_insert_str_values($row)
            );
            $debug &&  sizeof($row) and print ("<br>SAVE -> INSERT -> Would have executed this query: <br>".$q);
            $debug && !sizeof($row) and print ("<br>SAVE -> INSERT -> Would NOT have executed this query: <br>".$q);
            !$debug && sizeof($row) and $this->query($q);
            return $this->get_insert_id();
        }
    }

    function delete($table, $row = array(), $debug = 0)
    {
        $row = $this->unset_invalid_field_names($table, $row);
        $q = sprintf(
            "DELETE FROM %s WHERE %s",
            $table,
            $this->get_update_str($row, " AND ")
        );
        $debug &&  sizeof($row) and print ("<br>DELETE -> WILL execute this query: <br>".$q);
        $debug && !sizeof($row) and print ("<br>DELETE -> WONT execute this query: <br>".$q);
        if (sizeof($row)) {
            $pdo_statement = $this->query($q);
            return $pdo_statement->rowCount();
        }
    }

    function get_insert_str_fields($row)
    {
        foreach ($row as $fieldname => $value) {
            $rtn .= $commar.$fieldname;
            $commar = ", ";
        }
        return $rtn;
    }

    function get_insert_str_values($row)
    {
        foreach ($row as $fieldname => $value) {
            $rtn .= $commar.$this->esc($value);
            $commar = ", ";
        }
        return $rtn;
    }

    function get_update_str($row, $glue = ", ")
    {
        foreach ($row as $fieldname => $value) {
            $rtn .= $commar." ".$fieldname." = ".$this->esc($value);
            $commar = $glue;
        }
        return $rtn;
    }

    function unset_invalid_field_names($table, $row, $keys = array())
    {
        $valid_field_names = $this->get_table_fields($table);
        $keys = array_keys($keys);

        foreach ($row as $field_name => $v) {
            if (!in_array($field_name, $valid_field_names) || in_array($field_name, $keys)) {
                unset($row[$field_name]);
            }
        }
        $row or $row = array();
        return $row;
    }

    function get_escaped_query_str($args)
    {
        return call_user_func_array("prepare", $args);
    }

    function seek($pos = 0)
    {
        $this->pos = $pos;
    }

    function get_encoding()
    {
        $this->query("SHOW VARIABLES LIKE 'character_set_client'");
        $row = $this->row();
        return $row["Value"];
    }

    function dump_db($filename)
    {
        if ($this->password) {
            $pw = " -p" . $this->password;
        }
        $command = sprintf("mysqldump -B -c --add-drop-table -h %s -u %s %s %s", $this->hostname, $this->username, $pw, $this->database);

        $command .= " >" . $filename;

        system($command);
    }
}
