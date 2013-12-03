<?php

/**
 * Class IntColumn is a child column class used
 * to describe columns with float numeric content
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class FloatColumn extends Column {

    protected $_jsDataType = 'formatted-num';
    protected $_dataType = 'float';
    
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->dataType = 'float';
		$this->_jsFilterType = 'number';
		$this->addClass('numdata');
    }
    
    public function formatHandler( $cell ) {
		return number_format( $cell->getContent(), 2 );
    }
    
}


?>
