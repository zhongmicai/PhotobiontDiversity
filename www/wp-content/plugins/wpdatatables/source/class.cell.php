<?php

/**
 * Class Cell contains methods for working with individual cell
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class Cell {
    
    private $_content;
    private $_dataType;
    private $_classes = array( );
    private $_style = '';
	private $_additionalData = array( );
    
    /**
	 * Creates a new cell. Casts the value with the _castValue() method.
	 * If the provided content is an array, presumes that the real value 
	 * is in the 'value' key, otherwise throws an exception.
	 * All other keys are sent to the additional data array.
	 * 
	 * Additional data array is used by advanced column types.
	 * 
     * @param string $datatype
     * @param mixed $content 
     */
    public function __construct( $dataType = 'string', $content = '' ) {
		$this->_dataType = $dataType;
		if(!is_array($content)){
			$this->_content = $this->_castValue( $content );
		}else{
			if(!isset($content['value'])){
				throw new Exception('Value key not found in the cell content array!');
			}
			$this->_content = $this->_castValue($content['value']);
			unset($content['value']);
			$this->setAdditionalData($content);
		}
    }

	/**
	 * Sets the additional data array for a cell
	 * @param array $dataArray Array of additional data
	 */
	public function setAdditionalData( $dataArray ) {
		if(!is_array( $dataArray ) || empty( $dataArray )) {
			throw new Exception('Additional cell data array must be a non-empty array!');
		}
		$this->_additionalData = $dataArray;
	}
	
	/**
	 * Adds a parameter to the additional data array
	 * @param string $paramName a name of additional parameter
	 * @param mixed $data the value of additional parameter
	 */
	public function addData( $paramName, $data ) {
		if(!isset($paramName)) { throw new Exception('Additional parameter name not set!'); }
		if(!isset($data)) { throw new Exception('Additional parameter value not set!'); }
		$this->_additionalData[$paramName] = $data;
	}
	
	/**
	 * Returns all the additional cell data array 
	 */
	public function getAdditionalData() {
		return $this->_additionalData;
	}
	
	/**
	 * Returns additional cell parameter by key
	 */
	public function getAdditionalParameter( $paramKey ){
		if(!isset($paramKey) || !isset($this->_additionalData[$paramKey])) { return ''; }
		return $this->_additionalData[$paramKey];
	}
	
	/**
	 * Sets additional cell parameter by key
	 */
	public function setAdditionalParameter( $paramKey, $paramValue ){
		$this->_additionalData[$paramKey] = $paramValue;
	}
	
    
    /**
     * Returns the cell content
     */
    public function getContent() {
		return $this->_content;
    }
    
    /**
     * Sets the cell content
     */
    public function setContent( $content ) {
		$this->_content = $this->_castValue( $content );
    }
    
    /**
     * Adds a CSS class to cell
     * @param type $class 
     */
    public function addClass( $class ) {
	$this->_classes[] = $class;
    }
    
    /**
     * Returns list of CSS classes as a string 
     */
    public function getClasses() {
		return implode(' ', $this->_classes);
    }
    
    /**
     * Returns the array of classes
     */
    public function getClassesArray() {
		return $this->_classes;
    }
	
	/**
	 * Returns the current Style value 
	 */
	public function getStyle() {
		return $this->_style;
	}
	
	/**
	 * Sets the Style value 
	 * @param string style
	 */
	public function setStyle( $style ) {
		$this->_style = $style;
	}
    
    /**
     * Used to cast input value to the provided type
     * @param mixed $value 
     */
    private function _castValue( $value ) {
		switch( $this->_dataType ) {
			case 'int' :
			return (int)$value;
			case 'float' :
			return (float)$value;
			case 'date'  :
			return strtotime($value);
			case 'string' : 
			case 'email' : 
			default:
			return (string)$value;
		}
    }
    
}


?>
