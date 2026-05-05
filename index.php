<?php

declare(strict_types=1);

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/index.php';
$projectBase = rtrim(str_replace('\\', '/', dirname($scriptName)), '/.');
$target = ($projectBase !== '' ? $projectBase : '').'/public/';

header('Location: '.$target, true, 302);
exit;
