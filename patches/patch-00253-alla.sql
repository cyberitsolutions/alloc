ALTER TABLE reminder ADD reminderHash varchar(255) DEFAULT NULL AFTER reminderTime;
ALTER TABLE reminder ADD CONSTRAINT reminder_reminderHash FOREIGN KEY (reminderHash) REFERENCES token (tokenHash);

INSERT INTO tokenAction (tokenActionID,tokenAction,tokenActionType,tokenActionMethod) VALUES (3, "Task status move pending to open","task","moved_from_pending_to_open");

