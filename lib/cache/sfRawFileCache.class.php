<?php
/**
 * This file is part of the sfImageTransformExtraPlugin package.
 * (c) 2010 Christian Schaefer <caefer@ical.ly>>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    sfImageTransformExtraPlugin
 * @author     Christian Schaefer <caefer@ical.ly>
 * @version    SVN: $Id: sfRawFileCache.class.php 29957 2010-06-24 08:24:23Z caefer $
 */

/**
 * Cache class that stores raw files without expire times.
 *
 * Almost an exact copy of sfFileCache but without writing cache meta information
 * such as lifetime and last modified to file.
 *
 * @package    sfImageTransformExtraPlugin
 * @subpackage cache
 * @author     Christian Schaefer <caefer@ical.ly>
 */
class sfRawFileCache extends sfFileCache
{
  /**
   * @see sfCache
   */
  public function get($key, $default = null)
  {
    return $default;
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    return false;
  }

  /**
   * Writes the given data in the cache file.
   *
   * $data is usually a serialized sfWebResponse object. As we need to save the content
   * in raw format we unserialize it and get the content.
   *
   * @param string  $path    The file path
   * @param string  $data    The data to put in cache
   * @param integer $timeout The timeout timestamp
   *
   * @return boolean true if ok, otherwise false
   *
   * @throws sfCacheException
   */
  protected function write($path, $data, $timeout) 
  {
    $response = unserialize($data);
    $data = $response->getContent();

    $current_umask = umask();
    umask(0000);
    
    if (!is_dir(dirname($path))) 
    {
      // create directory structure if needed
      mkdir(dirname($path) , 0777, true);
    }
    
    $tmpFile = tempnam(dirname($path) , basename($path));
    
    if (!$fp = @fopen($tmpFile, 'wb')) 
    {
      throw new sfCacheException(sprintf('Unable to write cache file "%s".', $tmpFile));
    }
    
    @fwrite($fp, $data);
    @fclose($fp);
    // Hack from Agavi (http://trac.agavi.org/changeset/3979)
    // With php < 5.2.6 on win32, renaming to an already existing file doesn't work, but copy does,
    // so we simply assume that when rename() fails that we are on win32 and try to use copy()
    if (!@rename($tmpFile, $path)) 
    {
      if (copy($tmpFile, $path)) 
      {
        unlink($tmpFile);
      }
    }
    
    chmod($path, 0666);
    umask($current_umask);
    
    return true;
  }

  protected function getPathEnd(sfRoute $pattern)
  {
    $path = '';
    $tokens   = array_reverse($pattern->getTokens());
    foreach($tokens as $token)
    {
      if('text' == $token[0] || 'separator' == $token[0])
      {
        $path = $token[2].$path;
        continue;
      }
      break;
    }
    return $path;
  }

  protected function getPathStart(sfRoute $pattern)
  {
    $path = '';
    foreach($pattern->getTokens() as $token)
    {
      if('text' == $token[0] || 'separator' == $token[0])
      {
        $path .= $token[2];
        continue;
      }
      break;
    }
    return $path;
  }

  protected function getGlobPatterns(sfRoute $route)
  {
    $routeOptions = $route->getOptions();
    $pathBase     = $this->getOption('cache_dir');
    $pathStart    = $this->getPathStart($route);
    $pathEnd      = $this->getPathEnd($route);
    $depth        = substr_count($pathStart.'*'.$pathEnd, '/');
    $paths        = array();
    for($i=$depth; $i<=$routeOptions['max_folder_depth']; $i++)
    {
      $paths[] = $pathBase.$pathStart.str_repeat('*/', $i - $depth).'*'.$pathEnd;;
    }
    return $paths;
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    if(!$pattern instanceof sfRoute)
    {
      return parent::removePattern($pattern);
    }

    $paths = $this->getGlobPatterns($pattern);
    foreach($paths as $path)
    {
      $files = glob($path, GLOB_BRACE);
      foreach($files as $file)
      {
        @unlink($file);
      }
    }
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    return 0;
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    return 0;
  }

  /**
   * Converts a cache key to a full path.
   *
   * @param string $key The cache key
   *
   * @return string The full path to the cache file
   */
  protected function getFilePath($key)
  {
    return $this->getOption('cache_dir').DIRECTORY_SEPARATOR.str_replace(sfCache::SEPARATOR, DIRECTORY_SEPARATOR, $key);
  }

  /**
   * Callback to set a custom cache key
   *
   * This sets the cache key to the same value as the current image url.
   * set in settings.yml / cache_namespace_callable
   *
   * @static
   * @param  sfEvent $event Event object as passed by symfony event system
   *
   * @return void
   */
  static public function setCacheKey($internalUri, $hostName = '', $vary = '', $contextualPrefix = '', $sfViewCacheManager)
  {
    $context = sfContext::getInstance();
    $internalUri = str_replace('sfImageTransformator/index', '@'.$context->getRouting()->getCurrentRouteName(), $internalUri);
    return $context->getController()->genUrl($internalUri, false);
  }
}
