<?php
/**
 * This file is part of the sfImageTransformExtraPlugin fixture project package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @ignore
 * @package    sfImageTransformExtraPluginFixtureProject
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfRawFileCache.class.php 63 2010-03-09 04:34:28Z caefer $
 */

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public function setup()
  {
    $this->setPlugins(array('sfImageTransformExtraPlugin', 'sfImageTransformPlugin', 'sfDoctrinePlugin'));
    $this->setPluginPath('sfImageTransformExtraPlugin', dirname(__FILE__).'/../../../..');
    $this->setPluginPath('sfImageTransformPlugin', dirname(__FILE__).'/../../../../../sfImageTransformPlugin');
  }

  public function setupPlugins()
  {
    $this->pluginConfigurations['sfImageTransformExtraPlugin']->connectTests();
  }
}
