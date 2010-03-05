<?php

class JW_Controller_Request_Cli extends Zend_Controller_Request_Abstract
{

    protected $_getopt = null;

    public function __construct(Zend_Console_Getopt $getopt)
    {
        $this->_getopt = $getopt;
        $getopt->parse();
        if ($getopt->{$this->getModuleKey()}) {
            $this->setModuleName($getopt->{$this->getModuleKey()});
        }
        if ($getopt->{$this->getControllerKey()}) {
            $this->setControllerName($getopt->{$this->getControllerKey()});
        }
        if ($getopt->{$this->getActionKey()}) {
            $this->setActionName($getopt->{$this->getActionKey()});
        }
    }
    
    public function getParams()
    {
        $params = array(
            'module' => $this->getModuleName(),
            'controller' => $this->getControllerName(),
            'action' => $this->getActionName()
        );
        
        return $params;
    }

    public function getCliOptions()
    {
        return $this->_getopt;
    }

}
