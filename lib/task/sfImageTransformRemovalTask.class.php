<?php
/**
 * This file is part of the sfImageTransformExtraPlugin package.
 * (c) 2009-2010 Christian Schaefer <schaefer.christian@guj.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage task
 * @author     Christian Schaefer <schaefer.christian@guj.de>
 * @version    SVN: $Id: sfImageTransformRemovalTask.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * With this task you can remove thumbnails generated by sfImageTransformExtraPlugin.
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage task
 */
class sfImageTransformRemovalTask extends sfBaseTask
{
  /**
   * Declares this task to the Symfony task system with all arguments and options.
   *
   * @return void
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
    $this->addArgument('route', sfCommandArgument::REQUIRED, 'The sf_image route that generated the image(s) you want to remove');

    $this->aliases          = array(
      'remove-thumbnails'
    );
    $this->namespace        = 'transforms';
    $this->name             = 'remove';
    $this->briefDescription = 'Removes thumbnails generated by sfImageTransformExtraPlugin.';

    $this->detailedDescription = <<<EOF
Removes thumbnails generated by sfImageTransformExtraPlugin.
EOF;
  }

  /**
   * Dynamically adds options to this task based on the current route.
   *
   * @param sfCommandManager $commandManager
   * @param array            $options        Options as read from commandline
   *
   * @return void
   * @see sfTask
   */
  protected function process(sfCommandManager $commandManager, $options)
  {
    $commandManager->process($options);

    if(array_key_exists('application', $commandManager->getArgumentValues()) && array_key_exists('route', $commandManager->getArgumentValues()))
    {
      $application = $commandManager->getArgumentValue('application');
      $routeName = $commandManager->getArgumentValue('route');
      $configuration = $this->createConfiguration($application, 'prod');

      $this->route = sfImageTransformExtraPluginConfiguration::getRoute($routeName, $configuration);

      $routeVariables = array_keys($this->route->getVariables());
      $this->options = array();
      $optionSet = new sfCommandOptionSet();
      foreach($commandManager->getErrors() as $error)
      {
        if(preg_match('/"--([\w-_]+)"/', $error, $matches) && in_array($matches[1], $routeVariables))
        {
          $option = new sfCommandOption($matches[1], null, sfCommandOption::PARAMETER_OPTIONAL, '', null);
          $this->options[] = $option;
          $optionSet->addOption($option);
        }
      }
      $commandManager->setOptionSet($optionSet);
    }

    parent::process($commandManager, $options);
  }

  /**
   * Executes this task with all arguments and options from commandline
   *
   * @param array $arguments Arguments as read from commandline
   * @param array $options   Options as read from commandline
   *
   * @return void
   * @see sfTask
   */
  protected function execute($arguments = array() , $options = array())
  {
    if(count($options) || $this->askConfirmation('Do you really want to delete all generated images?', 'QUESTION', false))
    {
      $cache = sfImageTransformExtraPluginConfiguration::getCache();
      $this->route->preassemblePattern($options);
      $cache->removePattern($this->route);
      $this->logSection('files-', 'Generated images removed.');
    }
  }
}
