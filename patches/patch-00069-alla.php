<?php


// Change email default From email addresses from Alex Lance <alla@cyber.com.dsa> to alla@cyber.com.dsa
$email = config::get_config_item("AllocFromEmailAddress");
$email = preg_replace("/^.*</","",$email);
$email = str_replace(">","",$email);

$configID = config::get_config_item_id("AllocFromEmailAddress");

$c = new config();
$c->set_id($configID);
$c->select();
$c->set_value("value",$email);
$c->save();




?>
