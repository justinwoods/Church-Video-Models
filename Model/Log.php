<?php

class JW_Model_Log
{

    private $_ec2	= null;
    private $_sqs	= null;
    private $_config	= null;
    
    public function __construct($sqs)
    {
        $this->setAmazonSqs($sqs);
    }
    
    public function processQueue()
    {
        $log = new JW_Db_Table_Log;
        $messages = $this->getAmazonSqs()->log();

        foreach($messages as $message) {
            try {
                $r = $log->insert($message->toArray());
            }
            catch (Zend_Db_Statement_Exception $e){
                # If exception is  integrity constraint violation when
                # inserting the same message again, ignore it. Otherwise, rethrow.
                if(23000 != $e->getCode()) {
                    throw new Zend_Db_Statement_Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
            $this->getAmazonSqs()->deleteMessage($this->getAmazonSqs()->getUrl('log'), $message->handle);
        }
    }
    
    public function setAmazonSqs($sqs)
    {
        $this->_sqs = $sqs;
    }
    
    public function getAmazonSqs()
    {
        return $this->_sqs;
    }

}