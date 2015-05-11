<?php
/**
 * Unit Test Case that covers the growl handler.
 *
 * PHP version 5
 *
 * @category   Logging
 * @package    monolog-growlhandler
 * @subpackage Tests
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    GIT: $Id$
 * @link       http://php5.laurent-laville.org/growlhandler/
 * @since      Class available since Release 1.0.0
 */

namespace Bartlett\Tests\Monolog\Handler;

use Bartlett\Monolog\Handler\GrowlHandler;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;

/**
 * Unit Test Case that covers Bartlett\Monolog\Handler\GrowlHandler
 *
 * @category   Logging
 * @package    monolog-growlhandler
 * @subpackage Tests
 * @author     Laurent Laville <pear@laurent-laville.org>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: @package_version@
 * @link       http://php5.laurent-laville.org/growlhandler/
 */
class GrowlHandlerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $growl;

    /**
     * Sets up the fixture.
     *
     * @return void
     * @link   http://phpunit.de/manual/current/en/fixtures.html
     */
    public function setUp()
    {
        $this->growl = $this
            ->getMockBuilder('Net_Growl')
            ->disableOriginalConstructor()  // ctor is protected
            ->getMock()
        ;
        $this->growl->expects($this->any())
            ->method('register')
            ->will(
                $this->returnValue(
                    new \Net_Growl_Response("GNTP/1.0 -OK NONE")
                )
            )
        ;
    }

    /**
     * Filter events on standard log level (without restriction).
     *
     *  covers Bartlett\Monolog\Handler\GrowlHandler::isHandling
     * @dataProvider provideSuiteRecords
     */
    public function testIsHandling()
    {
        $record  = $this->formatRecord(func_get_args());
        $handler = new GrowlHandler($this->growl);

        $this->assertTrue($handler->isHandling($record));
    }

    /**
     * Filter events on standard log level (greater or equal than WARNING).
     *
     *  covers Bartlett\Monolog\Handler\GrowlHandler::isHandling
     * @dataProvider provideSuiteRecords
     */
    public function testIsHandlingLevel()
    {
        $record  = $this->formatRecord(func_get_args());
        $testlvl = Logger::WARNING;
        $handler = new GrowlHandler($this->growl, $testlvl);

        if ($record['level'] >= $testlvl) {
            $this->assertTrue($handler->isHandling($record));
        } else {
            $this->assertFalse($handler->isHandling($record));
        }
    }

    /**
     * Filter events on batch mode.
     *
     *  covers Bartlett\Monolog\Handler\GrowlHandler::handleBatch
     */
    public function testHandleBatch()
    {
        $records = $this->getMultipleRecords();

        $this->growl->expects($this->exactly(5))
            ->method('notify')
        ;

        $handler = new GrowlHandler($this->growl);
        $handler->handleBatch($records);
    }

    /**
     *  covers Bartlett\Monolog\Handler\GrowlHandler::handle
     *  covers Bartlett\Monolog\Handler\GrowlHandler::pushProcessor
     *  covers Bartlett\Monolog\Handler\GrowlHandler::setFormatter
     */
    public function testHandleUsesProcessors()
    {
        $sender = 'PHP 7.0.0-dev';

        $record = $this->getRecord(Logger::WARNING, 'caution message');
        $record['extra']['sender'] = $sender;

        $formatter = new LineFormatter("%message%\n%level_name%\n%extra.sender%");

        $this->growl->expects($this->any())
            ->method('notify')
            ->with(
                $record['level_name'],
                $record['channel'],
                $formatter->format($record)
            )
        ;

        $handler = new GrowlHandler($this->growl);
        $handler->setFormatter($formatter);
        $handler->pushProcessor(
            function ($record) use($sender) {
                $record['extra']['sender'] =  $sender; // 'PHP ' . phpversion();
                return $record;
            }
        );
        $handler->handle($record);
    }

    /**
     * Bad growl configuration.
     *
     * @expectedException \InvalidArgumentException
     */
    public function testHandleWithBadGrowlThrowsException()
    {
        $handler = new GrowlHandler(null);
    }
}
