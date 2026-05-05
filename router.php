<?php

declare(strict_types=1);

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$documentRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
$resolvedRequestTarget = $documentRoot.('/' === $requestPath ? '/index.php' : $requestPath);

if ('' !== $documentRoot && is_file($resolvedRequestTarget)) {
    return false;
}

$publicPrefix = '/public';
$publicRequestPath = str_starts_with($requestPath, $publicPrefix.'/')
    ? substr($requestPath, strlen($publicPrefix))
    : $requestPath;
$publicFile = __DIR__.'/public'.$publicRequestPath;

if ('/' !== $publicRequestPath && is_file($publicFile)) {
    return false;
}

$frontController = str_starts_with($requestPath, $publicPrefix.'/') ? '/public/index.php' : '/index.php';
$_SERVER['SCRIPT_NAME'] = $frontController;
$_SERVER['PHP_SELF'] = $frontController;
$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/public/index.php';

require __DIR__.'/public/index.php';
