
-- Fix the case of project types
UPDATE projectType SET projectTypeID = 'Contract' WHERE projectTypeID = 'contract';
UPDATE projectType SET projectTypeID = 'Job' WHERE projectTypeID = 'job';
UPDATE projectType SET projectTypeID = 'Prepaid' WHERE projectTypeID = 'prepaid';
UPDATE projectType SET projectTypeID = 'Project' WHERE projectTypeID = 'project';

-- Fix the ordering
UPDATE projectType SET projectTypeSeq = 1 WHERE projectTypeID = 'Project';
UPDATE projectType SET projectTypeSeq = 2 WHERE projectTypeID = 'Contract';
UPDATE projectType SET projectTypeSeq = 3 WHERE projectTypeID = 'Job';
UPDATE projectType SET projectTypeSeq = 4 WHERE projectTypeID = 'Prepaid';

-- Just in case the database cascade doesn't work, update the project records
UPDATE project SET projectType = 'Contract' WHERE projectType = 'contract';
UPDATE project SET projectType = 'Job' WHERE projectType = 'job';
UPDATE project SET projectType = 'Prepaid' WHERE projectType = 'prepaid';
UPDATE project SET projectType = 'Project' WHERE projectType = 'project';
