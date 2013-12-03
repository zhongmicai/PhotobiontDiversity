<?php

/**
 * Class LinkColumn is a child column class used
 * to describe columns which have links in the cells
 *
 * @author Alexander Gilmanov
 *
 * @since July 2012
 */

class LinkColumn extends Column {
	
    protected $_jsDataType = 'string';
    protected $_dataType = 'string';
    
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->_dataType = 'link';
    }
    
    public function formatHandler( $cell ) {
    	$content = $cell->getContent();
    	if(strpos($content,'||')!==false){
    		$link = '';
    		list($link,$content) = explode('||',$content);
			return "<a href='{$link}'>{$content}</a>";
    	}else{
			return "<a href='{$content}'>{$content}</a>";
    	}
    }    
    
}


?>
