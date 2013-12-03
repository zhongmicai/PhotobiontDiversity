<?php

/**
 * Class StringColumn is a child column class used
 * to describe columns with string content
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class EmailColumn extends Column {
	
    protected $_jsDataType = 'html';
    protected $_dataType = 'string';
        
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->_dataType = 'email';
    }
    
    public function formatHandler( $cell ) {
    	$content = $cell->getContent();
    	if(strpos($content,'||')!==false){
    		$link = '';
    		list($link,$content) = explode('||',$content);
			return "<a href='mailto:{$link}'>{$content}</a>";
    	}else{
			return "<a href='mailto:{$content}'>{$content}</a>";
    	}
    }    
    
}


?>
