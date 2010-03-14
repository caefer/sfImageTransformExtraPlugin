<?php
/**
 * This file is part of the sfImageTransformExtraPlugin unit tests package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPluginUnitTests
 * @subpackage bootstrap
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfRawFileCache.class.php 63 2010-03-09 04:34:28Z caefer $
 */

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

/** symfonys autoloader */
require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

$projectPath = dirname(__FILE__).'/../fixtures/project';
/** configuration of the fixture project */
require_once($projectPath.'/config/ProjectConfiguration.class.php');

if(!isset($app))
{
  $configuration = new ProjectConfiguration($projectPath);
}
else
{
  $configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
  sfContext::createInstance($configuration);
}

function sfImageTransformExtraPlugin_autoload_again($class)
{
  $autoload = sfSimpleAutoload::getInstance();
  $autoload->reload();
  return $autoload->autoload($class);
}
spl_autoload_register('sfImageTransformExtraPlugin_autoload_again');

if (file_exists($config = dirname(__FILE__).'/../../config/sfImageTransformExtraPluginConfiguration.class.php'))
{
  require_once $config;
  $plugin_configuration = new sfImageTransformExtraPluginConfiguration($configuration, dirname(__FILE__).'/../..', 'sfImageTransformExtraPlugin');
}
else
{
  $plugin_configuration = new sfPluginConfigurationGeneric($configuration, dirname(__FILE__).'/../..', 'sfImageTransformExtraPlugin');
}
