<?php

$login = file_get_contents('http://localhost/FixMyDevice/public/index.php?url=login');
echo 'Demo pills in login: ' . (strpos($login, 'demo-fill-bar') !== false ? 'FOUND (BAD)' : 'NONE (CLEAN)') . PHP_EOL;

$track = file_get_contents('http://localhost/FixMyDevice/public/index.php?url=track');
echo 'Demo codes in track: ' . (strpos($track, 'TKT-2026-1001') !== false ? 'FOUND (BAD)' : 'NONE (CLEAN)') . PHP_EOL;

$home = file_get_contents('http://localhost/FixMyDevice/public/index.php');
echo 'Demo section in home: ' . (strpos($home, 'demo-access-section') !== false ? 'FOUND (BAD)' : 'NONE (CLEAN)') . PHP_EOL;
echo 'Placeholder mock code in home: ' . (strpos($home, 'TKT-2026-1001') !== false ? 'FOUND (BAD)' : 'NONE (CLEAN)') . PHP_EOL;
