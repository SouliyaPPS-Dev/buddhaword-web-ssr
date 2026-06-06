<?php
header('Content-Type: application/javascript');
header('Cache-Control: no-cache, no-store, must-revalidate');
readfile(__DIR__ . '/public/sw.js');
