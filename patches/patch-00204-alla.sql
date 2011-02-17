-- Nuke old header/footer entries
DELETE FROM config WHERE name = 'task_email_header';
DELETE FROM config WHERE name = 'task_email_footer';
