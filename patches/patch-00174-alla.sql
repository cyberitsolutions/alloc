-- Add the new commentMaster and commentMasterID fields
ALTER TABLE comment ADD commentMaster varchar(255) NOT NULL DEFAULT '' AFTER commentID;
ALTER TABLE comment ADD commentMasterID integer NOT NULL AFTER commentMaster;

-- Indexes 
CREATE INDEX commentMaster ON comment (commentMaster);
CREATE INDEX commentMasterID ON comment (commentMasterID);

-- Populate the new fields
UPDATE comment SET commentMaster = commentType, commentMasterID = commentLinkID WHERE commentType != 'comment';

   UPDATE comment c1 
LEFT JOIN comment c2 ON (c1.commentType = 'comment' AND c1.commentLinkID = c2.commentID) 
      SET c1.commentMaster = c2.commentType, c1.commentMasterID = c2.commentLinkID 
    WHERE c1.commentType = 'comment';

