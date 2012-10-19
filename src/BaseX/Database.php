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
use BaseX\Resource\ResourceResults;
use BaseX\Query\QueryResultsInterface;
use BaseX\Query\Results\UnserializableResults;

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
   * @var \BaseX\Query\Result\MapperInterface Mapper to use for resource 
   * retrieval
   */
  protected $mapper;

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
    $this->session = $session;
    $this->setName($name);
  }

  public function setName($name)
  {
    if (!preg_match('/^[\-_a-zA-Z0-9]{1,128}$/', $name))
      throw new \InvalidArgumentException('Invalid database name.');

    $this->name = $name;
    return $this;
  }

  /**
   * Creates the database if it does not exist.
   * 
   * @return \BaseX\Database
   */
  public function create()
  {
    $name = $this->getName();
    $this->getSession()->execute("CHECK $name");
    return $this;
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
   * @return \BaseX\Database
   */
  public function add($path, $input)
  {
    $this->open();
    $this->getSession()->add($path, $input);
    return $this;
  }

  /**
   * Replaces a resource.
   * 
   * @link http://docs.basex.org/wiki/Commands#REPLACE
   * 
   * @param string $path
   * @param string|resource $input
   * 
   * @return \BaseX\Database
   */
  public function replace($path, $input)
  {
    $this->open();
    $this->getSession()->replace($path, $input);
    return $this;
  }

  /**
   * Stores a non-xml document to the database at the specified path.
   * 
   * @link http://docs.basex.org/wiki/Commands#STORE
   * 
   * @param string $path
   * @param string|resource $input
   * 
   * @return BaseX\Database
   */
  public function store($path, $input)
  {
    $this->open()->getSession()->store($path, $input);
    return $this;
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
   * 
   * @return BaseX\Database
   */
  public function delete($path)
  {
    $this->execute("DELETE \"$path\"");
    return $this;
  }

  /**
   * Renames a document.
   * 
   * @link http://docs.basex.org/wiki/Commands#RENAME
   * 
   * @param string $old 
   * @param string $new 
   * 
   * @return BaseX\Database
   */
  public function rename($old, $new)
  {
    $this->execute("RENAME \"$old\" \"$new\"");
    return $this;
  }

  /**
   * 
   * @param string $path
   * @param \BaseX\Query\Result\MapperInterface $mapper
   * @return mixed
   */
  public function getResource($path)
  {
    return $this->getResources($path)->getSingle();
  }

  /**
   * Lists all database resources.
   * 
   * @param string $path 
   * @return \BaseX\Resource\ResourceResults
   */
  public function getResources($path = null)
  {
    return $this->getSession()
        ->query("db:list-details('$this', '$path')")
        ->getResults(new ResourceResults($this));
  }

  protected function open()
  {
    $this->getSession()->execute("OPEN $this");
    return $this;
  }

  /**
   * Checks to see if $path exists.
   * 
   * @param string $path 
   */
  public function exists($path)
  {
    $xql = "count(db:list('$this', '$path')) ne 0";
    return 'true' === $this->getSession()->query($xql)->execute();
  }

  /**
   * Retrieves contents of a database filtered by an XPath expression.
   * 
   * @param string $xpath An XPath expression to apply to the contents.
   * @param string $path An path to limit scope of contents.
   * @param \BaseX\Query\Result\Results $results A mapper to use for
   * the results.
   * @return BaseX\Query\Results\QueryResultsInterface|array
   * 
   */
  public function xpath($xpath, $path = null,
                        QueryResultsInterface $results = null)
  {
    if (null === $path)
    {
      $xq = sprintf("db:open('%s')%s", $this->getName(), $xpath);
    }
    else
    {
      $xq = sprintf("db:open('%s', '%s')%s", $this->getName(), $path, $xpath);
    }

    return $this->getSession()->query($xq)->getResults($results);
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
   * 
   * @return string
   */
  public function __toString()
  {
    return $this->getName();
  }

  /**
   * Copies contents form source path to destination path.
   * 
   * @param string $src
   * @param string $dest
   * @return \BaseX\Database
   */
  public function copy($src, $dest)
  {
    $xql = <<<XQL
      for \$resource in db:list-details('$this', '$src')
        let \$src := \$resource/text()
        let \$dest := replace(\$src, '^$src', '$dest')
        return 
        if(\$resource/@raw = 'true') 
        then 
          db:store('$this', \$dest, db:retrieve('$this', \$src))
        else
          db:replace('$this', \$dest, db:open('$this', \$src))
XQL;

    $this->getSession()->query($xql)->execute();

    return $this;
  }

  public function getLatestBackup()
  {
    return $this->getBackups()->getFirst();
  }

  public function getBackups()
  {
    $xql = <<<XQL
      for \$b in db:backups('$this') 
        order by \$b descending
        return \$b
XQL;
    return $this->getSession()
        ->query($xql)
        ->getResults(new UnserializableResults('BaseX\Database\Backup'));
  }

  /**
   * Create a new backup for this Database.
   * @return \BaseX\Database
   */
  public function backup()
  {
    $this->getSession()->execute("CREATE BACKUP $this");
    return $this;
  }

}