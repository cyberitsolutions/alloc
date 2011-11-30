-- New config values for rss feed
INSERT INTO config (name,value,type) VALUES ('rssStatusFilter', 'a:7:{i:0;s:12:"pending_info";i:1;s:15:"pending_manager";i:2;s:14:"pending_client";i:3;s:14:"closed_invalid";i:4;s:16:"closed_duplicate";i:5;s:17:"closed_incomplete";i:6;s:15:"closed_complete";}', 'array');
INSERT INTO config (name,value,type) VALUES ('rssEntries', '20', 'text');
INSERT INTO config (name,value,type) VALUES ('rssShowProject', 'on', 'text');
