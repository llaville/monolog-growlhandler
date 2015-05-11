<?php
/**
 * This custom example will :
 * - log all events to a file called "growl_conf.log"
 * - notify events with Growl
 *
 * growl-starkicon.png usage
 *  License: Free for non-commercial use.
 *  Commercial usage: Not allowed
 * @link     http://www.iconarchive.com/show/stark-icons-by-fruityth1ng/Growl-icon.html
 *
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD License
 * @since    Example available since Release 1.0.0
 */

$baseDir   = dirname(__DIR__);
$vendorDir = $baseDir . '/vendor';

require_once $vendorDir . '/autoload.php';

use Bartlett\Monolog\Handler\GrowlHandler;
use Bartlett\Monolog\Handler\CallbackFilterHandler;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

// Create the logger
$logger = new Logger('growl_conf');

// Create filter rules
$filters = array(
    function ($record) {
        if ($record['level'] < Logger::WARNING) {
            return true;
        }
        if (!array_key_exists('exception', $record['context'])) {
            return false;
        }
        return (preg_match('/^An error has occured/', $record['message']) === 1);
    }
);

// Create some handlers
$stream = new RotatingFileHandler(__DIR__ . DIRECTORY_SEPARATOR . 'growl_conf.log');
$stream->setFilenameFormat('{filename}-{date}', 'Ymd');

$logger->pushHandler($stream);

try {
    $resourceDir   = __DIR__ . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR;
    $notifications = array(
        GrowlHandler::DEBUG => array(
            'icon'    => $resourceDir . 'green.png',
        ),
        GrowlHandler::INFO => array(
            'icon'    => $resourceDir . 'green.png',
        ),
        GrowlHandler::NOTICE => array(
            'icon'    => $resourceDir . 'yellow.png',
        ),
        GrowlHandler::WARNING => array(
            'icon'    => $resourceDir . 'yellow.png',
        ),
        GrowlHandler::ERROR => array(
            'icon'    => $resourceDir . 'red.png',
        ),
        GrowlHandler::CRITICAL => array(
            'icon'    => $resourceDir . 'red.png',
        ),
        GrowlHandler::ALERT => array(
            'icon'    => $resourceDir . 'red.png',
        ),
        GrowlHandler::EMERGENCY => array(
            'icon'    => $resourceDir . 'red.png',
        ),
    );
    $options = array(
        'AppIcon' => $resourceDir . 'growl-starkicon.png',
        // if you have troubles with Net_Growl then debug requests to a local file
        //'debug'   => __DIR__ . '/net_growl_debug.log',
    );

    $growl = new GrowlHandler(
        array(
            'name'          => 'My Custom Growl',
            'notifications' => $notifications,
            'options'       => $options,
        )
    );
    $growl->setFormatter(
        new LineFormatter("%message%\n%level_name%")
    );

    $logger->pushHandler(new CallbackFilterHandler($growl, $filters));

} catch (\Exception $e) {
    // Growl server is probably not started
    echo $e->getMessage(), PHP_EOL, PHP_EOL;
}

// You can now use your logger
$logger->addInfo('My logger is now ready');

// This record won't be stopped by the $filters rules, but by the growl $notifications config
$logger->addDebug('A debug message.');

$logger->addError('An error has occured. Will be logged to file BUT NOT notified by Growl.');

try {
    throw new \RuntimeException();

} catch (\Exception $e) {
    $logger->addCritical(
        'An error has occured. Will be logged to file AND notified by Growl.',
        array('exception' => $e)
    );
}
