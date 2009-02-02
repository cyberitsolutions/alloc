
-- $cc[] = array("label"=>"Client", "value"=>1);
-- $cc[] = array("label"=>"Vendor", "value"=>2);
-- $cc[] = array("label"=>"Supplier", "value"=>3);
-- $cc[] = array("label"=>"Consultant", "value"=>4);
-- $cc[] = array("label"=>"Government", "value"=>5);
-- $cc[] = array("label"=>"Non-profit", "value"=>6);
-- $cc[] = array("label"=>"Internal", "value"=>7);

-- new config values for the clientCategory
INSERT INTO config (name, value, type) VALUES ("clientCategories",'a:7:{i:0;a:2:{s:5:"label";s:6:"Client";s:5:"value";i:1;}i:1;a:2:{s:5:"label";s:6:"Vendor";s:5:"value";i:2;}i:2;a:2:{s:5:"label";s:8:"Supplier";s:5:"value";i:3;}i:3;a:2:{s:5:"label";s:10:"Consultant";s:5:"value";i:4;}i:4;a:2:{s:5:"label";s:10:"Government";s:5:"value";i:5;}i:5;a:2:{s:5:"label";s:10:"Non-profit";s:5:"value";i:6;}i:6;a:2:{s:5:"label";s:8:"Internal";s:5:"value";i:7;}}','array');

-- new client.clientCategory field
ALTER TABLE client ADD clientCategory int(11) DEFAULT 1 AFTER clientStatus;
