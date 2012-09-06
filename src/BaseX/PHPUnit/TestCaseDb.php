<?php
/**
 * @package BaseX
 * @subpackage Tests
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */
namespace BaseX\PHPUnit;

use BaseX\PHPUnit\TestCaseSession;

use BaseX\Database;

/**
 * TestCase for tests that require a BaseX database.
 * 
 * It builds/destroys a BaseX database before/after each test.
 * 
 * @package BaseX
 * @subpackage Tests
 */
class TestCaseDb extends TestCaseSession
{
  /**
   *
   * @var string
   */
  protected $dbname;
  
  /**
   *
   * @var BaseX\Database
   */
  protected $db = null;
  
  public function setUp()
  {
    parent::setUp();
    $this->dbname = 'db_' . time();
    $this->db = new Database($this->session, $this->dbname);
  }
  
  /**
   * Get document contents.
   *
   * @param string $path
   * @return string 
   */
  protected function doc($path)
  {
    return $this->session->query("db:open('$this->dbname','$path')")->execute();
  }
  
  /**
   * Get raw document contents.
   * 
   * @param string $path
   * @return string 
   */
  protected function raw($path)
  {
    $this->session->execute("OPEN $this->dbname");
    $this->session->execute("SET SERIALIZER method=raw");
    $raw = $this->session->execute("RETRIEVE \"$path\"");
    $this->session->execute("SET SERIALIZER");
    return $raw;
  }

  /**
   * list all database resources.
   * 
   * @return string
   */
  protected function ls()
  {
    return $this->session->query("db:list('$this->dbname')")->execute();
  }
  
  public function tearDown()
  {
    // Truncate db.
    $this->session->execute("CLOSE");
    $this->session->execute("DROP DB $this->dbname");
  }
}
