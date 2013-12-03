<?php

/**
 * Class StringColumn is a child column class used
 * to describe columns with string content
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class StringColumn extends Column {
	
    protected $_dataType = 'string';
    protected $_jsDataType = 'string';
    
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->_dataType = 'string';
    }
    
    public function formatHandler( $cell ) {
		if(!is_array($cell->getContent())){
			return '<span>'.$cell->getContent().'</span>';
		}else{
			$value = $cell->getContent();
			return '<span>'.$value['value'].'</span>';
		}
    }    
    
}


?>
