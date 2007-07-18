-- Nuke old sessdata reference from sessions
UPDATE person SET sessData = replace(sessData,"projectSummary.php","projectGraph.php");
