-- Add index to client table for primary contact
ALTER TABLE client ADD INDEX idx_clientPrimaryContactID (clientPrimaryContactID);
