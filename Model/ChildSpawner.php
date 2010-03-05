<?php

class JW_Model_ChildSpawner
{

    private $_script	 = null;
    private $_controller = null;
    private $_action	 = null;

    public function __construct()
    {
        $this->_script = basename($_SERVER['argv'][0]);
    }

    public function run($command)
    {
        $pointer = popen($command, 'r');
        pclose($pointer);
    }
    
    public function start()
    {
        $command = $this->_getCommand();
        $this->run($command);
    }
    
    public function getController()
    {
        return $this->_controller;
    }

    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    public function getAction()
    {
        return $this->_action;
    }

    public function setAction($action)
    {
        $this->_action = $action;
    }
    
    private function _getCommand()
    {
        $command = APPLICATION_ROOT . '/' . $this->_script . ' ';

        if(!empty($this->_controller)) {
            $command .= "-c {$this->_controller}";
        }

        if(!empty($this->_action)) {
            $command .= "-a {$this->_action}";
        }
        
        return $command . ' > /dev/null &';
    }
    
    

}