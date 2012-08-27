<?php

namespace BaseX;

use BaseX\TestCaseSession;

use BaseX\Database;

class TestCaseDb extends TestCaseSession
{
  /**
   *
   * @var string
   */
  static protected $dbname;
  
  /**
   *
   * @var BaseX\Database
   */
  static protected $db = null;
  
  static public function setUpBeforeClass()
  {
    parent::setUpBeforeClass();
    self::$dbname = 'test_db_' . time();
    self::$db = new Database(self::$session, self::$dbname);
  }
  
  protected function tearDown()
  {
    // Truncate db.
    self::$session->execute("DROP DB ".self::$dbname);
    self::$session->execute("CREATE DB ".self::$dbname);
  }
  
  /**
   * Get document contents.
   *
   * @param string $path
   * @return string 
   */
  static protected function doc($path)
  {
    return self::$session->execute("XQUERY db:open('".self::$dbname."','$path')");
  }
  
  /**
   * Get raw document contents.
   * 
   * @param string $path
   * @return string 
   */
  static protected function raw($path)
  {
    self::$session->execute("OPEN ".self::$dbname);
    self::$session->execute("SET SERIALIZER method=raw");
    $raw = self::$session->execute('RETRIEVE "'.$path.'"');
    self::$session->execute("SET SERIALIZER");
    return $raw;
  }

  /**
   * list all database resources.
   * 
   * @return string
   */
  static protected function ls()
  {
    return self::$session->execute("LIST ".self::$dbname);
  }
  
  static public function tearDownAfterClass()
  {
    self::$session->execute("DROP DB ".self::$dbname);
    parent::tearDownAfterClass();
  }
}
