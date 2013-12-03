<?php

/**
 * Class IntColumn is a child column class used
 * to describe columns with float numeric content
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class DateColumn extends Column {
	
    protected $_jsDataType = 'date';
    protected $_dataType = 'date';
    
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->_dataType = 'date';
    }
    
    public function formatHandler( $cell ) {
		if(!is_array($cell->getContent())){
			return date(get_option('wdtDateFormat'), $cell->getContent());
		}else{
			$value = $cell->getContent();
			return date(get_option('wdtDateFormat'), $value['value']);
		}
    }        
    
}


?>
