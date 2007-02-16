-- New tables to allow user customisation of html elements

DROP TABLE IF EXISTS htmlElement;

CREATE TABLE htmlElement ( 
  htmlElementID INT(11) NOT NULL auto_increment,
  htmlElementTypeID INT(11) NOT NULL,
  htmlElementParentID INT(11) DEFAULT 0,
  handle VARCHAR(255) NOT NULL,
  label VARCHAR(255) DEFAULT NULL,
  helpText TEXT DEFAULT NULL,
  defaultValue VARCHAR(255) DEFAULT NULL,
  sequence INT(11) DEFAULT 0,
  enabled  INT(1) DEFAULT 1,
  PRIMARY KEY (htmlElementID)
);

DROP TABLE IF EXISTS htmlAttribute;

CREATE TABLE htmlAttribute ( 
  htmlAttributeID INT(11) NOT NULL auto_increment,
  htmlElementID INT(11) NOT NULL,
  name VARCHAR(255) DEFAULT NULL,
  value VARCHAR(255) DEFAULT NULL,
  isDefault INT(1) DEFAULT 0,
  PRIMARY KEY (htmlAttributeID)
);

DROP TABLE IF EXISTS htmlElementType;

CREATE TABLE htmlElementType ( 
  htmlElementTypeID INT(11) NOT NULL auto_increment,
  handle VARCHAR(255) DEFAULT NULL,
  name VARCHAR(255) DEFAULT NULL,
  hasEndTag INT(1) DEFAULT 1,
  hasChildElement INT(1) DEFAULT 0,
  hasContent INT(1) DEFAULT 0,

  hasValueContent INT(1) DEFAULT 0,
  hasValueAttribute INT(1) DEFAULT 0,
  valueAttributeName VARCHAR(255) DEFAULT NULL,
  parentHtmlElementID INT(11) DEFAULT 0,
  PRIMARY KEY  (htmlElementTypeID)
);

-- INSERT select, table, tr, td, label, content (ie plaintext), 
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (1,'select','select',1,1,0,0,0,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (2,'option','option',1,0,1,0,1,"selected",1);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (3,'textarea','textarea',1,0,1,1,0,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (4,'input_checkbox','input',0,0,0,0,1,"checked",NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (5,'input_text','input',0,0,0,0,1,"value",NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (6,'input_hidden','input',0,0,0,0,1,"value",NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (7,'input_submit','input',0,0,0,0,1,"value",NULL);


DROP TABLE IF EXISTS htmlAttributeType;

CREATE TABLE htmlAttributeType ( 
  htmlAttributeTypeID INT(11) NOT NULL auto_increment,
  htmlElementTypeID INT(11) DEFAULT NULL,
  name VARCHAR(255) NOT NULL DEFAULT "",
  mandatory INT(1) NOT NULL DEFAULT 0,
  defaultValue VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (htmlAttributeTypeID)
);

INSERT INTO htmlAttributeType (htmlElementTypeID,name,mandatory,defaultValue) VALUES (1,"size",1,"1");
INSERT INTO htmlAttributeType (htmlElementTypeID,name,mandatory,defaultValue) VALUES (3,"rows",1,"4");
INSERT INTO htmlAttributeType (htmlElementTypeID,name,mandatory,defaultValue) VALUES (3,"cols",1,"60");

INSERT INTO htmlAttributeType (name,mandatory) VALUES ("class",0);
INSERT INTO htmlAttributeType (name,mandatory) VALUES ("value",0);
INSERT INTO htmlAttributeType (name,mandatory) VALUES ("name",1);
INSERT INTO htmlAttributeType (name,mandatory) VALUES ("id",1);

-- align, class, name, value, size



