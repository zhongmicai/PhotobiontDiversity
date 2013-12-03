<?php
/**
 * Class Column is a factory class which is used inside the table class 
 * to describe columns of different nature
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class Column {
    
    private $_header;
    private $_visible = true;
    private $_style;
    private $_width;
    private $_sorting;
    private $_searching = true;
    protected $_classes;
    protected $_formatCallback = null;
    protected $_cellTemplate;
    protected $_jsDefinitionTemplate;
    protected $_dataType;
    protected $_jsDataType = 'html';
    protected $_jsFilterType = 'text';

    /**
     * Constructor in the factory class is used to set the common properties in
     * all the child classes (header, visibility, width etc)
     * 
     * @param type $params 
     */
    public function __construct( $params = array () ) {
		$this->_header	= isset($params['header']) ? $params['header'] : '';
		$this->_visible	= isset($params['visible']) ? $params['visible'] : true;
		$this->_width	= isset($params['width']) ? $params['width'] : '';
		$this->_classes	= isset($params['classes']) ? $params['classes'] : array();
		$this->_sorting	= isset($params['sorting']) ? $params['sorting'] : true;
		$this->_sorting	= isset($params['searching']) ? $params['searching'] : true;
    }
    
    /**
     * The factory method is used to return a new instance of one of the column
     * child classes
     * 
     * @param string $dataType the dataType of the generated column. May be one 
     * of these: 'int', 'float', 'date', 'email', 'string'.
     * 
     * @param array $params Array of parameters which will be sent to the 
     * constructor of the child class
     * 
     * @return \IntColumn|\FloatColumn|\DateColumn|\EmailColumn|\StringColumn 
     */
    public static function factory( $dataType = 'string', $params = array( ) ) {
		if(!$dataType){ $dataType = 'string'; }
		// if the column datatype is unknown, throw exception
		if( !in_array( $dataType, PHPDataTable::$allowedColumnTypes )) {
			throw new Exception('Unknown column datatype: "'.$dataType.'" !');
		}
		// creating a new column
		$columnTypeName = ucfirst($dataType).'Column';
                require_once('class.column.'.strtolower($dataType).'.php');
		return new $columnTypeName( $params );
		
    }
    
    /**
     * Returns the header value of the column
     * @return string Header (title) of the column
     */
    public function getHeader() {
		return $this->_header;
    }
    
    /**
     * Sets the title of the column
     * @param type $header 
     */
    public function setHeader( $header ) {
		$this->_header = $header;
    }
    
    /**
     * Returns the current data type 
     */
    public function getDataType(){
        return $this->_dataType;
    }
    
    /**
     * Checks is the column visible
     * @return bool
     */
    public function isVisible() {
		return $this->_visible;
    }
    
    /**
     * Sets column to be visible
     */
    public function show() {
		$this->_visible = true;
    }
    
    /**
     * Sets column to be invisible
     */
    public function hide() {
		$this->_visible = false;
    }
    
    /**
     * Returns the currently set CSS classes of the column as an array
     * @return array
     */
    public function getClassesArray() {
		return $this->_classes;
    }
    
    /**
     * Adds a CSS class to the column
     */
    public function addClass( $class ) {
		$this->_classes[] = $class;
    }    
    
    /**
     * Returns CSS classes as a sting
     */
    public function getClasses( ) {
		return implode(' ', $this->_classes);
    }
    
    /**
     * Returns the current column width
     */
    public function getWidth() {
		if($this->_width){
			return $this->_width;
		}else{
			return 'auto';
		}
    }
    
    /**
     * Sets the column width 
     */
    public function setWidth( $width ) {
		$this->_width = $width;
    }
    
    /**
     * Get the defined CSS style
     * @return type 
     */
    public function getStyle() {
		return $this->_style;
    }
    
    /**
     * Sets the CSS style.
     * Doesn't apply for hidden columns 
     */
    public function setStyle( $style ) {
		$this->_style = $style;
    }
    
    /**
     * Returns if the sorting is allowed for the column 
     */
    public function sortingEnabled() {
		return $this->_sorting;
    }
    
    /**
     * Enable sorting 
     */
    public function enableSorting() {
		$this->_sorting = true;
    }
    
    /**
     * Disable sorting 
     */
    public function disableSorting() {
		$this->_sorting = false;
    }
	
	/**
	 * Enables searching (filtering)
	 */
	public function enableSearching() {
		$this->_searching = true;
	}
	
	/**
	 * Disable searching (filtering)
	 */
	public function disableSearching() {
		$this->_searching = false;
	}
	
	/**
	 * Returns if searching/filtering is enabled.
	 * @return bool
	 */
	public function searchingEnabled() {
		return $this->_searching;
	}
    
    /**
     * Calls the provided callback for the cell formatting, or 
     * the existing in the class format method, if callback is not provided.
     * 
     * @access private
     * @param mixed $value The value to be formatted
     * @return mixed The formatted value
     */
    private function _formatVal( $value ) {
		if( is_null($this->_formatCallback) ) return $this->formatHandler( $value );
		else return call_user_func ( $this->_formatCallback, $value );
    }
    
    /**
     * Returns the formatted value (content) of a cell object
     * @param Cell $cell The cell object from which to extract content
     */
    public function getFormattedCellVal( $cell ) {
		if( !isset($cell) || !( $cell instanceof Cell ) ) throw new Exception('Valid Cell object not provided!');
		return $this->_formatVal( $cell );
    }
    
    /**
     * The general function for formatting the values in the column.
     * Should be overriden in child classes.
     * Does nothing in the general class (just returns the value);
     * 
     * @param mixed $cell Cell, which value to format
     * @return mixed $value Formatted value
     */
    public function formatHandler( $cell ) {
		if(!is_array($cell->getContent())){
			return $cell->getContent();
		}else{
			$value = $cell->getContent();
			return $value['value'];
		}
    }
    
	/**
	 * Returns an StdClass object with all parameters required for the column 
	 * definition in the JavaScript.
	 * Is invoked in the child classes to use overriden parameters.
	 * 
	 * @return \StdClass definition
	 */
	public function getJsDefinition() {
		$def = new StdClass();
		$def->sType			= $this->_jsDataType;
		$def->sClass		= $this->getClasses();
		$def->bVisible		= $this->isVisible();
		$def->bSortable		= $this->sortingEnabled();
		$def->bSearchable	= $this->searchingEnabled();
		if($this->_width != ''){
			$def->sWidth		= $this->_width;
		}
		return $def;
	}
	
	/**
	 * Returns an StdClass object with the filtertype for column
	 */
	 public function getFilterType() {
	 	$ftype = new StdClass();
	 	$ftype->type = $this->_jsFilterType;
	 	return $ftype;
	 }
	 
	 /**
	  * Set a filter type for a column
	  * Accepts parameters: 'text', 'number', 'select', 'null', 'number-range', 'date-range'
	  * @param string Filter type
	  */
	  public function setFilterType( $filterType ) {
	  	if(!in_array( $filterType, array('text', 'number', 'select', 'null', 'number-range', 'date-range') )){
	  		throw new Exception('Unknown column type!');
	  	}
	  	$this->_jsFilterType = $filterType;
	  }
}

?>
