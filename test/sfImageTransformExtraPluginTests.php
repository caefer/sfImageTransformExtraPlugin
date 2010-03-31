<?php
/**
 * This file is part of the sfImageTransformExtraPlugin unit tests package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfRawFileCache.class.php 63 2010-03-09 04:34:28Z caefer $
 */

/** central bootstrap for unit tests */
require_once dirname(__FILE__).'/bootstrap/unit.php';
/** PHPUnit Framework */
require_once 'PHPUnit/Framework.php';

/**
 * PHPUnit test suite for sfImageTransformExtraPlugin
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage TestSuite
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfImageTransformExtraPluginTests
{
  public static function suite()
  {
    global $configuration, $plugin_configuration;
    $suite = new PHPUnit_Framework_TestSuite('sfImageTransformExtraPlugin');

    // loading plugin configurations
    $configuration = ProjectConfiguration::getActive();
    $pluginConfig = $configuration->getPluginConfiguration('sfImageTransformExtraPlugin');

    // instantiate a fake symfony unit test task to retrieve all connected tests for this plugin
    $task = new sfTestUnitTask($configuration->getEventDispatcher(), new sfFormatter());
    $event = new sfEvent($task, 'task.test.filter_test_files', array('arguments' => array('name' => array()), 'options' => array()));
    $files = $pluginConfig->filterTestFiles($event, array());
    $suite->addTestFiles($files);

    return $suite;
  }
}
