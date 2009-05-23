
-- fix up skillProficiencys stuff
alter table skillList rename to skill;
alter table skillProficiencys rename to proficiency;
update permission set tableName = 'skill' where tableName = 'skillList';
update permission set tableName = 'proficiency' where tableName = 'skillProficiencys';
