<?php

class sfRouteFilterIterator extends FilterIterator
{
  private $basedir = null;
  private $route = null;

  public function __construct(Iterator $iterator, sfRoute $route)
  {
    parent::__construct($iterator);
    $this->setRoute($route);
  }

  public function accept()
  {
    if($this->route)
    {
      $fileinfo = $this->getInnerIterator()->current();
      if($fileinfo->isFile() && '.' != strpos($fileinfo->getFilename(), 0, 1))
      {
        return (false !== $this->route->matchesUrl(
          str_replace(array(DIRECTORY_SEPARATOR, $this->basedir), array('/', ''), $fileinfo->getPathname()),
          array('method' => 'get')
        ));
      }
      return false;
    }

    return true;
  }

  protected function setRoute(sfRoute $route)
  {
    $this->route = $route;
    $this->basedir = str_replace(DIRECTORY_SEPARATOR, '/', sfConfig::get('sf_web_dir'));
  }
}
