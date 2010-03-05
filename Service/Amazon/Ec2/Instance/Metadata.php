<?php

class JW_Service_Amazon_Ec2_Instance_Metadata
{

    private $_http = null;
    
    const BASEPATH = 'http://169.254.169.254/2009-04-04';
    const METADATA = 'meta-data';
    const USERDATA = 'user-data';
    
    private $_valid = null;
    
    public function __construct()
    {
        $this->_http = $this->getHttpClient();
        
        # Dashes are not allowed in PHP variable names. Use underscores when using __get
        $this->_valid = array(
            'ami-id', 'ami-launch-index', 'ami-manifest-path', 'hostname',
            'instance-action', 'instance-id', 'instance-type', 'kernel-id',
            'local-hostname', 'local-ipv4', 'public-hostname', 'public-ipv4',
            'ramdisk-id', 'reservation-id', 'security-groups', self::USERDATA
        );
    }
    
    public function __get($name)
    {
        $name = str_replace('_', '-', $name);
        if(!in_array($name, $this->_valid)) {
            throw new Exception("JW_Service_Amazon_Ec2_Instance_Metadata: {$name} is not a valid option.");
        }
        return $this->get($name);
    }
    
    public function get($name)
    {
        $uri = $this->getUri($name);
        $response = $this->_http->setUri($uri)->request();
        
        if($response->getStatus() == 404) {
            return null;
        }
        
        return $response->getBody();
    }
    
    public function getAll()
    {
        $fields = $this->getValid();
        foreach($fields as $field) {
            $data[$field] = $this->get($field);
        }
        return $data;
    }
    
    public function getUri($name)
    {
        if($name == 'user-data') {
            return self::BASEPATH . '/' . self::USERDATA;
        }
        
        return self::BASEPATH . '/' . self::METADATA . '/' . $name;
    }
    
    public function getValid()
    {
        return $this->_valid;
    }
    
    public function getHttpClient()
    {
        if(null == $this->_http) {
            $config = array(
                'maxredirects' => 0
            );
            $this->_http = new Zend_Http_Client();
            $this->_http->setConfig($config);
        }
        return $this->_http;
    }

}