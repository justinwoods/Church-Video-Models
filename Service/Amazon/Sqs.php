<?php

class JW_Service_Amazon_Sqs extends Zend_Service_Amazon_Sqs
{

    private $_queues = null;
    private $_num_messages = 10;
    private $_sqs_message = null;
    
    public function exists($name)
    {
        return ($this->getUrl($name) === false) ? false : true;
    }
    
    public function safeCreate($name)
    {
        if(!$this->exists($name)) {
            return $this->create($name);
        }

        return $this->getUrl($name);
    }
    
    public function getUrl($name)
    {
        $queues = $this->getQueues();
        
        foreach($queues as $url) {
            if($this->getName($url) == $name) {
                return $url;
            }
        }
        return false;
    }
    
    public function getName($url)
    {
        return basename($url);
    }
    
    public function getQueues()
    {
        if(null === $this->_queues) {
            $this->_queues = parent::getQueues();
        }
        return $this->_queues;
    }
    
    public function setGetNumMessages($num)
    {
        if(!is_int($num)) {
            return;
        }
        
        $this->_num_messages = $num;
    }
    
    public function messagesPerQueue()
    {
        $queues = $this->getQueues();

        foreach($queues as $queue) {
            $list[] = array(
                'name'=>$this->getName($queue), 
                'number' => $this->count($queue)
            );
        }
                                            
        return $list;                                    
    }
    
    public function encodeMessage($data)
    {
        $message = $this->_getSqsMessage();
        $message->addData($data);
        return $message->getEncoded();
    }
    
    public function decodeMessage($data)
    {
        $message = $this->_getSqsMessage();
        $message->addRawData($data);
        return $message->toString();
    }
    
    public function __call($name, $arguments)
    {
        if(!$this->exists($name)) {
            return false;
        }

        $url = $this->getUrl($name);
        
        # If a message is passed, place it in the queue
        if(count($arguments) > 0) {
            $message = $this->encodeMessage($arguments[0]);
            return $this->send($url, $message);
        }
        
        # If no message is passed, READ from the queue
        $messages = $this->receive($url, $this->_num_messages);
        if(!is_array($messages)) {
            return false;
        }
        
        foreach($messages as $key => $message) {
            $messages[$key]['body'] = $this->decodeMessage($message['body']);
        }
        return $messages;
    }
    
    private function _getSqsMessage()
    {
        if(null === $this->_sqs_message) {
            $this->_sqs_message = new JW_Service_Amazon_Sqs_Message;
        }
        return $this->_sqs_message;
    }
}