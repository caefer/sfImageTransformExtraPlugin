<?php

class sfTransformsCheckcachingTask extends sfBaseTask
{
  private $error = false;

  protected function configure()
  {
    $this->addArgument('application', sfCommandArgument::REQUIRED, 'The application name');
    $this->addOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'prod');
    $this->addOption('route-name', null, sfCommandOption::PARAMETER_REQUIRED, 'The sfImageTransform routename', 'sf_image');

    $this->namespace        = 'transforms';
    $this->name             = 'check-caching';
    $this->briefDescription = 'Run this task to check if your settings allow the thumbnail caching to work properly.';
    $this->detailedDescription = <<<EOF
The [transforms:check-caching|INFO] task performs a series of tests on your project to verify the thumbnail caching to work.
Call it with:

  [php symfony transforms:check-caching application|INFO]

  Please read the output carefully especially if one or more checks fail.

  You can also run the tests for a specific environment by providing the env option. It defaults to prod which in most cases is the only environment you want your cache to be enabled.

  [php symfony transforms:check-caching application --env=prod|INFO]

  The tasks assumes the default route name sf_image for your thumbnails. If you use a different one you can specify it with the route-name option.

  [php symfony transforms:check-caching application --route-name=your_thumbnail_route|INFO]

  Please note that the permission checks can not be reliable as they are performed with the system permissions of your current user account while your web server should run with a different user account which might have different priviledges.
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->log($this->briefDescription);
    $this->checkIfThumbnailModuleIsEnabled($arguments['application']);
    $this->checkIfNoScriptNameIsTrue($arguments['application']);
    $this->checkIfCachingIsActivated($arguments['application']);
    $absolutePathToThumbnailCacheDir = $this->checkForRoute($options['route-name']);
    $this->checkCacheDirExistsAndIsWritable($absolutePathToThumbnailCacheDir);

    if(true === $this->error)
    {
      $this->logBlock(array(
        'Your current settings will probably prevent caching of the generated thumbnails.',
        'Please have a look at the above error messages and comments to correct this.',
      ), 'ERROR');
    }
    else
    {
      $this->log('Everything seems to be alright. If it still does not work it\'s probably a permissions problem.');
    }
  }

  private function checkIfThumbnailModuleIsEnabled($application)
  {
    if(in_array('sfImageTransformator', sfConfig::get('sf_enabled_modules', array())))
    {
      $this->logSection('module', 'The module "sfImageTransformator" is enabled.', null, 'INFO');
    }
    else
    {
      $this->logSection('module', 'The module "sfImageTransformator" is not enabled in apps/'.$application.'/config/settings.yml', null, 'ERROR');
      $this->logBlock(array(
        'You must add the "sfImageTransformator" module to the \'enabled_modules\' setting',
        'in your applications settings.yml. Otherwise the application will not be able to',
        'generate any images.'
      ), 'COMMENT');
      $this->error = true;
    }
  }

  private function checkIfNoScriptNameIsTrue($application)
  {
    if(false !== sfConfig::get('sf_no_script_name', false))
    {
      $this->logSection('no script name', 'sf_no_script_name is set to true.', null, 'INFO');
    }
    else
    {
      $this->logSection('no script name', 'sf_no_script_name is set to false in apps/'.$application.'/config/settings.yml', null, 'ERROR');
      $this->logBlock(array(
        'The setting \'sf_no_script_name\' in your applications settings.yml must be set to',
        'true in order to cache the generated thumbnails. The reason for this is that a',
        'front controller i.e. frontend_dev.php or index.php is always a file and therefor',
        'can not be part of an absolute filepath (a file can not be a driectory)'
      ), 'COMMENT');
      $this->error = true;
    }
  }

  private function checkIfCachingIsActivated($application)
  {
    if(false !== sfConfig::get('sf_cache', false))
    {
      $this->logSection('caching', 'sf_cache is set to true.', null, 'INFO');
    }
    else
    {
      $this->logSection('caching', 'sf_cache is set to false in apps/'.$application.'/config/settings.yml', null, 'ERROR');
      $this->logBlock(array(
        'sfImageTransformExtraPlugins caching is build on top of symfonys native caching',
        'You have to enable the caching in your apps setting.yml to cache thumbnails.'
      ), 'COMMENT');
      $this->error = true;
    }
  }

  private function checkForRoute($routeName)
  {
    if($this->getRouting()->hasRouteName($routeName))
    {
      $this->logSection('route', '\''.$routeName.'\' exists.', null, 'INFO');
    }
    else
    {
      $this->logSection('route', '\''.$routeName.'\' does not exist.', null, 'ERROR');
      $this->logBlock(array(
        'The route that is used to generate and cache your thumbnails does not exist.',
        'Please check if you provided the correct route name or if the default route',
        '\'sf_image\' was accidentally overridden.',
      ), 'COMMENT');
      $this->error = true;
      return false;
    }
    $routes = $this->getRouting()->getRoutes();
    $tokens = $routes[$routeName]->getTokens();
    $absolutePathToThumbnailCacheDir = '';
    foreach($tokens AS $token)
    {
      if(!in_array($token[0], array('separator', 'text')))
      {
        break;
      }

      $absolutePathToThumbnailCacheDir .= $token[2];
    }
    $this->logSection('route', 'Route \''.$routeName.'\' points to \''.$absolutePathToThumbnailCacheDir.'\'.', null, 'INFO');
    $absolutePathToThumbnailCacheDir = sfConfig::get('sf_web_dir').$absolutePathToThumbnailCacheDir;
    $this->logSection('route', 'The absolute path for this is \''.$absolutePathToThumbnailCacheDir.'\'.', null, 'INFO');

    return $absolutePathToThumbnailCacheDir;
  }

  private function checkCacheDirExistsAndIsWritable($cacheDir)
  {
    $relativeCacheDir = str_replace(sfConfig::get('sf_web_dir'), '', $cacheDir);
    $cacheDirParts = explode('/', rtrim($relativeCacheDir, '/'));
    while(0 < count($cacheDirParts) && !file_exists(sfConfig::get('sf_web_dir').implode('/', $cacheDirParts)))
    {
      $this->logSection('cache dir', 'Path \''.implode('/', $cacheDirParts).'\' does not exist. Let\'s move one level up.', null, 'INFO');
      array_pop($cacheDirParts);
    }

    if(count($cacheDirParts))
    {
      $cacheDir = sfConfig::get('sf_web_dir').implode('/', $cacheDirParts);
      $this->logSection('cache dir', 'Path \''.$cacheDir.'\' exists.', null, 'INFO');
    }
    else
    {
      $this->logSection('cache dir', 'Path \''.$cacheDir.'\' does not exist.', null, 'ERROR');
      $this->logBlock(array(
        'The cache dir is specified in your routing.yml. It is the fixed path at the beginning',
        'and defaults to \'/thumbnails/\'. This part of the URL is also the path to the cache',
        'dir relative to your applications web dir/doc root. It must exists in order for your',
        'web server to store the generated thumbnails in it.'
      ), 'COMMENT');
      $this->error = true;
      return false;
    }

    if(is_dir($cacheDir))
    {
      $this->logSection('cache dir', 'Path \''.$cacheDir.'\' is a directory.', null, 'INFO');
    }
    else
    {
      $this->logSection('cache dir', 'Path \''.$cacheDir.'\' is not a directory.', null, 'ERROR');
      $this->logBlock(array(
        'The cache dir must be a directory and not a file as the web server must be',
        'able to save files in it. Please make sure that the path exists.',
      ), 'COMMENT');
      $this->error = true;
      return false;
    }

    if(is_writable($cacheDir))
    {
      $this->logSection('cache dir', 'Path \''.$cacheDir.'\' is writable.', null, 'INFO');
      $this->logBlock(array(
        'Please note that this check is testing priviledges for your current user account.',
        'If your web server is running from a different user account (as it should) the',
        'result could be different.',
      ), 'COMMENT');
    }
    else
    {
      $this->logSection('cache dir', 'Path \''.$cacheDir.'\' is not writable.', null, 'ERROR');
      $this->logBlock(array(
        'The cache dir must be writable for the web server in order to save the generated',
        'thumbnails in it.',
        'Please note that this check is testing priviledges for your current user account.',
        'If your web server is running from a different user account (as it should) the',
        'result could be different.',
      ), 'COMMENT');
      $this->error = true;
    }
  }
}
