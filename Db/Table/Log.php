<?php

class JW_Db_Table_Log extends Zend_Db_Table_Abstract
{

    const TABLE_COLUMN_NAME_EXTRAVALUES = 'extra_values';

    protected $_schema	= 'monitor';
    protected $_name	= 'log';
    protected $_id	= 'id';
    
    public function insert($data)
    {
        $extra  = $this->_getExtraValues($data);
        $string = $this->_encodeExtraValues($extra);
        
        $data = $this->_getMainValues($data);
        $data[self::TABLE_COLUMN_NAME_EXTRAVALUES] = $string;
    
        return parent::insert($data);
    }
    
    private function _getExtraValues($data)
    {
        $table_metadata = $this->info();
        return array_diff_key($data, array_flip($table_metadata['cols']));
    }

    private function _getMainValues($data)
    {
        $table_metadata = $this->info();
        return array_intersect_key($data, array_flip($table_metadata['cols']));
    }
    
    private function _encodeExtraValues($data)
    {
        $message = new JW_Model_Rfc822;
        $message->addData($data);
        return $message->toString();
    }

}