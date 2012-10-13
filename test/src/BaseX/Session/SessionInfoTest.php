<?php

namespace BaseX\Session;

use BaseX\Session\SessionInfo;
use BaseX\PHPUnit\TestCaseSession;

/**
 * Description of SessionInfoTest
 *
 * @author alxarch
 */
class SessionInfoTest extends TestCaseSession {

  function setUp() {
    parent::setUp();

    $this->data = $this->session->query("db:system()")->execute();
    $this->xml = simplexml_load_string($this->data);
    
  }
  
  /**
   */
  function testGet() {
    
    $info = new SessionInfo($this->session);
    $info->unserialize($this->data);
    foreach ($this->xml->mainoptions->children() as $opt) {
      $this->assertEquals((string) $opt, $info->{$opt->getName()});
    }

    $this->assertNull($info->{'option_' . time()});
  }

  function testOption() 
  {
    $info = new SessionInfo($this->session);
    $info->unserialize($this->data);
    foreach ($this->xml->options->children() as $opt) {
      $this->assertEquals((string) $opt, $info->option($opt->getName()));
    }

    $this->assertNull($info->option('option_' . time()));
  }

  function testVersion() {
    $info = new SessionInfo($this->session);
    $info->unserialize($this->data);
    $this->assertNotEmpty($info->version());
  }

}
