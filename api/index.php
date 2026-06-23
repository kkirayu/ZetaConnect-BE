<?php

// Override the script name to prevent Laravel from stripping '/api' from the URL
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Forward Vercel requests to Laravel's public/index.php
require __DIR__ . '/../public/index.php';
