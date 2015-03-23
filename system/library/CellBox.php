<?php

if (!defined('BASE_PATH'))
    exit('<H2 style="margin-top:200px;text-align:center;">Your request was forbidden!</H2>');

/********************************************************************************************
 * CellBox类
 * @copyright   Copyright(c) 2015
 * @author      iProg
 * @version     1.0
 ********************************************************************************************/

class CellBox {

	protected $_fields = array();
	
	function __destruct()
	{
		/*if (count($this->_fields) > 0)
			unset($this->_fields);*/
	}


	public function __set($name, $value) 
	{
		if ($name == '_fields'){
			$this->_fields = $value;
			return;
		}
		$temp =$value;
		if (is_string($temp)){
			$temp = is_null($value) ? null : trim($value);
		}
		
		$name = strtoupper($name);
	    	$this->_fields[$name] = $temp;
    	}


	public function __get($name) 
	{
		if ($name == '_fields') return null;
			$name = strtoupper($name);
		if (array_key_exists($name, $this->_fields))
            		return $this->_fields[$name];
        	return null;
    	}


	public function __isset($name) 
	{
		$name = strtoupper($name);
        	return isset($this->_fields[$name]);
    	}


	public function __unset($name) 
	{
		$name = strtoupper($name);
        	unset($this->_fields[$name]);
        }


	public function getFieldsMap(){
	    	return $this->_fields;
	}


    	public function cloneFields(& $fieldsMap){
    		unset($this->_fields);
    		$fieldsMap = array_change_key_case($fieldsMap,CASE_UPPER);
    		$field_Names = array_keys($fieldsMap);
    		$field_Values = array_values($fieldsMap);
    		$this->_fields = array_combine($field_Names,$field_Values);
    		return true;
    	}
}
?>
