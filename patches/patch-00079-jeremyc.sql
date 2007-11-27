-- Add field for TF status
ALTER TABLE tf MODIFY status enum('active', 'disabled') DEFAULT 'active';

