<?php

namespace BaseX;

use BaseX\Session;
use BaseX\Document;
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
   * Retrieves contents of a raw resource from the database.
   * 
   * @see http://docs.basex.org/wiki/Commands#RETRIEVE
   * 
   * @param string $path
   * @return string
   */
  public function retrieve($path)
  {
    
//    $script = <<<XML
//<commands>
//    <set option='serializer'>raw</set>
//    <open name='$db'/>
//    <retrieve path='$path'/>
//    <set option='serializer'/>
//</commands>
//XML;
    $script = <<<SCRIPT
      SET SERIALIZER raw;
      OPEN $this->name;
      RETRIEVE "$path";
      SET SERIALIZER
SCRIPT;
    
    return $this->session->script($script);
    
    // Using xquery to limit socket traffic.
//    $xql =<<<XQL
//      declare option output:method "raw";
//      db:retrieve("$db", "$path") 
//XQL;
//    return $this->session->query($xql)->execute();
    
//    $this->session->execute("SET SERIALIZER method=raw");
//    $this->open();
//    $retrieve = sprintf('RETRIEVE "%s"', B::escape($path));
//    $data = $this->session->execute($retrieve);
//    $this->session->execute('SET SERIALIZER');
//    return $data;
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
   * @return \Basex\Document
   * @throws \InvalidArgumentException 
   */
  public function document($path, $class=null)
  {
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
   * @return \SimpleXmlElement
   */
  public function getResources($path = null)
  {
    $filter = $this->getResourceFilter();
    $db = $this->getName();
    $xql = "<index>{ db:list-details('$db', '$path')$filter }</index>";
    
    $index = $this->session->query($xql)->execute();

    return simplexml_load_string($index)->resource;
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
    
    $this->session->script("SET PARSER; SET PARSEROPT; SET HTMLOPT; SET CREATEFILTER");
  }

  protected function open()
  {
    $open = sprintf('OPEN %s', $this->getName());
    $this->session->execute($open);
  }
}