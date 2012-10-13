<?php
/**
 * @package BaseX 
 * 
 * @copyright Copyright (c) 2012, Alexandors Sigalas
 * @author Alexandros Sigalas <alxarch@gmail.com>
 * @license BSD License
 */

namespace BaseX\Database;

use BaseX\Database;
use BaseX\Error\ImportError;
use BaseX\Error;
use BaseX\Helpers as B;
use Symfony\Component\Finder\Finder;

/**
 * Description of Importer
 *
 * @author alxarch
 */
abstract class Importer 
{
  /**
   *
   * @var \BaseX\Database
   */
  protected $db;
  
  abstract public function getDefaultCreateFilter();

  abstract public function getParser();
  
  abstract public function getParserOptions();

  public function __construct(Database $db) {
    $this->db = $db;
   
  }
  
  protected function startImport($options, $createfilter)
  {
    if(null === $createfilter)
    {
      $createfilter = $this->getDefaultCreateFilter();
    }
    
    $parser = $this->getParser();
    
    $opts = B::options($this->getParserOptions() + $options);
    $parseropt = ('html' === $parser) ? 'HTMLOPT' : 'PARSEROPT';
    
    $session =  $this->db->getSession();
    $restore = $session->getInfo();
    
    $session->setOption('parser', $parser)
            ->setOption($parseropt, $opts)
            ->setOption('createfilter', $createfilter);
    
    return $restore;
  }
  
  protected function endImport(SessionInfo $restore)
  {
     $this->db->getSession()
        ->setOption('parser', $restore)
        ->setOption('parseropt', $restore)
        ->setOption('parser', $restore)
        ->setOption('htmlopt', $restore)
        ->setOption('createfilter', $restore);
  }
  
  public function importDir($dir, $path, $options=array(), $createfilter=null)
  {
    if(null === $createfilter)
    {
      $createfilter = $this->getDefaultCreateFilter();
    }
    
    $finder = Finder::create()->files()->name($createfilter)->in($dir);
    
    $this->beginImport($options, $createfilter);
    
    foreach ($finder as $file)
    {
      $input = fopen($file->getRealpath(), 'r');
      $dest = B::path($path, $file->getRelativePathname());
      $this->add($dest, $input);
    }
    
    $this->endimport();
  }
  
  public function importMultiple($imports, $options=array(), $createfilter=null)
  {
    
    $this->beginImport($options, $createfilter);
    
    foreach ($imports as $path => $input)
    {
      $this->add($path, $input);
    }
    
    $this->endimport();
  }

  protected function add($path, $input)
  {
    try
    {
      $this->db->replace($path, $input);
    }
    catch (Error $e)
    {
      throw new ImportError($e->getMessage());
    }
  }

  public function import($path, $input, $options=array(), $createfilter=null)
  {
    $this->beginImport($options, $createfilter);
    
    $this->add($path, $input);
    
    $this->endimport();
    
  }
  
}

