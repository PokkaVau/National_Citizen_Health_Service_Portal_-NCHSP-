<?php
// Store sensitive keys here. Not to be committed to version control in real scenario.
require_once __DIR__ . '/env_loader.php';

// Store sensitive keys here. Not to be committed to version control in real scenario.
define('GROQ_API_KEY', getenv('GROQ_API_KEY'));
define('GROQ_API_URL', getenv('GROQ_API_URL'));
?>