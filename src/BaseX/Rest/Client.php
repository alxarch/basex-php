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

use BaseX\Rest\Operation as Op;

use Zend\Http\Client as HttpClient;
use Zend\Uri\Http as HttpUri;


/**
 * Client for BaseX REST API.
 * @see http://docs.basex.org/wiki/REST
 * 
 * @uses Zend\Http\Client
 * 
 */
class Client{

  static public $errorCodes = array(400, 404, 500);
  static public $successCodes = array(200, 201);

  /*
   * @var \Zend\Http\Client
   */
  protected $http;

  /**
   * Constructor.
   * 
   * @param string $uri The uri for the BaseX REST api
   * @param string $user The username.
   * @param string $pass The password.
   */
  public function __construct($uri, $user, $pass){
    $this->http = new HttpClient($uri);
    $this->http->setAuth($user, $pass);
  }

  /**
   * Executes a Query, Run or Command operation.
   * 
   * @param BaseX\Rest\Operation\Base $op The operation to perform.
   * @param string $path The relative path for this operation.
   * @param boolean $raw Whether to convert the output based on $op serialization method.
   * 
   * @return string $response
   */
  public function exec(Operation $op, $path='/', $raw=false){
    $this->setPath($path)
      ->http
      ->setRawBody($op->build())
      ->setHeaders(array('Content-Type' => 'application/xml'))
      ->setMethod('post')
      ->send();

    $response = $this->handleResponse();
    
    if($raw) return $response;
    
    switch($op->getMethod()){
      case 'xml':
        return simplexml_load_string($response);
        break;
      case 'jsonml':
      case 'json':
        return json_decode($response);
        break;
      case 'text':
      default:
        return $response;
        break;
    }
  }

  /**
   * Set the relative path for the next operation.
   * 
   * @param string $path
   * @param array $query
   * 
   * @return BaseX\Rest\Client
   */
  public function setPath($path, $query=array()){
    $uri = $this->http->getUri();
    if(!HttpUri::validatePath($path)){
      throw new \InvalidArgumentException(sprintf('Invalid path "%s"', $path));
    }

    $uri->setPath($uri->getPath().'/'.ltrim($path,'/'));
    $uri->setQuery($query);
    
    $this->http->setUri($uri);
    
    return $this;
  }

  /**
   * Executes a GET API call.
   *  
   * @param $string $path The relative path to use as context for the operation
   * @param array $query    Query arguments for the GET request
   * @return string       The response from the server.   
   */
  public function get($path='/', $query=array())
  {
    $this->setPath($path)
         ->http
         ->setParameterGet($query)
         ->setMethod('get')
         ->send();
    
    return $this->handleResponse();
  }
 
  /**
   * Handles a response from the api.
   * @return string The response text.
   * @throws \Exception If the request failed.
   */
  protected function handleResponse()
  {
    $response = $this->http->getResponse();
    
    if($response->isSuccess()){
      return $response->getBody();
    }
    
    throw new \Exception($response->getContent(), $response->getStatusCode());
  }
  
  /**
   * Adds/updates a document.
   * 
   * @param string $doc A file/directory uri or an xml string. 
   * @param string $path The path at which to store the document. 
   * @return string The server response.
   */
  public function put($doc, $path="/"){
    $this->setPath($path)
         ->http
         ->setRawBody($doc)
         ->setMethod('put')
         ->send();
    
    return $this->handleResponse();
  }
}
