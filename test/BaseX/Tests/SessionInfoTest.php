<?php

namespace BaseX\Tests;

use BaseX\Session\Info;
use BaseX\TestCaseSession;


/**
 * Description of SessionInfoTest
 *
 * @author alxarch
 */
class SessionInfoTest extends TestCaseSession{
  function testConstruct() 
  {
    return new Info(self::$session);
  }
  
  /**
   */
  function testGet() 
  {
    $info = new Info(self::$session);
    
    $data = self::$session->query("db:system()")->execute();
    $xml = simplexml_load_string($data);
    
    foreach ($xml->mainoptions->children() as $opt)
    {
      $this->assertEquals((string) $opt, $info->{$opt->getName()});
    }
    
    $this->assertNull($info->{'option_'.time()});
  }
  
  function testOption()
  {
    $info = new Info(self::$session);
    $data = self::$session->query("db:system()")->execute();
    $xml = simplexml_load_string($data);
    foreach ($xml->options->children() as $opt)
    {
      $this->assertEquals((string) $opt, $info->option($opt->getName()));
    }
    
    $this->assertNull($info->option('option_'.time()));
  }
  
  function testVersion()
  {
    $info = new Info(self::$session);
    $this->assertNotEmpty($info->version());
  }
  
  function testReload()
  {
    $info = new Info(self::$session);
    
    $old = $info->option('serializer');
    
    self::$session->execute('SET SERIALIZER html');
    
    $info->reload();
    $new = $info->option('serializer');
    
    $this->assertNotEquals($new, $old);
  }
}

?>
