-- Convert all the rest of the table types over to InnoDB
ALTER TABLE sess ENGINE = InnoDB;
ALTER TABLE skill ENGINE = InnoDB;
ALTER TABLE proficiency ENGINE = InnoDB;
ALTER TABLE task ENGINE = InnoDB;
ALTER TABLE auditItem ENGINE = InnoDB;
ALTER TABLE interestedParty ENGINE = InnoDB;
ALTER TABLE commentTemplate ENGINE = InnoDB;
ALTER TABLE taskType ENGINE = InnoDB;
ALTER TABLE tf ENGINE = InnoDB;
ALTER TABLE tfPerson ENGINE = InnoDB;
ALTER TABLE timeSheet ENGINE = InnoDB;
ALTER TABLE timeSheetItem ENGINE = InnoDB;
ALTER TABLE timeUnit ENGINE = InnoDB;
ALTER TABLE token ENGINE = InnoDB;
ALTER TABLE tokenAction ENGINE = InnoDB;
ALTER TABLE transaction ENGINE = InnoDB;
ALTER TABLE transactionRepeat ENGINE = InnoDB;
ALTER TABLE product ENGINE = InnoDB;
ALTER TABLE productCost ENGINE = InnoDB;
ALTER TABLE productSale ENGINE = InnoDB;
ALTER TABLE productSaleItem ENGINE = InnoDB;

