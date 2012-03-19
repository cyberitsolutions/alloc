-- Simplify task comment subject lines
UPDATE config SET value = '%ti %tn [%tp]' WHERE name = 'emailSubject_taskComment';
