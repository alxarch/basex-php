<?php

/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Resource;

use BaseX\Helpers as B;

/**
 * Description of Tree
 *
 * @author alxarch
 */
class Tree implements \ArrayAccess
{

  protected $root;
  protected $cache;
  protected $children;
  protected $maxdepth;

  /**
   *
   * @var callable
   */
  protected $loader;

  /**
   *
   * @var array
   */
  protected $items;

  /**
   * @var callable
   */
  protected $converter;

  /**
   * 
   * @param type $root
   * @param callable $items A c
   * @param type $maxdepth
   */
  public function __construct($root)
  {
    $this->root = $root;
  }

  public function setItemLoader($loader)
  {
    if (!is_callable($loader))
    {
      throw new \InvalidArgumentException('Non callable loader.');

      if (is_array($loader))
      {
        $callback = new \ReflectionMethod($loader[0], $loader[1]);
      }
      else
      {
        $callback = new \ReflectionFunction($loader);
      }

      if ($callback->getNumberOfRequiredParameters() > 1)
      {
        throw new \InvalidArgumentException('Invalid callable, too many required params.');
      }
    }

    $this->loader = $loader;

    return $this;
  }

  public function setTreeConverter($converter)
  {
    if (!is_callable($converter))
    {
      throw new \InvalidArgumentException('Non callable converter.');

      if (is_array($converter))
      {
        $callback = new \ReflectionMethod($converter[0], $converter[1]);
      }
      else
      {
        $callback = new \ReflectionFunction($converter);
      }

      if ($callback->getNumberOfRequiredParameters() > 1)
      {
        throw new \InvalidArgumentException('Invalid callable, too many required params.');
      }
    }

    $this->converter = $converter;
    return $this;
  }

  public function getRoot()
  {
    return $this->root;
  }

  public function setMaxdepth($depth)
  {
    $this->maxdepth = (int) $depth;
    return $this;
  }

  public function getMaxdepth($depth)
  {
    return $this->maxdepth = (int) $depth;
  }

  /**
   * Extracts tree structure from a list of resources.
   * 
   * @param array[string]Object $items path => item
   * @param $depth int
   */
  protected function build($items, $depth = -1)
  {
    $class = get_called_class();
    if (null === $this->cache)
      $this->cache = array();
    if (null === $this->children)
      $this->children = array();

    $rootpath = $this->root === '' ? '' : sprintf('%s/', $this->root);
    $rootlen = strlen($rootpath);

    foreach ($items as $path => $item)
    {
      if ($rootlen === 0 || 0 === strpos($path, $rootpath))
      {
        $relpath = substr($path, $rootlen);
      }
      else
      {
        continue;
      }

      $pos = strpos($relpath, '/');

      if ($pos === false)
      {
        $this->children[$relpath] = $item;
        $this->cache["/$path"] = $item;
        continue;
      }

      $name = substr($relpath, 0, $pos);

      $childroot = B::path($this->root, $name);

      if (!isset($this->cache["/$childroot"]))
      {
        $child = new $class($childroot);

        $this->children[$name] = $child;
        $this->cache["/$childroot"] = $child;

        if ($depth !== 0)
        {
          $child->build($items, $depth - 1);
          $this->cache = array_replace($this->cache, $child->cache);
        }
      }
    }

    $this->cache['/'] = $this;
  }

  public function getChildren()
  {
    return $this->children;
  }

  protected function getItems($path = '')
  {
    if (null !== $this->loader)
    {
      return call_user_func($this->loader, $path);
    }

    if (null !== $this->items)
    {
      if ($path)
      {
        if (isset($this->items[$path]))
        {
          return $this->items[$path];
        }
      }
      else
      {
        return $this->items;
      }
    }

    return null;
  }

  public function rebuild($path = '')
  {
    if ($path === '/')
      $path = '';

    if ($this->maxdepth >= 0)
      $depth = $this->maxdepth - count(explode('/', $path)) + 1;
    else
      $depth = -1;

    if ($this->maxdepth < 0 || $depth >= 0)
    {
      $items = $this->getItems($path);
      if (null === $items)
      {
        throw new \RuntimeException('Unable to refresh resources.');
      }

      $this->build(call_user_func($this->loader, $path), $depth);
    }
    else
    {
      throw new \LogicException('Requested path is too deep for this tree.');
    }

    return $this;
  }

  public function offsetExists($path)
  {
    if (isset($this->cache["/$path"]))
    {
      return true;
    }
    elseif (null === $this->loader)
    {
      return false;
    }
    else
    {
      $this->rebuild($path);

      return isset($this->cache["/$path"]);
    }
  }

  public function convert($item)
  {
    if ($item instanceof Tree && null !== $this->converter)
    {
      return call_user_func($this->converter, $item);
    }
    return $item;
  }

  public function offsetGet($path)
  {
    $path = trim($path, '/');
    if ($this->offsetExists($path))
    {
      $item = $this->cache["/$path"];
      return $this->convert($item);
    }

    return null;
  }

  public function offsetSet($offset, $value)
  {
    throw new \RuntimeException('Not implemented.');
  }

  public function offsetUnset($path)
  {
    if (null === $this->cache)
      return;
    unset($this->cache["/$path"]);

    $check = "/$path/";

    foreach ($this->cache as $key => $path)
    {
      if (strpos($key, $check) === 0)
      {
        unset($this->cache[$key]);
      }
    }
  }

  /**
   * 
   * @param type $path
   * @return \BaseX\Resource\Tree
   */
  public static function make($path)
  {
    $class = get_called_class();
    return new $class($path);
  }

}
