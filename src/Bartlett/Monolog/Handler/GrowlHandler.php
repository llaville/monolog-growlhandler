<?php
/**
 * Growl Handler for Monolog.
 *
 * @category Logging
 * @package  monolog-growlhandler
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version  GIT: $Id$
 * @link     http://php5.laurent-laville.org/growlhandler/
 * @link     http://pear.php.net/package/Net_Growl
 */

namespace Bartlett\Monolog\Handler;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Formatter\LineFormatter;

require_once 'Net/Growl/Autoload.php';

/**
 * Monolog handler that send notifications to Growl.
 *
 * @category Logging
 * @package  monolog-growlhandler
 * @author   Laurent Laville <pear@laurent-laville.org>
 * @license  http://www.opensource.org/licenses/bsd-license.php BSD License
 * @version  Release: @package_version@
 * @since    Class available since Release 1.0.0
 */
class GrowlHandler extends AbstractProcessingHandler
{
    /**
     * Notification types
     */
    const DEBUG     = 'DEBUG';
    const INFO      = 'INFO';
    const NOTICE    = 'NOTICE';
    const WARNING   = 'WARNING';
    const ERROR     = 'ERROR';
    const CRITICAL  = 'CRITICAL';
    const ALERT     = 'ALERT';
    const EMERGENCY = 'EMERGENCY';

    private $growl;

    /**
     * {@inheritdoc}
     */
    public function __construct($growl, $level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);

        if (is_array($growl)) {

            if (isset($growl['name'])) {
                $name = $growl['name'];
            } else {
                $name = 'Growl for Monolog';
            }

            if (isset($growl['notifications'])) {
                $notifications = $growl['notifications'];
            } else {
                $notifications = array();
            }

            if (empty($notifications)) {
                // default growl channels
                $notifications = array(
                    self::DEBUG,
                    self::INFO,
                    self::NOTICE,
                    self::WARNING,
                    self::ERROR,
                    self::CRITICAL,
                    self::ALERT,
                    self::EMERGENCY,
                );
            }

            if (isset($growl['password'])) {
                $password = $growl['password'];
            } else {
                $password = '';
            }

            if (isset($growl['options'])) {
                $options = $growl['options'];
            } else {
                $options = array();
            }

            if (!isset($options['protocol'])) {
                // changed from default udp protocol to gntp
                $options['protocol'] = 'gntp';
            }

            $this->growl = \Net_Growl::singleton(
                $name, $notifications, $password, $options
            );

        } elseif ($growl instanceof \Net_Growl) {
            $this->growl = $growl;

        } else {
            throw new \InvalidArgumentException(
                'Expect to be either an array or a Net_Growl instance. ' .
                gettype($growl) . ' provided.'
            );
        }

        $response = $this->growl->register();
        if ($response->getStatus() != 'OK') {
            throw new \RuntimeException(
                'Growl Error ' . $response->getErrorCode() .
                ' - ' . $response->getErrorDescription()
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->growl->notify(
            $record['level_name'],
            $record['channel'],
            $record['formatted']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter()
    {
        return new LineFormatter("%message%");
    }
}
