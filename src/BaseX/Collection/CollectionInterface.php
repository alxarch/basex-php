<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
namespace BaseX\Collection;

use BaseX\Resource\ResourceInterface;

/**
 * Description of CollectionInterface
 *
 * @author alxarch
 */
interface CollectionInterface extends ResourceInterface
{
  public function listContents();
  public function getResources();
}
