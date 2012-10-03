<?php
/**
 * @package BaseX
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX;

use BaseX\Session;
use BaseX\Collection;
use BaseX\Resource\Raw;
use BaseX\Resource\ResourceInfo;
use BaseX\Resource\Document;
use BaseX\Query\QueryBuilder;
use BaseX\Error;
use BaseX\Helpers as B;

/**
 * BaseX Database object.
 * 
 * @package BaseX
 * 
 * @todo Isolate only database-specific functionality in this class, 
 * move everything else (add, delete, rename etc) to Collection
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
   * @param \BaseX\Session $session a BaseX\Session to use
   * @param string $name database name
   */
  public function __construct(Session $session, $name)
  {
    if(!preg_match('/^[\-_a-zA-Z0-9]{1,128}$/', $name))
      throw new \InvalidArgumentException('Invalid database name.');
    
    $this->session = $session;
    $this->name = $name;
    
    // Creates the database if it does not exist.
    $this->session->execute("CHECK $name");
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
   * @link http://docs.basex.org/wiki/Commands#ADD
   *
   * @param string $path
   * @param string|resource $input
   * 
   * @return BaseX\Resource\Document The created document
   */
  public function add($path, $input)
  {
    $this->open()->getSession()->add($path, $input);
    return new Document($this->getSession(), $this->getName(), $path);
  }
  
  /**
   * Replaces a resource.
   * 
   * @link http://docs.basex.org/wiki/Commands#REPLACE
   * 
   * @param string $path
   * @param string|resource $input
   * @return BaseX\Resource The replaced resource
   */
  public function replace($path, $input)
  {
    $this->open()->getSession()->replace($path, $input);
    $info = array_shift(ResourceInfo::get($this->getSession(), $this->getName(), $path));
    if($info->isRaw())
    {
      return new Raw($this->getSession(), $this->getName(), $path, $info);
    }
    else
    {
      return new Document($this->getSession(), $this->getName(), $path, $info);
    }
  }
  
  /**
   * Stores a non-xml document to the database at the specified path.
   * 
   * @link http://docs.basex.org/wiki/Commands#STORE
   * 
   * @param string $path
   * @param string|resource $input
   * 
   * @return BaseX\Resource\Raw the created resource
   */
  public function store($path, $input)
  {
    $this->open()->getSession()->store($path, $input);
    return new Raw($this->getSession(), $this->getName(), $path);
  }
  
  /**
   * Executes a command after opening the database.
   * 
   * @link http://docs.basex.org/wiki/Commands
   * 
   * @param string $command
   * @return string 
   */
  public function execute($command)
  {
    return $this->open()->getSession()->execute($command);
  }
  
  /**
   * Deletes a document.
   * 
   * @link http://docs.basex.org/wiki/Commands#DELETE
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
   * @link http://docs.basex.org/wiki/Commands#RENAME
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
    $col = new Collection($this->getSession(), $this->getName(), $path);
    return $col->getResources();
  }
    
  /**
   * Adds a document using the xml parser.
   * 
   * @link http://docs.basex.org/wiki/Parsers#XML_Parser
   * 
   * @param string $path
   * @param mixed $input
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
   * @link http://docs.basex.org/wiki/Parsers#HTML_Parser
   * @link http://home.ccil.org/~cowan/XML/tagsoup/#program
   * @link http://docs.basex.org/wiki/Options#CREATEFILTER
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
   * @link http://docs.basex.org/wiki/Parsers#HTML_Parser
   * 
   * @link http://docs.basex.org/wiki/Options#CREATEFILTER
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
   * @link http://docs.basex.org/wiki/Parsers#CSV_Parser
   * 
   * @link http://docs.basex.org/wiki/Options#CREATEFILTER
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
   * @link http://docs.basex.org/wiki/Parsers#Text_Parser
   * 
   * @link http://docs.basex.org/wiki/Options#CREATEFILTER
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
    
    $restore = $this->getSession()->getInfo();
    
    $this->getSession()
        ->setOption('parser', $parser)
        ->setOption($parseropt, $options)
        ->setOption('createfilter', $filter)
        ->execute('open '.$this->getName());
    
    $add = is_array($path) ? $path : array($path => $input);
    
    foreach ($add as $path => $input)
    {
      $this->getSession()->add($path, $input);
    }
    
    $this->getSession()
        ->setOption('parser', $restore)
        ->setOption('parseropt', $restore)
        ->setOption('parser', $restore)
        ->setOption('htmlopt', $restore)
        ->setOption('createfilter', $restore);

  }

  protected function open()
  {
    $this->session->execute('OPEN '.$this->getName());
    return $this;
  }
  
  /**
   * Fetches contents of a resource at specified path.
   * 
   * @param string $path
   * @param boolean $raw
   * @return string 
   */
  public function fetch($path, $raw=false)
  {
    $db =  $this->getName();
    
    $q = QueryBuilder::begin();
    
    if($raw)
    {
      $q->setBody("db:retrieve('$db', '$path')")
        ->setParameter('method', 'raw');
    }
    else
    {
      $q->setBody("db:open('$db', '$path')")
        ->setParameter('omit-xml-declaration', false);
    }
    
    return $q->getQuery($this->getSession())->execute();
  }
  
  /**
   * Checks to see if $path exists.
   * @param string $path 
   */
  public function exists($path)
  {
    $xql = sprintf("db:exists('%s', '%s')", $this->getName(), $path);
    return 'true' === $this->session->query($xql)->execute();
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
  
  /**
   * Lists database resources/collections at $path.
   * 
   * all subcollections are returned as <collection/>
   * @param string $path 
   * @return \SimpleXMLElement
   * 
   * @throws \BaseX\Error
   */
  public function getContents($path=null)
  {
    $path = (string) $path;
    $path = trim($path, '/');
    $db = $this->getName();
    //Frakking XQuery starts counting at 1 (+1 to trim leading '/')
    $start = '' === $path ? 1 : strlen($path) + 2; 
    $xql = <<<XQL
<contents>
{
for \$r in db:list-details('$db', '$path')
  let \$p := substring(\$r/string(), $start)
  let \$parts := tokenize(\$p, '/')
  let \$name := \$parts[1]
  let \$count := count(\$parts)
  let \$time :=  xs:dateTime(\$r/@modified-date/string())
  group by \$name
  order by -sum(\$count), \$name
  return if(\$count > 1) 
    then <collection modified-date="{max(\$time)}">{\$name}</collection>
    else \$r
}
</contents>
XQL;
    
    $data = $this->getSession()->query($xql)->execute();
    $xml = simplexml_load_string($data);
    if(false === $xml)
      throw new Error('Failed to get contents.');
    return $xml;
  }
  
  public function __toString()
  {
    return $this->getName();
  }
  
  /**
   * @return \BaseX\Collection
   */
  public function getCollection($path='')
  {
    return new Collection($this->getSession(), $this->getName(), $path);
  }
}