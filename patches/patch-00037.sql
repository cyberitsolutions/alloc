-- New tables to allow user customisation of html elements
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

CREATE TABLE htmlAttribute ( 
  htmlAttributeID INT(11) NOT NULL auto_increment,
  htmlElementID INT(11) NOT NULL,
  name VARCHAR(255) DEFAULT NULL,
  value VARCHAR(255) DEFAULT NULL,
  isDefault INT(1) DEFAULT 0,
  PRIMARY KEY (htmlAttributeID)
);

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
  hasLabelValue INT(1) DEFAULT 0,
  parentHtmlElementID INT(11) DEFAULT 0,
  PRIMARY KEY  (htmlElementTypeID)
);

CREATE TABLE htmlAttributeType ( 
  htmlAttributeTypeID INT(11) NOT NULL auto_increment,
  htmlElementTypeID INT(11) DEFAULT NULL,
  name VARCHAR(255) NOT NULL DEFAULT "",
  defaultValue VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY  (htmlAttributeTypeID)
);

-- INSERT select, table, tr, td, label, content (ie plaintext), 
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (1,'select','select',1,1,0,0,0,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (2,'option','option',1,0,1,0,1,"selected",1);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (3,'textarea','textarea',1,0,1,1,0,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (4,'input_checkbox','input',0,0,0,0,1,"checked",NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (5,'input_text','input',0,0,0,0,1,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID) VALUES (6,'input_hidden','input',0,0,0,0,1,NULL,NULL);
INSERT INTO htmlElementType (htmlElementTypeID,handle,name, hasEndTag,hasChildElement,hasContent,hasValueContent,hasValueAttribute,valueAttributeName,parentHtmlElementID,hasLabelValue) VALUES (7,'input_submit','input',0,0,0,0,1,NULL,NULL,1);

-- Insert default attributes for a Select
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (1,"size","1");
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (1,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (1,"id",NULL);

-- Insert default attributes for an Option
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (2,"value",NULL);

-- Insert default attributes for an Textarea
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"rows","4");
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (3,"cols","60");

-- Insert default attributes for an Checkbox
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"value",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (4,"type","checkbox");

-- Insert default attributes for an Textbox
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"size",60);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (5,"type","text");

-- Insert default attributes for an Hidden
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (6,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (6,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (6,"type","hidden");

-- Insert default attributes for an Submit
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (7,"name",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (7,"id",NULL);
INSERT INTO htmlAttributeType (htmlElementTypeID,name,defaultValue) VALUES (7,"type","submit");







