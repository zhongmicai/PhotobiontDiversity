<?php

/**
 * Class LinkColumn is a child column class used
 * to describe columns which have links in the cells
 *
 * @author Alexander Gilmanov
 *
 * @since July 2012
 */

class ImageColumn extends Column {
	
    protected $_jsDataType = 'string';
    protected $_dataType = 'string';
    
    public function __construct( $params = array () ) {
		parent::__construct( $params );
		$this->_dataType = 'icon';
    }
    
    public function formatHandler( $cell ) {
    	$content = $cell->getContent();
    	if(strpos($content,'||')!==false){
    		$image = ''; $link = '';
    		list($image,$link) = explode('||',$content);
    		return "<a href='{$link}' target='_blank'><img src='{$image}' /></a>";
    	}else{
			return "<img src='{$content}' />";
    	}
    }    
    
}


?>
