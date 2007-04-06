-- add customeBilledDollars to timeSheet table
ALTER TABLE timeSheet ADD customerBilledDollars decimal(19,2) default '0.00';
