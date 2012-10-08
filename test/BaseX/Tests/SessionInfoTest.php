<?php

namespace BaseX\Tests;

use BaseX\Session\SessionInfo;
use BaseX\PHPUnit\TestCaseSession;

/**
 * Description of SessionInfoTest
 *
 * @author alxarch
 */
class SessionInfoTest extends TestCaseSession {

  /**
   */
  function testGet() {
    $info = new SessionInfo($this->session);

    $data = $this->session->query("db:system()")->execute();
    
    $info->setData($data);
    
    $xml = simplexml_load_string($data);

    foreach ($xml->mainoptions->children() as $opt) {
      $this->assertEquals((string) $opt, $info->{$opt->getName()});
    }

    $this->assertNull($info->{'option_' . time()});
  }

  function testOption() 
  {
    $info = new SessionInfo($this->session);
    $data = $this->session->query("db:system()")->execute();
    $info->setData($data);
    $xml = simplexml_load_string($data);
    foreach ($xml->options->children() as $opt) {
      $this->assertEquals((string) $opt, $info->option($opt->getName()));
    }

    $this->assertNull($info->option('option_' . time()));
  }

  function testVersion() {
    $info = new SessionInfo($this->session);
    $data = $this->session->query("db:system()")->execute();
    $info->setData($data);
    $this->assertNotEmpty($info->version());
  }

}

?>
