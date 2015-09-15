<?php

namespace PF;

class ContentTypes extends \Slim\Middleware {
  protected $contentTypes;

  public function __construct($settings = array())
  {
    $defaults = array(
      'application/json' => array($this, 'parseJson'),
      'application/xml' => array($this, 'parseXml'),
    );

    $this->contentTypes = array_merge($defaults, $settings);
  }

  public function call()
  {
    $mediaType = $this->app->request->getMediaType();

    if ($mediaType) {
      $env = $this->app->environment;
      $env['slim.input_original'] = $env['slim.input'];
      $env['slim.input'] = $this->parse($env['slim.input'], $mediaType);
    }

    $this->next->call();
  }

  protected function parse($input, $contentType)
  {
    if (isset($this->contentTypes[$contentType]) && is_callable($this->contentTypes[$contentType])) {
      $result = call_user_func($this->contentTypes[$contentType], $input);
      if ($result) {
        return $result;
      }
    }
    return $input;
  }

  protected function parseJson($input)
  {
    if (function_exists('json_decode')) {
      $result = json_decode($input, true);
      if ($result) {
        return $result;
      }
    }
  }

  protected function parseXml($input)
  {
    if (class_exists('SimpleXMLElement')) {
      try {
        $backup = libxml_disable_entity_loader(true);
        $result = new \SimpleXMLElement($input);
        libxml_disable_entity_loader($backup);
        return $result;
      } catch (\Exception $e) {
        // Do nothing
      }
    }
    return $input;
  }
}
