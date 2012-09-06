<?php

namespace BaseX\Tests;

use BaseX\Session\SessionInfo;
use BaseX\PHPUnit\TestCaseSession;


/**
 * Description of SessionInfoTest
 *
 * @author alxarch
 */
class SessionInfoTest extends TestCaseSession
{
  function testConstruct() 
  {
    return new SessionInfo($this->session);
  }
  
  /**
   */
  function testGet() 
  {
    $info = new SessionInfo($this->session);
    
    $data = $this->session->query("db:system()")->execute();
    $xml = simplexml_load_string($data);
    
    foreach ($xml->mainoptions->children() as $opt)
    {
      $this->assertEquals((string) $opt, $info->{$opt->getName()});
    }
    
    $this->assertNull($info->{'option_'.time()});
  }
  
  function testOption()
  {
    $info = new SessionInfo($this->session);
    $data = $this->session->query("db:system()")->execute();
    $xml = simplexml_load_string($data);
    foreach ($xml->options->children() as $opt)
    {
      $this->assertEquals((string) $opt, $info->option($opt->getName()));
    }
    
    $this->assertNull($info->option('option_'.time()));
  }
  
  function testVersion()
  {
    $info = new SessionInfo($this->session);
    $this->assertNotEmpty($info->version());
  }
  
  function testReload()
  {
    $info = new SessionInfo($this->session);
    
    $old = $info->option('serializer');
    
    $this->session->execute('SET SERIALIZER html');
    
    $info->reload();
    $new = $info->option('serializer');
    
    $this->assertNotEquals($new, $old);
  }
}

?>
