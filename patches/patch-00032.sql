-- Nuke eventFilter table
drop table eventFilter;

-- Nuke permission entries for eventFilter
delete from permission where tableName='eventFilter';
