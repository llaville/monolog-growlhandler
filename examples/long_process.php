<?php
/**
 * This example will :
 * - log all events to a file called "long_process.log"
 * - notify with Growl the end of a user long process
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
use Monolog\Processor\PsrLogMessageProcessor;

$processors = array(
    new PsrLogMessageProcessor(),
);

// Create the logger
$logger = new Logger('long_process', array(), $processors);

// Create some handlers
$stream = new RotatingFileHandler(__DIR__ . DIRECTORY_SEPARATOR . 'long_process.log');
$stream->setFilenameFormat('{filename}-{date}', 'Ymd');

$logger->pushHandler($stream);

try {
    // Create filter rules
    $filters = array(
        function ($record) {
            if (!array_key_exists('count', $record['context'])) {
                return false;
            }
            return ($record['context']['count'] > 5);
        }
    );

    $growl = new CallbackFilterHandler(
        new GrowlHandler(
            array(), // with all default options
            Logger::INFO
        ),
        $filters
    );

    $logger->pushHandler($growl);

} catch (\Exception $e) {
    // Growl server is probably not started
    echo $e->getMessage(), PHP_EOL;
}

// Processing each file in a queue
$queue = new \SplQueue();
for ($i = 1; $i < 10; $i++) {
    $queue->enqueue( sprintf('File_%02d.txt', $i) );
}
$fileCount = count($queue);

while (!$queue->isEmpty()) {
    $file = $queue->dequeue();

    $logger->addInfo('Processing file "{filename}"', array('filename' => $file));
    echo '.';

    // simulate the long process
    sleep(1);
}

$logger->addInfo(
    'Long Process with {count} files, is over !',
    array('count' => $fileCount)
);
