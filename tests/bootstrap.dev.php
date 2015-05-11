<?php

require dirname(__DIR__) . '/vendor/autoload.php';

require __DIR__ . '/ResultPrinter.php';
require __DIR__ . '/MonologConsoleLogger.php';

require_once 'Net/Growl/Autoload.php';

date_default_timezone_set('UTC');
