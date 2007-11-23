-- Add field for emergency contact details
ALTER TABLE person ADD emergencyContact varchar(255) DEFAULT "" AFTER phoneNo2;

