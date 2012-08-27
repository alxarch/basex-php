<?php

namespace BaseX;

use BaseX\Session;
use BaseX\Document;
use BaseX\Resource;
use BaseX\Resource\Info;
use BaseX\Exception;
use BaseX\Helpers as B;

/**
 * BaseX Session Wrapper that operates within a database.
 *  
 */
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
  
  /**
   * Constructor.
   * 
   * If the database does not exist (and the session user has the required 
   * privileges) it will be created.
   * 
   * @param Session $session a BaseX\Session to use
   * @param string $name database name
   */
  public function __construct(Session $session, $name)
  {
    $this->session = $session;
    $this->name = $name;
    
    // Creates the database if it does not exist.
    $check = sprintf('CHECK "%s"', B::escape($name));
    $this->session->execute($check);
  }
  
  /**
   * The name of the database.
   * 
   * @return string
   */
  public function getName()
  {
    return $this->name;
  }
  
  /**
   * Adds a document to the database.
   * 
   * @see http://docs.basex.org/wiki/Commands#ADD
   *
   * @param string $path
   * @param string $input
   */
  public function add($path, $input)
  {
    $this->open();
    $this->session->add($path, $input);
  }
  
  /**
   * Replaces a document.
   * 
   * @see http://docs.basex.org/wiki/Commands#REPLACE
   * 
   * @param string $path
   * @param string $input
   */
  public function replace($path, $input)
  {
    $this->open();
    $this->session->replace($path, $input);
  }
  
  /**
   * Stores a non-xml document to the database at the specified path.
   * 
   * @see http://docs.basex.org/wiki/Commands#STORE
   * 
   * @param string $path
   * @param string $input
   */
  public function store($path, $input)
  {
    $this->open();
    $this->session->store($path, $input);
  }
  
  /**
   * Executes a command after opening the database.
   * 
   * @see http://docs.basex.org/wiki/Commands
   * 
   * @param string $command
   * @return string 
   */
  public function execute($command)
  {
    $this->open();
    return $this->session->execute($command);
  }
  
  /**
   * Fetches a resource as a BaseX\Document.
   * 
   * @param string          $path
   * @param string|object   $class A BaseX\Document subclass to use.
   * @return \Basex\Resource or null if file not found
   * @throws \InvalidArgumentException 
   */
  public function document($path, $class=null)
  {
    if(!$this->exists($path))
      return null;
    
    if(null === $class)
    {
      $class = 'BaseX\Document';
    }
    else if(!is_subclass_of($class, 'BaseX\Document'))
    {
      throw new \InvalidArgumentException('Invalid class for document.');
    }
    
    return new $class($this, $path);
  }
  
  /**
   * Deletes a document.
   * 
   * @see http://docs.basex.org/wiki/Commands#DELETE
   * 
   * @param string $path 
   */
  public function delete($path)
  {
    $path = B::escape($path);
    $command = sprintf('DELETE "%s"', $path);
    $this->execute($command);
  }
  
  /**
   * Renames a document.
   * 
   * @see http://docs.basex.org/wiki/Commands#RENAME
   * 
   * @param string $path 
   */
  public function rename($old, $new)
  {
    $old = B::escape($old);
    $new = B::escape($new);
    $command = sprintf('RENAME "%s" "%s"', $old, $new);
    $this->execute($command);
  }
    
  /**
   * Lists all database resources.
   * 
   * @param string $path 
   * @return array 
   */
  public function getResources($path = null)
  {
    $filter = $this->getResourceFilter();
    $db = $this->getName();
    $xql = "<index>{ db:list-details('$db', '$path')$filter }</index>";
    
    $data = $this->session->query($xql)->execute();
    $resources = array();
    foreach (simplexml_load_string($data)->resource as $resource)
    {
      $resources[] = new Info($resource);
    }
    
    return $resources;
  }
  
  /**
   * XPath expression to limit index results.
   * 
   * Used by getResources.
   * 
   * @return string 
   */
  protected function getResourceFilter()
  {
    return "";
  }
    
  /**
   * Adds a document using the xml parser.
   * 
   * @see http://docs.basex.org/wiki/Parsers#XML_Parser
   * 
   * @param type $path
   * @param type $input
   * @param string $filter Filter added files wildcard.
   * 
   */
  public function addXML($path, $input, $filter = "*.xml")
  {
    $this->doAdd($path, $input, 'xml', array(), $filter);
  }
  
  /**
   * 
   * Adds a document using the html parser.
   * 
   * @see http://docs.basex.org/wiki/Parsers#HTML_Parser
   * @see http://home.ccil.org/~cowan/XML/tagsoup/#program
   * @see http://docs.basex.org/wiki/Options#CREATEFILTER
   * 
   * @param type $path
   * @param type $input
   * @param array $options Options to pass to TagSoup.
   * @param string $filter Filter added files wildcard.
   * 
   */
  public function addHTML($path, $input, $options=array(), $filter = '*.html')
  {
    $options = $options + array(
      'method' => 'xml', 
    );
    
    $this->doAdd($path, $input, 'html', $options, $filter);
    
  }
  
  /**
   * 
   * Adds a document using the JSON parser.
   * 
   * @see http://docs.basex.org/wiki/Parsers#HTML_Parser
   * 
   * @see http://docs.basex.org/wiki/Options#CREATEFILTER
   * 
   * @param type $path
   * @param type $input
   * @param array $options Options to pass to JSON parser
   * @param string $filter Filter added files wildcard.
   * 
   */
  public function addJSON($path, $input, $options=array(), $filter = '*.json')
  {
    $options = $options + array(
      'encoding' => 'utf-8', 
      'jsonml'   => false
    );
 
    $this->doAdd($path, $input, 'json', $options, $filter);
  }
  
  /**
   * 
   * Adds a document using the CSV parser.
   * 
   * @see http://docs.basex.org/wiki/Parsers#CSV_Parser
   * 
   * @see http://docs.basex.org/wiki/Options#CREATEFILTER
   * 
   * @param type $path
   * @param type $input
   * @param array $options Options to pass to CSV parser
   * @param string $filter Filter added files wildcard.
   * 
   */
  public function addCSV($path, $input, $options=array(), $filter = '*.csv')
  {
    $options = $options + array(
      'encoding'  => 'utf-8', 
      'separator' => 'comma', 
      'format'    => 'simple',
      'header'    => true
    );
   
    $this->doAdd($path, $input, 'csv', $options, $filter);
  }

  /**
   * 
   * Adds a document using the Text parser.
   * 
   * @see http://docs.basex.org/wiki/Parsers#Text_Parser
   * 
   * @see http://docs.basex.org/wiki/Options#CREATEFILTER
   * 
   * @param type $path
   * @param type $input
   * @param array $options Options to pass to Text parser
   * @param string $filter Filter added files wildcard.
   * 
   */
  public function addText($path, $input, $options=array(), $filter = '*')
  {
    $options = $options + array(
      'encoding' => 'utf-8', 
      'lines'    => true
    );
    
    $this->doAdd($path, $input, 'text', $options, $filter);
  }
  
  protected function doAdd($path, $input, $parser, $options, $filter)
  {
    $options = B::options($options);
    $parseropt = ('html' === $parser) ? 'HTMLOPT' : 'PARSEROPT';
    $db = $this->getName();
    $s = "SET PARSER $parser; SET $parseropt $options; SET CREATEFILTER $filter; OPEN $db";
    $this->session->script($s);
    
    $add = is_array($path) ? $path : array($path => $input);
    
    foreach ($add as $path => $input)
    {
      $this->session->add($path, $input);
    }
    $this->session->script("SET PARSER xml; SET PARSEROPT; SET HTMLOPT; SET CREATEFILTER");
  }

  protected function open()
  {
    $open = sprintf('OPEN %s', $this->getName());
    $this->session->execute($open);
  }
  
  /**
   * Fetches contents of a document at specified path.
   * 
   * @param type $path
   * @return type 
   */
  public function fetch($path, $raw=false)
  {
    $db =  $this->getName();
    
    if($raw)
    {
      $script = "SET SERIALIZER raw; OPEN $db; RETRIEVE \"$path\"; SET SERIALIZER";
      return $this->session->script($script);
    }
    
    return $this->session->query("db:open('$db', '$path')")->execute();
  }
  
  /**
   * Checks to see if $path exists.
   * @param type $path 
   */
  protected function exists($path)
  {
    $db = $this->getName();
    $xq = "count(db:list('$db', '$path')) > 0";
    return 'true' === $this->session->query($xq)->execute();
  }
  
  /**
   * Retrieves contents of a database filtered by an XPath expression.
   * 
   * @param string $xpath An XPath expression to apply to the contents.
   * @param string $path An path to limit scope of contents.
   * @return string $result
   */
  public function xpath($xpath, $path=null)
  {
    if(null === $path)
      $xq = sprintf("db:open('%s')%s", $this->getName(), $xpath);
    else
      $xq = sprintf("db:open('%s', '%s')%s", $this->getName(), $path, $xpath);
    
    return $this->getSession()->query($xq)->execute();
  }
  
  /**
   *
   * @return \BaseX\Session
   */
  public function getSession()
  {
    return $this->session;
  }
}