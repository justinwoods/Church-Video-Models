<?php

class JW_Service_Amazon_Sqs_Message extends JW_Model_Rfc822
{

    public function addRawData($raw_data)
    {
        if(false === ($data = base64_decode($raw_data, true))) {
            # $raw_data is not base64 encoded
            $this->_addRawData($raw_data);
        }
        else {
            # $raw_data is base64 encoded
            $this->_addRawData($data);
        }
    }

    public function getEncoded()
    {
        return base64_encode($this->_formatOutput());
    }
    

}