-- Add field for TF status
ALTER TABLE tf add status enum('active', 'disabled', 'readonly') DEFAULT 'active';

