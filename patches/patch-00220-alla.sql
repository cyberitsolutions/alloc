-- Add new field to client contacts to permit deactivated client contacts
ALTER TABLE clientContact ADD clientContactActive BOOLEAN DEFAULT TRUE AFTER primaryContact;
