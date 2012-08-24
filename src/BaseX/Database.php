<?php

namespace BaseX;

use BaseX\Session;
use BaseX\Document;
use BaseX\Exception;

class Database
{
    
  /**
   *
   * @var \BaseX\Session
   */
  protected $session;
  
  /**
   *
   * @var string
   */
  protected $name;
  
  public function __construct(Session $session, $name)
  {
    $this->session = $session;
    $this->name = $name;
    
    // Create the database if it does not exist.
    $com = sprintf('CHECK %s', $name);
    $this->session->execute($com);
  }
  
  public function getName()
  {
    return $this->name;
  }
  
  public function add($path, $input)
  {
    $path = implode('/', array($this->getName(), $path));
    return $this->session->add($path, $input);
  }
  
  public function store($path, $content)
  {
    $path = implode('/', array($this->getName(), $path));
    $this->session->store($path, $content);
  }
  
  public function retrieve($path)
  {
    return $this->execute('RETRIEVE %s', $path);
  }
  
  public function execute($com)
  {
    $count = func_num_args();
    
    $args = func_get_args();
    
    $com = $args[0];
    
    if($count > 1)
    {
      $com = call_user_func_array('sprintf', $args);
    }
    
    $com = sprintf('OPEN %s; %s;', $this->getName(), $com);
    
    return $this->session->execute($com);
  }
  
  public function document($path, $class=null)
  {
    if(null !== $class && !is_subclass_of($class, 'Document'))
    {
      throw new \InvalidArgumentException('Invalid class for document.');
    }
    
    return new $class($this, $path);
  }
  
  public function delete($path)
  {
    $this->execute('DELETE %s', $path);
  }
  
  public function rename($old, $new)
  {
    $this->execute('RENAME %s %s', $old, $new);
  }
  
  
  public function index($path = null)
  {
    $xq = <<<XQUERY
      decalre variable \$db external;
      declare variable \$path external := "";
      db:list-details(\$db, \$path)
XQUERY;
    
    return $this->session
      ->query($xq)
      ->bind('db', $this->getName(), 'xs:string')
      ->bind('path', $path, 'xs:string')
      ->execute();
  }
  
}