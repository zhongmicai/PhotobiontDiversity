<?php
/**
 * Class PDTTpl is an extremely lightweight templater used to render tables
 * for the PHPDataTables module.
 *
 * @author cjbug@ya.ru
 * 
 * @since September 2012
 */
class PDTTpl{
    
    private $data;
    private $body;
    private $js;
    private $css;
    
    function setTemplate($b)    { $this->body                   = $b;  }  
    function addCss($c)         { $this->data['_css'][]         = $c;  }
    function addJs($j)          { $this->data['_js'][]          = $j;  }    
    function addBread($n,$l)    { $this->breadcrumbs[$n]        = $l;  }

    function addData($key, $val){
        $this->data[$key] = $val;    
    }

    function addDataRef($key, $val){
        $this->data[$key] = $val;    
    }
    
    function showData(){
    	if(!empty($this->data)){
	        foreach ($this->data as $key=>$value) {
	            $$key=$value;   
	        }
	        unset($this->data);
	    	}
        if(!empty($_css)){
            foreach($_css as $css_file){
                echo '<link rel="stylesheet" href="'.$css_file.'" type="text/css" media="screen, projection" />'."\n";
            }
            unset($_css);
        }
        if(!empty($_js)){
            foreach($_js as $js_file){
                echo '<script type="text/javascript" src="'.$js_file.'" type="text/css"></script>'."\n";
            }
            unset($_js);
        }
        include(PDT_TEMPLATE_PATH.$this->body);
    }
    
    function returnData(){
        ob_start();
        $this->showData();
        $ret_val = ob_get_contents();
        ob_end_clean();
        return $ret_val;
    }
    
}
?>
