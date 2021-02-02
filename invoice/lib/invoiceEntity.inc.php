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

define("DEFAULT_SEP", "\n");
class invoiceEntity extends db_entity
{
    public $classname = "invoiceEntity";
    public $data_table = "invoiceEntity";
    public $key_field = "invoiceEntityID";
    public $data_fields = array("invoiceID",
                                "timeSheetID",
                                "expenseFormID",
                                "productSaleID",
                                "useItems");

    public function create($invoiceID, $entity, $entityID, $useItems = 0)
    {
        $q = prepare("SELECT * FROM invoiceEntity WHERE invoiceID = %d AND %sID = %d", $invoiceID, $entity, $entityID);
        $db = new db_alloc();
        $db->query($q);
        $row = $db->row();
        $ie = new invoiceEntity();
        if ($row) {
            $ie->set_id($row["invoiceEntityID"]);
        }
        $ie->set_value("invoiceID", $invoiceID);
        $ie->set_value($entity."ID", $entityID);
        $ie->set_value("useItems", sprintf("%d", $useItems));
        $ie->save();
    }

    public function get($entity, $entityID)
    {
        $q = prepare("SELECT invoiceEntity.*,invoice.invoiceNum
                        FROM invoiceEntity
                   LEFT JOIN invoice ON invoiceEntity.invoiceID = invoice.invoiceID
                       WHERE invoiceEntity.%sID = %d
                     ", $entity, $entityID);
        $db = new db_alloc();
        $db->query($q);
        while ($row = $db->row()) {
            $rows[] = $row;
        }
        return (array)$rows;
    }

    public function get_links($invoiceID)
    {
        $rows = invoiceEntity::get("invoice", $invoiceID); // cheating :)
        foreach ($rows as $row) {
            if ($row["timeSheetID"]) {
                $timeSheet = new timeSheet($row["timeSheetID"]);
                $timeSheet_links[] = $timeSheet->get_link();
            }

            if ($row["expenseFormID"]) {
                $expenseForm = new expenseForm($row["expenseFormID"]);
                $expenseForm_links[] = $expenseForm->get_link();
            }

            if ($row["productSaleID"]) {
                $productSale = new productSale($row["productSaleID"]);
                $productSale_links[] = $productSale->get_link();
            }
        }

        $timeSheet_links and $rtn[] = "Time Sheet: ".implode(", ", (array)$timeSheet_links);
        $expenseForm_links and $rtn[] = "Expense Form: ".implode(", ", (array)$expenseForm_links);
        $productSale_links and $rtn[] = "Product Sale: ".implode(", ", (array)$productSale_links);
        return implode(" / ", (array)$rtn);
    }

    public function save_invoice_timeSheet($invoiceID, $timeSheetID)
    {
        global $TPL;
        $invoice = new invoice($invoiceID);
        if ($invoice->get_value("invoiceStatus") != "finished") {
            $timeSheet = new timeSheet();
            $timeSheet->set_id($timeSheetID);
            $timeSheet->select();
            $timeSheet->load_pay_info();
            $project = $timeSheet->get_foreign_object("project");
            $date = $timeSheet->get_value("dateFrom") or $date = date("Y-m-d");

            // customerBilledDollars will not be set if the actual field is blank,
            // and thus there won't be a usable total_customerBilledDollars.
            if (isset($timeSheet->pay_info["customerBilledDollars"])) {
                $amount = $timeSheet->pay_info["total_customerBilledDollars"];
                $iiUnitPrice = $timeSheet->pay_info["customerBilledDollars"];
                $iiQuantity = $timeSheet->pay_info["total_duration"];
            } else {
                $amount = $timeSheet->pay_info["total_dollars"];
                $iiUnitPrice = $amount;
                $iiQuantity = 1;
            }

            $q = prepare("SELECT * FROM invoiceItem WHERE invoiceID = %d AND timeSheetID = %d AND timeSheetItemID IS NULL
                   ", $invoiceID, $timeSheetID);
            $db = new db_alloc();
            $db->query($q);
            $row = $db->row();
            $ii = new invoiceItem();
            if ($row) {
                $ii->set_id($row["invoiceItemID"]);
            }
            $ii->set_value("invoiceID", $invoiceID);
            $ii->set_value("timeSheetID", $timeSheet->get_id());
            $ii->set_value("iiMemo", "Time Sheet #".$timeSheet->get_id()." for ".person::get_fullname($timeSheet->get_value("personID")).", Project: ".$project->get_value("projectName"));
            $ii->set_value("iiQuantity", $iiQuantity);
            $ii->set_value("iiUnitPrice", $iiUnitPrice);
            $ii->set_value("iiAmount", $amount);
            $ii->set_value("iiDate", $date);
            $ii->set_value("iiTax", config::get_config_item("taxPercent"));
            $ii->currency = $timeSheet->get_value("currencyTypeID");
            $ii->save();
        } else {
            alloc_error("Unable to update related Invoice (ID:".$invoiceID.").");
        }
    }

    public function save_invoice_timeSheetItems($invoiceID, $timeSheetID)
    {
        $timeSheet = new timeSheet();
        $timeSheet->set_id($timeSheetID);
        $timeSheet->select();
        $currency = $timeSheet->get_value("currencyTypeID");
        $timeSheet->load_pay_info();
        $amount = $timeSheet->pay_info["total_customerBilledDollars"] or $amount = $timeSheet->pay_info["total_dollars"];

        $project = $timeSheet->get_foreign_object("project");
        $client = $project->get_foreign_object("client");

        $db = new db_alloc();
        $q1 = $db->query(prepare("SELECT * FROM timeSheetItem WHERE timeSheetID = %d", $timeSheetID));
        while ($row = $db->row($q1)) {
            if (imp($timeSheet->pay_info["customerBilledDollars"])) {
                $iiUnitPrice = $timeSheet->pay_info["customerBilledDollars"];
            } else {
                $iiUnitPrice = page::money($currency, $row["rate"], "%mo");
            }

            unset($str);
            if ($row["comment"] && !$row["commentPrivate"]) {
                $str = $row["comment"];
            }

            // Look for an existing invoiceItem
            $q = prepare("SELECT invoiceItem.invoiceItemID
                            FROM invoiceItem
                       LEFT JOIN invoice ON invoiceItem.invoiceID = invoice.invoiceID
                           WHERE invoiceItem.timeSheetID = %d
                             AND invoiceItem.timeSheetItemID = %d
                             AND invoiceItem.invoiceID = %d
                             AND invoice.invoiceStatus != 'finished'
                        ORDER BY iiDate DESC LIMIT 1
                         ", $timeSheet->get_id(), $row["timeSheetItemID"], $invoiceID);
            $q2 = $db->query($q);
            $r2 = $db->row($q2);

            $ii = new invoiceItem();
            if ($r2["invoiceItemID"]) {
                $ii->set_id($r2["invoiceItemID"]);
            }
            $ii->currency = $currency;
            $ii->set_value("invoiceID", $invoiceID);
            $ii->set_value("timeSheetID", $timeSheet->get_id());
            $ii->set_value("timeSheetItemID", $row["timeSheetItemID"]);
            $ii->set_value("iiMemo", "Time Sheet for ".person::get_fullname($timeSheet->get_value("personID")).", Project: ".$project->get_value("projectName").", ".$row["description"]."\n".$str);
            $ii->set_value("iiQuantity", $row["timeSheetItemDuration"] * $row["multiplier"]);
            $ii->set_value("iiUnitPrice", $iiUnitPrice);
            $ii->set_value("iiAmount", $iiUnitPrice * $row["timeSheetItemDuration"] * $row["multiplier"]);
            $ii->set_value("iiDate", $row["dateTimeSheetItem"]);
            $ii->set_value("iiTax", config::get_config_item("taxPercent"));
            $ii->save();
        }
    }

    public function save_invoice_expenseForm($invoiceID, $expenseFormID)
    {
        $expenseForm = new expenseForm();
        $expenseForm->set_id($expenseFormID);
        $expenseForm->select();
        $db = new db_alloc();
        $db->query("SELECT max(transactionDate) as maxDate
                      FROM transaction
                     WHERE expenseFormID = %d", $expenseFormID);
        $row = $db->row();
        $amount = $expenseForm->get_abs_sum_transactions();

        $q = prepare("SELECT * FROM invoiceItem WHERE expenseFormID = %d AND transactionID IS NULL", $expenseFormID);
        $db = new db_alloc();
        $q2 = $db->query($q);
        $r2 = $db->row($q2);
        $ii = new invoiceItem();
        if ($r2) {
            $ii->set_id($r2["invoiceItemID"]);
        }
        $ii->set_value("invoiceID", $invoiceID);
        $ii->set_value("expenseFormID", $expenseForm->get_id());
        $ii->set_value("iiMemo", "Expense Form #".$expenseForm->get_id()." for ".person::get_fullname($expenseForm->get_value("expenseFormCreatedUser")));
        $ii->set_value("iiQuantity", 1);
        $ii->set_value("iiUnitPrice", $amount);
        $ii->set_value("iiAmount", $amount);
        $ii->set_value("iiDate", $row["maxDate"]);
        $ii->set_value("iiTax", config::get_config_item("taxPercent"));
        $ii->save();
    }

    public function save_invoice_expenseFormItems($invoiceID, $expenseFormID)
    {
        $expenseForm = new expenseForm();
        $expenseForm->set_id($expenseFormID);
        $expenseForm->select();
        $db = new db_alloc();
        $q1 = $db->query("SELECT * FROM transaction WHERE expenseFormID = %d", $expenseFormID);
        while ($row = $db->row($q1)) {
            $amount = page::money($row["currencyTypeID"], $row["amount"], "%mo");

            $q = prepare("SELECT * FROM invoiceItem WHERE expenseFormID = %d AND transactionID = %d", $expenseFormID, $row["transactionID"]);
            $db = new db_alloc();
            $q2 = $db->query($q);
            $r2 = $db->row($q2);
            $ii = new invoiceItem();
            if ($r2) {
                $ii->set_id($r2["invoiceItemID"]);
            }
            $ii->currency = $row["currencyTypeID"];
            $ii->set_value("invoiceID", $invoiceID);
            $ii->set_value("expenseFormID", $expenseForm->get_id());
            $ii->set_value("transactionID", $row["transactionID"]);
            $ii->set_value("iiMemo", "Expenses for ".person::get_fullname($expenseForm->get_value("expenseFormCreatedUser")).", ".$row["product"]);
            $ii->set_value("iiQuantity", $row["quantity"]);
            $ii->set_value("iiUnitPrice", $amount/$row["quantity"]);
            $ii->set_value("iiAmount", $amount);
            $ii->set_value("iiDate", $row["transactionDate"]);
            $ii->set_value("iiTax", config::get_config_item("taxPercent"));
            $ii->save();
        }
    }

    public function save_invoice_productSale($invoiceID, $productSaleID)
    {
        $productSale = new productSale();
        $productSale->set_id($productSaleID);
        $productSale->select();
        $db = new db_alloc();
        $db->query("SELECT max(transactionDate) as maxDate
                      FROM transaction
                     WHERE productSaleID = %d", $productSaleID);
        $row = $db->row();
        $amounts = $productSale->get_amounts();

        $q = prepare("SELECT * FROM invoiceItem WHERE productSaleID = %d AND productSaleItemID IS NULL", $productSaleID);
        $db = new db_alloc();
        $q2 = $db->query($q);
        $r2 = $db->row($q2);
        $ii = new invoiceItem();
        if ($r2) {
            $ii->set_id($r2["invoiceItemID"]);
        }
        $ii->set_value("invoiceID", $invoiceID);
        $ii->set_value("productSaleID", $productSale->get_id());
        $ii->set_value("iiMemo", "Sale #".$productSale->get_id()." for ".person::get_fullname($productSale->get_value("personID")));
        $ii->set_value("iiQuantity", 1);
        $ii->set_value("iiUnitPrice", $amounts["total_sellPrice_value"]);
        $ii->set_value("iiAmount", $amounts["total_sellPrice_value"]);
        $ii->set_value("iiDate", $row["maxDate"]);
        $ii->save();
    }

    public function save_invoice_productSaleItems($invoiceID, $productSaleID)
    {
        $productSale = new productSale();
        $productSale->set_id($productSaleID);
        $productSale->select();
        $db = new db_alloc();
        $q = prepare("SELECT * FROM productSaleItem WHERE productSaleID = %d", $productSale->get_id());
        $q1 = $db->query($q);
        while ($row = $db->row($q1)) {
            $q = prepare("SELECT * FROM invoiceItem WHERE productSaleID = %d AND productSaleItemID = %d", $productSaleID, $row["productSaleItemID"]);
            $db = new db_alloc();
            $q2 = $db->query($q);
            $r2 = $db->row($q2);
            $ii = new invoiceItem();
            if ($r2) {
                $ii->set_id($r2["invoiceItemID"]);
            }
            $ii->currency = $row["sellPriceCurrencyTypeID"];
            $ii->set_value("invoiceID", $invoiceID);
            $ii->set_value("productSaleID", $productSale->get_id());
            $ii->set_value("productSaleItemID", $row["productSaleItemID"]);
            $ii->set_value("iiMemo", "Sale (".$productSale->get_id().") item for ".person::get_fullname($productSale->get_value("personID")).", ".$row["description"]);
            $ii->set_value("iiQuantity", $row["quantity"]);
            $row["sellPrice"] = page::money($ii->currency, $row["sellPrice"]/$row["quantity"], "%mo");
            $ii->set_value("iiUnitPrice", $row["sellPrice"]);
            $ii->set_value("iiAmount", $row["sellPrice"]*$row["quantity"]);
            $d = $productSale->get_value("productSaleDate") or $d = $productSale->get_value("productSaleModifiedTime") or $d = $productSale->get_value("productSaleCreatedTime");
            $ii->set_value("iiDate", $d);
            $ii->save();
        }
    }
}
