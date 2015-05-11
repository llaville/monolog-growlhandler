<?php
/**
 * This example will :
 * - notify with Growl, CRITICAL events or higher
 *
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since    Example available since Release 1.0.0
 */

$baseDir   = dirname(__DIR__);
$vendorDir = $baseDir . '/vendor';

require_once $vendorDir . '/autoload.php';

use Bartlett\Monolog\Handler\GrowlHandler;

use Monolog\Logger;

// Create the logger
$logger = new Logger('my_logger');

// Create some handlers
try {
    $growl = new GrowlHandler(
        array(), // with all default options
        Logger::CRITICAL
    );

    $logger->pushHandler($growl);

} catch (\Exception $e) {
    // Growl server is probably not started
    echo $e->getMessage(), PHP_EOL;
}

// You can now use your logger
$logger->addInfo('My logger is now ready');

$logger->addError('An error has occured.');

try {
    throw new \RuntimeException();

} catch (\Exception $e) {
    $logger->addCritical(
        'A critical condition has occured. You will be notified by growl.',
        array('exception' => $e)
    );
}
