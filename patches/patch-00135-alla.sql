
-- InnoDB doesn't support the same FULL TEXT indexes as MyISAM
ALTER TABLE task DROP INDEX taskName_2;

-- Convert all the table types over to InnoDB cause we're going to add
-- referential integrity to alloc
ALTER TABLE absence ENGINE = InnoDB;
ALTER TABLE announcement ENGINE = InnoDB;
ALTER TABLE client ENGINE = InnoDB;
ALTER TABLE clientContact ENGINE = InnoDB;
ALTER TABLE comment ENGINE = InnoDB;
ALTER TABLE config ENGINE = InnoDB;
ALTER TABLE expenseForm ENGINE = InnoDB;
ALTER TABLE history ENGINE = InnoDB;
ALTER TABLE invoice ENGINE = InnoDB;
ALTER TABLE invoiceItem ENGINE = InnoDB;
ALTER TABLE item ENGINE = InnoDB;
ALTER TABLE loan ENGINE = InnoDB;
ALTER TABLE patchLog ENGINE = InnoDB;
ALTER TABLE permission ENGINE = InnoDB;
ALTER TABLE person ENGINE = InnoDB;
ALTER TABLE project ENGINE = InnoDB;
ALTER TABLE projectCommissionPerson ENGINE = InnoDB;
ALTER TABLE projectPerson ENGINE = InnoDB;
ALTER TABLE role ENGINE = InnoDB;
ALTER TABLE reminder ENGINE = InnoDB;

