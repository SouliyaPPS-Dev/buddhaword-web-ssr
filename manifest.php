<?php
header('Content-Type: application/json');
header('Cache-Control: public, max-age=86400');
readfile(__DIR__ . '/public/manifest.json');
