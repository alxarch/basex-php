<?php
/*
 * Copyright (c) 2012, Sigalas Alexandros
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the <organization> nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 */


namespace BaseX\Rest;

abstract class Operation
{
  const XMLNS = 'http://basex.org/rest';

  const METHODS = '/(xml|xhtml|html|text|html5|json|jsonml|raw)/';
  
  protected $text;
  
  protected $variables = array();
  
  protected $options = array();
  
  protected $parameters = array();
  

  public function __construct($text, $variables=array(), $options=array(), $parameters=array()){
    $this->text = (string) $text;
    $this->bindVariables($variables)
         ->setOptions($options)
         ->setParameters($parameters);
  }
  
  /**
   * Returns the tag to be used for the request body.
   * 
   * @return string $tag
   */
  abstract protected function getTag();

  
  protected function doBuild(){
    $tag = $this->getTag();
    $data = sprintf('<%s xmlns="%s"></%s>', $tag, self::XMLNS, $tag);
    
    $xml = simplexml_load_string($data);

    $xml->addChild('text', $this->text);
    
    foreach ($this->options as $name => $value){
      $opt = $xml->addchild('option');
      $opt->addAttribute('name', $name);        
      $opt->addAttribute('value', $value);        
    }
    foreach ($this->variables as $name => $value){
      $v = $xml->addchild('variable');
      $v->addAttribute('name', $name);        
      $v->addAttribute('value', $value);        
    }

    foreach ($this->parameters as $name => $value){
      $par = $xml->addchild('parameter');
      $par->addAttribute('name', $name);        
      $par->addAttribute('value', $value);        
    }

    return $xml->asXML();
  }

  public function getText(){
    return $this->text;
  }

  public function setText($text){
    $this->text = (string) $text;
    return $this;
  }
  
  public function build()
  {
    return $this->doBuild();
  }

  public function bindVariables($variables)
  {
    $this->variables = array_replace($this->variables, $variables);
    return $this;
  }
  
  public function bindVariable($name, $value)
  {
    $this->variables[$name] = $value;
    return $this;
  }

  public function getVariable($name)
  {
    return isset($this->variables[$name]) ? $this->variables[$name] : null;
  }

  public function getVariables()
  {
    return $this->variables;
  }
  
  /**
   * Set multiple serialization parameters for this operation.
   * 
   * @see http://docs.basex.org/wiki/Serialization
   * 
   * @param array $parameters
   * @return \BaseX\Rest\Operation 
   */
  public function setParameters($parameters)
  {
    $this->parameters = array_replace($this->parameters, $parameters);
    if(isset($parameters['method']))
      $this->setMethod($parameters['method']);
    return $this;
  }

  /**
   * Set serializarion method for this operation.
   * 
   * @see http://docs.basex.org/wiki/Serialization
   * 
   * @param string $method
   * @throws \InvalidArgumentException 
   */
  public function setMethod($method){
    if(!preg_match(self::METHODS, $method))
      throw new \InvalidArgumentException('Invalid serialization method.');
    $this->parameters['method'] = $method;
  }

  /**
   * Gets serialization method for this operation.
   * 
   * @return type 
   */
  public function getMethod(){
    return isset($this->parameters['method']) ? $this->parameters['method'] : 'xml';
  }
  
  
  /**
   * Set a serialization parameter for this operation.
   * 
   * @see http://docs.basex.org/wiki/Serialization
   * 
   * @param string $name
   * @param mized $value
   * @return \BaseX\Rest\Operation 
   */
  public function setParameter($name, $value)
  {
    $this->parameters[$name] = $value;
    return $this;
  }
  
  
  /**
   * Get all serialization parameters for this operation.
   * 
   * @see http://docs.basex.org/wiki/Serialization
   * 
   * @return array
   */
  public function getParameters(){
    return $this->parameters;
  }

  
  /**
   * Get a serialization parameter by name.
   * 
   * @see http://docs.basex.org/wiki/Serialization
   * 
   * @param string $name
   * @return mixed 
   */
  public function getParameter($name)
  {
    return $this->parameters[$name];
  }

  /**
   * Set multiple options for this operation.
   * 
   * @see http://docs.basex.org/wiki/Options
   * 
   * @param array $options
   * @return \BaseX\Rest\Operation 
   */
  public function setOptions($options)
  {
    $this->options = array_replace($this->options, $options);
    return $this;
  }
  
  /**
   * Set an option for this operation.
   * 
   * @see http://docs.basex.org/wiki/Options
   * 
   * @param string $name
   * @param mixed $value
   * 
   * @return \BaseX\Rest\Operation 
   */
  public function setOption($name, $value){
    $this->options[$name] = $value;
    return $this;
  }

  /**
   * Get all options set for this operation.
   * 
   * @see http://docs.basex.org/wiki/Options
   * 
   * @return array
   */
  public function getOptions()
  {
    return $this->options;
  }
  
  /**
   * Get an option by name.
   * 
   * @see http://docs.basex.org/wiki/Options
   * 
   * @param string $name
   * @return mixed 
   */
  public function getOption($name)
  {
    return isset($this->options[$name]) ? $this->options[$name] : null;
  }
  
}
