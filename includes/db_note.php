<?php
include 'db.php';

// ── Secure DB connection using config constants ───────────────
// (This file already uses $conn from db.php)

// db.php is kept simple for XAMPP compatibility.
// config.php defines constants; db.php uses its own vars for mysqli.
// They coexist without conflict.

// Note: If you want to fully centralize, you can replace db.php content
// with the code below and include config.php first.
// For now, keep db.php as-is for backward compatibility with existing files.
