-- Allow updating of interested parties
UPDATE permission SET actions = 11 WHERE tableName = 'interestedParty' AND actions = 9;
