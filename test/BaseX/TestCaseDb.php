<?php

namespace BaseX;

use BaseX\TestCaseSession;

use BaseX\Database;

class TestCaseDb extends TestCaseSession
{
  /**
   *
   * @var BaseX\Database
   */
  protected $db = null;
  
  protected function setUp()
  {
    parent::setUp();
    $this->db = new Database($this->session, $this->dbname);
  }
  
  protected function tearDown()
  {
    $this->session->execute("DROP DB $this->dbname");
    parent::tearDown();
  }
  
  protected function doc($path)
  {
    $this->session->execute("SET SERIALIZER method=xml");
    return $this->session->execute("XQUERY db:open('$this->dbname','$path')");
  }
  
  protected function raw($path)
  {
    $this->session->execute("OPEN $this->dbname");
    $this->session->execute("SET SERIALIZER method=raw");
    $raw = $this->session->execute('RETRIEVE "'.$path.'"');
    return $raw;
  }

  protected function ls()
  {
    return $this->session->execute('LIST '.$this->dbname);
  }
}