<?php

/**
 * WSDL_Gen: A WSDL Generator for PHP5
 *
 * This class generates WSDL from a PHP5 class.
 * @example
 */
class WSDL_Gen {
  const SOAP_XML_SCHEMA_VERSION = 'http://www.w3.org/2001/XMLSchema';
  const SOAP_XML_SCHEMA_INSTANCE = 'http://www.w3.org/2001/XMLSchema-instance';
  const SOAP_SCHEMA_ENCODING = 'http://schemas.xmlsoap.org/soap/encoding/';
  const SOAP_ENVELOP = 'http://schemas.xmlsoap.org/soap/envelope/';
  const SCHEMA_SOAP_HTTP = 'http://schemas.xmlsoap.org/soap/http';
  const SCHEMA_SOAP = 'http://schemas.xmlsoap.org/wsdl/soap/';
  const SCHEMA_WSDL = 'http://schemas.xmlsoap.org/wsdl/';

  static public $baseTypes = array(
    'int'    => array('ns'   => self::SOAP_XML_SCHEMA_VERSION,
                      'name' => 'int'),
    'float'  => array('ns'   => self::SOAP_XML_SCHEMA_VERSION,
                      'name' => 'float'),
    'string' => array('ns'   => self::SOAP_XML_SCHEMA_VERSION,
                      'name' => 'string'));
  public $types;
  public $operations = array();
  public $className;
  public $ns;
  public $endpoint;
  public $complexTypes;
  private $mytypes = array();

  /** The WSDL_Gen constructor
   * @param string $className The class containing the methods to implement
   * @param string $endpoint  The endpoint for the service
   * @param string $ns optional The namespace you want for your service.
   */
  function __construct($className, $endpoint, $ns=false) {
    $this->types = self::$baseTypes;
    $this->className = $className;
    if(!$ns) { $ns = $endpoint; }
    $this->ns = $ns;
    $this->endpoint = $endpoint;
    $this->createPHPTypes();

    $class = new ReflectionClass($className);
    $methods = $class->getMethods();
    $this->discoverOperations($methods);
    $this->discoverTypes();
  }

  protected function discoverOperations($methods) {
    foreach($methods as $method) {
      $this->operations[$method->getName()]['input'] = array();
      $this->operations[$method->getName()]['output'] = array();
      $doc = $method->getDocComment();
      if(preg_match_all('|@param\s+(?:object\s+)?(\w+)\s+\$(\w+)|', $doc, $matches, PREG_SET_ORDER)) {
        foreach($matches as $match) {
          $this->mytypes[$match[1]] = 1;
          $this->operations[$method->getName()]['input'][] = 
                array('name' => $match[2], 'type' => $match[1]);
        }
      }
      if(preg_match('|@return\s+(?:object\s+)?(\w+)|', $doc, $match)) {
        $this->mytypes[$match[1]] = 1;
        $this->operations[$method->getName()]['output'][] = 
              array('name' => 'return', 'type' => $match[1]);
      }
    }
  }
  protected function discoverTypes() {
    foreach(array_keys($this->mytypes) as $type) {
      if(!isset($this->types[$type])) {
        $this->addComplexType($type);
      }
    }
  }
  protected function createPHPTypes() {
    $this->complexTypes['mixed'] = array(
                                     array('name' => 'varString',
                                           'type' => 'string'),
                                     array('name' => 'varInt',
                                           'type' => 'int'),
                                     array('name' => 'varFloat',
                                           'type' => 'float'),
                                     array('name' => 'varArray',
                                           'type' => 'array'));
    $this->types['mixed'] = array('name' => 'mixed', 'ns' => $this->ns);
    $this->types['array'] = array('name' => 'array', 'ns' => $this->ns);
  }
  protected function addComplexType($className) {
    $class = new ReflectionClass($className);
    $this->complexTypes[$className] = array();
    $this->types[$className] = array('name' => $className, 'ns' => $this->ns);

    foreach($class->getProperties() as $prop) {
      $doc = $prop->getDocComment();
      if(preg_match('|@var\s+(?:object\s+)?(\w+)|', $doc, $match)) {
        $type = $match[1];
        $this->complexTypes[$className][] = array('name' => $prop->getName(), 'type' => $type);
        if(!isset($this->types[$type])) {
          $this->addComplexType($type);
        }
      }
    }
  }
  
  protected function addMessages(DomDocument $doc, DomElement $root) {
    foreach(array('input', 'output') as $type) {
      foreach($this->operations as $name => $params) {
        $el = $doc->createElementNS(self::SCHEMA_WSDL, 'message');
        $el->setAttribute("name", "$name".ucfirst($type));
        foreach($params[$type] as $param) {
          $part = $doc->createElementNS(self::SCHEMA_WSDL, 'part');
          $part->setAttribute('name', $param['name']);
          $prefix = $root->lookupPrefix($this->types[$param['type']]['ns']);
          $part->setAttribute('type', "$prefix:".$this->types[$param['type']]['name']);
          $el->appendChild($part);
        } 
        $root->appendChild($el);
      }
    }
  }
  protected function addPortType(DomDocument $doc, DomElement $root) {
    $el = $doc->createElementNS(self::SCHEMA_WSDL, 'portType');
    $el->setAttribute('name', $this->className."PortType");
    foreach($this->operations as $name => $params) {
      $op = $doc->createElementNS(self::SCHEMA_WSDL, 'operation');
      $op->setAttribute('name', $name);
      foreach(array('input', 'output') as $type) {
        $sel = $doc->createElementNS(self::SCHEMA_WSDL, $type);
        $sel->setAttribute('message', 'tns:'. "$name".ucfirst($type));
        $op->appendChild($sel);
      }
      $el->appendChild($op);
    }
    $root->appendChild($el);
  }
  protected function addBinding(DomDocument $doc, DomElement $root) {
    $el = $doc->createElementNS(self::SCHEMA_WSDL, 'binding');
    $el->setAttribute('name', $this->className."Binding");
    $el->setAttribute('type', "tns:{$this->className}PortType");

    $s_binding = $doc->createElementNS(self::SCHEMA_SOAP, 'binding');
    $s_binding->setAttribute('style', 'rpc');
    $s_binding->setAttribute('transport', self::SCHEMA_SOAP_HTTP);
    $el->appendChild($s_binding);

    foreach($this->operations as $name => $params) {
      $op = $doc->createElementNS(self::SCHEMA_WSDL, 'operation');
      $op->setAttribute('name', $name);
      foreach(array('input', 'output') as $type) {
        $sel = $doc->createElementNS(self::SCHEMA_WSDL, $type);
        $s_body = $doc->createElementNS(self::SCHEMA_SOAP, 'body');
        $s_body->setAttribute('use', 'encoded');
        $s_body->setAttribute('encodingStyle', self::SOAP_SCHEMA_ENCODING);
        $sel->appendChild($s_body);
        $op->appendChild($sel);
      }
      $el->appendChild($op);
    }
    $root->appendChild($el);
  }
  protected function addService(DomDocument $doc, DomElement $root) {
    $el = $doc->createElementNS(self::SCHEMA_WSDL, 'service');
    $el->setAttribute('name', $this->className."Service");

    $port = $doc->createElementNS(self::SCHEMA_WSDL, 'port');
    $port->setAttribute('name', $this->className."Port");
    $port->setAttribute('binding', "tns:{$this->className}Binding");

    $addr = $doc->createElementNS(self::SCHEMA_SOAP, 'address');
    $addr->setAttribute('location', $this->endpoint);

    $port->appendChild($addr);
    $el->appendChild($port);
    $root->appendChild($el);
  }
  
  protected function addTypes(DomDocument $doc, DomElement $root) {
    $types = $doc->createElementNS(self::SCHEMA_WSDL, 'types');
    $root->appendChild($types);
    $el = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'schema');
    $types->appendChild($el);

    /* BEGIN: crutch */
    $ct = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'complexType');
    $el->appendChild($ct);
    $ct->setAttribute('name', 'array');
    $cc = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'complexContent');
    $ct->appendChild($cc);
    $restriction = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'restriction');
    $cc->appendChild($restriction);
    $restriction->setAttribute('base', 'soapenc:array');
    $attribute = $doc->createElementNS(self::SOAP_XML_SCHEMA_VERSION, 'attribute');
    $restriction->appendChild($attribute);
    $attribute->setAttribute('ref', 'soapenc:arrayType');
    $attribute->setAttributeNS(self::SCHEMA_WSDL, 'arrayType', 'tns:mixed[]');

    /* END: crutch */
    
    foreach($this->complexTypes as $name => $data) {
      $ct = $doc->createElementNS(self::SCHEMA_WSDL, 'complexType');
      $ct->setAttribute('name', $name);

      $all = $doc->createElementNS(self::SCHEMA_WSDL, 'all');

      foreach($data as $prop) {
        $p = $doc->createElementNS(self::SCHEMA_WSDL, 'element');
        $p->setAttribute('name', $prop['name']);
        $p->setAttribute('name', $prop['name']);
        $prefix = $root->lookupPrefix($this->types[$prop['type']]['ns']);
        $p->setAttribute('type', "$prefix:".$this->types[$prop['type']]['name']);
        $all->appendChild($p);
      }
      $ct->appendChild($all);
      $el->appendChild($ct);
    }
  }
 
  /**
   * Return an XML representation of the WSDL file
   */
  public function toXML() {
    $wsdl = new DomDocument("1.0");
    $root = $wsdl->createElementNS('http://schemas.xmlsoap.org/wsdl/', 'definitions');
    $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd','http://www.w3.org/2001/XMLSchema');
    $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:tns', $this->ns);
    $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soap-env',self::SCHEMA_SOAP);
    $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:wsdl',self::SCHEMA_WSDL);
    $root->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:soapenc',self::SOAP_SCHEMA_ENCODING);
    $root->setAttribute('targetNamespace', $this->ns);
    $this->addTypes($wsdl, $root);
    $this->addMessages($wsdl, $root);
    $this->addPortType($wsdl, $root);
    $this->addBinding($wsdl, $root);
    $this->addService($wsdl, $root);

    $wsdl->appendChild($root);
    $wsdl->formatOutput = true;
    return $wsdl->saveXML();
  }
}

/* vim: set ts=2 sts=2 bs=2 ai expandtab : */
