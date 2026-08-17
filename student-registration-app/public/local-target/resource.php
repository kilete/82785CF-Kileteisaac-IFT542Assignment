<?php
// A harmless "course resource" page - the ONLY kind of target the preview
// feature is meant to fetch. Used to demonstrate the allowlist working
// correctly (this succeeds) versus a blocked internal address (fails).
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html><head><title>IFT542 Reading List</title></head>
<body>
<h1>IFT542 Reading List</h1>
<p>Chapter 3: Threat Modelling with STRIDE. Chapter 7: Authentication and Session Management.</p>
</body></html>
