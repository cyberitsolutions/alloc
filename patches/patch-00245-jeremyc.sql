-- Ensure project short names are unique.
UPDATE project SET projectShortName = NULL WHERE projectShortName = '';
ALTER TABLE project add unique (projectShortName);
