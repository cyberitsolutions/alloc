
UPDATE comment SET commentEmailUID = commentEmailUIDORIG WHERE commentEmailUID is null and commentEmailUIDORIG IS NOT NULL;
