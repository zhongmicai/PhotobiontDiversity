<?php

/**
 * Class IntColumn is a child column class used
 * to describe columns with integer content
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class IntColumn extends Column {
    
    protected $_dataType = 'int';
    protected $_jsDataType = 'numeric';
    
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->_dataType = 'int';
		$this->_jsFiltertype = 'number';
		$this->addClass('numdata');
    }
    
}


?>
