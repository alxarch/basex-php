<?php

namespace BaseX\Tests;

use BaseX\Session\Info;
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
    return new Info($this->session);
  }
  
  /**
   */
  function testGet() 
  {
    $info = new Info($this->session);
    
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
    $info = new Info($this->session);
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
    $info = new Info($this->session);
    $this->assertNotEmpty($info->version());
  }
  
  function testReload()
  {
    $info = new Info($this->session);
    
    $old = $info->option('serializer');
    
    $this->session->execute('SET SERIALIZER html');
    
    $info->reload();
    $new = $info->option('serializer');
    
    $this->assertNotEquals($new, $old);
  }
}

?>
