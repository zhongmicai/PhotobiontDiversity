<?php

/**
 * Class FormatnumColumn is a child column class used
 * to describe columns with formatted numeric content
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class FormatnumColumn extends Column {

    protected $_jsDataType = 'formatted-num';
    protected $_dataType = 'float';
    
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->dataType = 'formatted-num';
		$this->_jsFilterType = 'number';
		$this->addClass('numdata');
    }
    
    public function formatHandler( $cell ) {
		return $cell->getContent();
    }
    
}


?>
