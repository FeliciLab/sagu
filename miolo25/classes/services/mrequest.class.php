<?php
/**
 * Brief Class Description.
 * Complete Class Description.
 */
class MRequest extends MService
{
    private $parameters;
    private $http;
    private $server;
    
    public function __construct($name = '')
    {
        parent::__construct();
        $this->setParametersArray();
        $this->setServerArray();
        $this->setHTTPArray();
    }

    private function setParametersArray()
    {
        $this->parameters = $_REQUEST;
    }

    private function setHTTPArray()
    {
        $this->http = $_SERVER;
    }

    private function setServerArray()
    {
        $this->server = $_SERVER;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getParameter($name)
    {
        return $this->parameters[$name];
    }

    public function getParametersNames()
    {
        return array_keys($this->parameters);
    }

    public function getParameterValues($name)
    {
        return $this->parameters[$name];
    }

    public function getServer($name)
    {
        return $this->server[$name];
    }

    public function getRequestURI()
    {
        return $this->getServer('REQUEST_URI');
    }

    public function getQueryString()
    {
        return $this->getServer('QUERY_STRING');
    }

}
?>