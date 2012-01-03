#!/bin/bash

# This script creates the views for a single user alloc database
# This is to be imported as the mysql root user eg:
# ./make_single_user_db.sh | mysql -u root -p alloc_someuser

# This script can be re-imported repeatedly and it should rebuild clean every time

username="boppity"
password="boppity"
personID="4"

cat <<EOD

DROP DATABASE IF EXISTS alloc_${username};
CREATE DATABASE alloc_${username};


CREATE OR REPLACE VIEW alloc_${username}.timeSheet     AS SELECT * FROM alloc.timeSheet     WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.timeSheetItem AS SELECT * FROM alloc.timeSheetItem WHERE personID = ${personID};
CREATE OR REPLACE VIEW alloc_${username}.task          AS SELECT * FROM alloc.task;

CREATE OR REPLACE VIEW alloc_${username}.project AS SELECT project.*, projectPerson.rate as rate, projectPerson.rateUnitID as rateUnitID
                                                      FROM alloc.project
                                                 LEFT JOIN alloc.projectPerson ON projectPerson.projectID = project.projectID 
                                                     WHERE projectPerson.personID = ${personID};

CREATE OR REPLACE VIEW alloc_${username}.person        AS SELECT personID, username, emailAddress, availability, areasOfInterest, comments, managementComments, lastLoginDate, personModifiedUser, firstName, surname, preferred_tfID, personActive, sessData, phoneNo1, phoneNo2, emergencyContact FROM alloc.person;


-- can read everything in their database
GRANT SELECT ON alloc_${username}.* TO ${username} IDENTIFIED BY '${password}';

-- can insert, update and delete these tables:
GRANT INSERT,UPDATE,DELETE ON alloc_${username}.timeSheet TO ${username};
GRANT INSERT,UPDATE,DELETE ON alloc_${username}.timeSheetItem TO ${username};
GRANT INSERT,UPDATE,DELETE ON alloc_${username}.task TO ${username};


EOD
