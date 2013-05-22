-- remove unique constraint on comment.commentEmailUID field.
ALTER TABLE comment DROP KEY commentEmailUID;
