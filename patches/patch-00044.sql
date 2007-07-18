-- extend length of password field
ALTER TABLE person change password password varchar(255) NOT NULL DEFAULT '';

