-- Insert new tables for the product module

CREATE TABLE product (
  productID int(11) NOT NULL auto_increment,
  productName varchar(255) NOT NULL DEFAULT '',
  buyCost DECIMAL(19,2) NOT NULL DEFAULT 0,
  sellPrice DECIMAL(19,2) NOT NULL DEFAULT 0,
  description varchar(255),
  comment TEXT,
  PRIMARY KEY(productID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productCost (
  productCostID int(11) NOT NULL auto_increment,
  productID int(11) NOT NULL,
  tfID int(11) DEFAULT 0,
  amount DECIMAL(19,2) NOT NULL DEFAULT 0,
  isPercentage BOOL DEFAULT 0,
  description varchar(255),
  PRIMARY KEY(productCostID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productSale (
  productSaleID int(11) NOT NULL auto_increment,
  projectID int(11) NOT NULL,
  status enum('edit', 'admin', 'invoiced', 'finished') DEFAULT NULL,
  productSaleCreatedTime datetime default NULL,
  productSaleCreatedUser int(11) default NULL,
  productSaleModifiedTime datetime default NULL,
  productSaleModifiedUser int(11) default NULL,
  PRIMARY KEY(productSaleID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productSaleItem (
  productSaleItemID int(11) NOT NULL auto_increment,
  productID int(11) NOT NULL,
  productSaleID int(11) NOT NULL,
  buyCost DECIMAL(19,2) NOT NULL DEFAULT 0,
  sellPrice DECIMAL(19,2) NOT NULL DEFAULT 0,
  quantity int(5) DEFAULT 1,
  description varchar(255),
  PRIMARY KEY(productSaleItemID)
) TYPE=MyISAM PACK_KEYS=0;

CREATE TABLE productSaleTransaction (
  productSaleTransactionID int(11) NOT NULL auto_increment,
  productSaleItemID int(11) NOT NULL,
  tfID int(11) DEFAULT 0,
  amount DECIMAL (19,2) NOT NULL DEFAULT 0,
  isPercentage BOOL DEFAULT 0,
  description varchar(255),
  PRIMARY KEY (productSaleTransactionID)
) TYPE=MyISAM PACK_KEYS=0;

ALTER TABLE transaction ADD productSaleItemID int(11) default NULL AFTER timeSheetID;
ALTER TABLE transaction CHANGE transactionType transactionType enum('invoice','expense','salary','commission','timesheet','adjustment','tax','product') NOT NULL;

 
INSERT INTO `permission` (`tableName`, `entityID`, `personID`, `roleName`, `allow`, `sortKey`, `comment`, `actions`)
VALUES
('product', 0, 0, '', 'Y', 0, 'Users can view product templates', 1),
('product', 0, 0, 'manage', 'Y', 100, 'Manager can manipulate all product templates.', 15),
('productCost', 0, 0, 'manage', 'Y', 100, 'Manager can manipulate all product templates.', 15),
('productCost', 0, 0, '', 'Y', 100, 'Users can view product templates', 1),
('productSale', 0, 0, 'manage', 'Y', 100, 'Managers can manipulate product sales.', 15),
('productSale', 0, 0, '', 'Y', 100, 'Users can view product sales', 1),
('productSaleItem', 0, 0, 'manage', 'Y', 100, 'Managers can manipulate product sales.', 15),
('productSaleItem', 0, 0, '', 'Y', 100, 'Users can view product sales', 1),
('productSaleTransaction', 0, 0, 'manage', 'Y', 100, 'Managers can manipulate product sales', 15),
('productSaleTransaction', 0, 0, '', 'Y', 100, 'Users can view product sales', 1);

