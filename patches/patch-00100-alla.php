<?php

// Add new Estimate options to the types of printable time sheets.
$ops = config::get_config_item("timeSheetPrintOptions");
$ops["timeSheetPrintMode=estimate"] = "Estimate";
$ops["timeSheetPrintMode=estimate&printDesc=1"] = "Estimate+";

$config = new config();
$id = $config->get_config_item_id("timeSheetPrintOptions");

$c = new config();
$c->set_id($id);
$c->select();
$c->set_value("value",serialize($ops));
$c->save();

?>
