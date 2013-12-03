<?php

/**
 * Class Column is a factory class which is used inside the table class 
 * to describe columns of different nature
 *
 * @author Alexander Gilmanov
 *
 * @since May 2012
 */

class Row {
	
	/**
	 * The private array of cells in the current row
	 * @var array
	 */
	private $_cells = Array();

	
	/**
	 * Static ID count, is used to auto-generate IDs if not provided by user
	 */
	private static $id_count = 0;
	
	/**
	 * The amount of columns in the parent table
	 * @var int;
	 */
	private $_columnCount;

	/**
	 * The array of column dataTypes of the parent table.
	 * Count of keys in the array must be equal to $_columnCount.
	 * 
	 * @var array
	 */
	private $_dataTypes = Array();

	/**
	 * The array of keys to access the cells by column key
	 *  
	 */
	private $_keys = Array();

	/**
	 * CSS class of the row
	 * 
	 * @var string
	 */
	private $_classes = Array();
	
	/**
	 * CSS style of the row 
	 */
	private $_style = '';

	/**
	 * Id of the row 
	 */
	private $_id = '';

	/**
	 * Reference to child table, if exists
	 * 
	 * @var array
	 */
	private $_childTable = null;

	/**
	 * Property which determines the child rows render type
	 */
	private $_childRowRender = PHPDataTable::CHILD_ROWS;

	/**
	 * Flag indicating is the row visible
	 * @var bool
	 */
	private $_visible = true;
	
	/**
	 * Link to the parent table
	 */
	private $_parentTable = null;
	
	/**
     * Constructor of the row
     * Accepts following keys in the $params array:
     * 
     * 'dataTypes' (required) - array of the column dataTypes in the table
     * 
     * @param type $params 
     */
    public function __construct( $params = Array( ) ) {
		if(!isset($params['dataTypes'])) { throw new Exception('Data types not provided'); }
		if(!isset($params['parentTable'])) { throw new Exception('Link to parent table not provided'); }

		$this->_dataTypes	= $params['dataTypes'];
		$this->_parentTable = &$params['parentTable'];
	//	$this->_keys	    = $params['dataTypes'];
		$this->_columnCount	= count( $this->_dataTypes );
		$this->_id		= isset($params['id']) ? $params['id'] : 'tbl_'.(PHPDataTable::$id_count-1).'_row_'.self::$id_count++;
    }
    
    /**
     * Checks is the row visible
     * @return bool
     */
    public function isVisible() {
		return $this->_visible;
    }
    
    /**
     * Sets row to be visible
     */
    public function show() {
		$this->_visible = true;
    }
    
    /**
     * Sets row to be invisible
     */
    public function hide() {
		$this->_visible = false;
    }
	
	/**
	 * Sets the child rows to be rendered as a collapsible table
	 */
	public function renderChildAsTable() {
		$this->_childRowRender = PHPDataTable::CHILD_TABLE;
		if($this->hasChildTable()){
			$this->getChildTable()->showHeader();
		}		
	}
	
	/**
	 * Sets the child rows to be rendered as rows
	 */
	public function renderChildAsRows() {
		$this->_childRowRender = PHPDataTable::CHILD_ROWS;
		if($this->hasChildTable()){
			$this->getChildTable()->hideHeader();
		}		
	}
	
    /**
     * Adds a CSS class to the row
     * @param type $class 
     */
    public function addClass( $class ) {
		$this->_classes[] = $class;
    }
    
    /**
     * Returns all names of CSS classes in a string
     * @return string
     */
    public function getClasses() {
		return implode(' ', $this->_classes);
    }
    
    /**
     * Sets the CSS style for the row
     * @var string $style
     */
    public function setStyle( $style ) {
		$this->_style = $style;
    }
    
    /**
     * Returns the CSS style value
     * @return string style
     */
    public function getStyle() {
		return $this->_style;
    }
    
    /**
     * Returns the CSS classes in an array
     */
    public function getClassesArray() {
		return $this->_classes;
    }
    
	/**
	 * Sets the #id to the row
	 * @param string id
	 */
	public function setId($id) {
		$this->_id = $id;
	}
	
	/**
	 * Returns the #id of the row
	 */
	public function getId(){
		return $this->_id;
	}
    
    /**
     * Returns the number of cells
     * @return int
     */
    public function getCellCount() {
		return count($this->_cells);
    }
    
    
    /**
     * Method adds a cell to the row
     * 
     * @param Cell $cell
     * @return boolean Result of adding the cell
     */
    public function addCell( &$cell, $key = null ) {
		if(!($cell instanceof Cell)) throw new Exception('Only Cell object instances are allowed.');
		if( is_null($key) ) {
			$this->_cells[] = &$cell;
		} else {
			$this->_cells[$key] = &$cell;
		}
		return true;
    }
    
    /**
     * Method adds a prepared group of cells to the row
     * 
     * @param array $cells
     * @return boolean|void
     */
    public function addCells( &$cells ) {
		if( !is_array( $cells ) ) return false;
		foreach( $cells as $key=>&$cell ) { 
			$this->addCell( $cell, $key );
		}
    }
    
    /**
     * Method determines if row has child rows
     * @return bool
     */
    public function hasChildTable() {
		return (!is_null($this->_childTable));
    }
    
    /**
     * Returns the child Table
     * @return array Array of child (collapsible) Row objects
     */
    public function getChildTable() {
		return $this->_childTable;
    }
	
	/**
	 * Sets the passed table as child table for the row
	 * @param Table $table
	 * @throws Exception 
	 */
	public function setChildTable($table){
		if(!($table instanceof PHPDataTable)){
			throw new Exception('Only Table class instances accepted!');
		}
		$table->addClass('child_table');
		$table->setTableType($this->_parentTable->getChildTablesType());
		$this->_parentTable->showCollapsible();
		$table->showCollapsible();		
		if($this->_parentTable->getChildRenderType() == PHPDataTable::CHILD_ROWS){
			$table->hideHeader();
			$table->hideFooter();
		}
			
		$this->_childTable = $table;
	}
	
	
    /**
     * Adds a group of cells to the row from prepared values array and prepared dataTypes array
     * 
     * @param array $rowArray
     * @param array $dataTypesArray 
     */    
    public function addCellsFromRowArray( $rowArray ) {
	if( !is_array( $rowArray ) ) { throw new Exception('Only arrays accepted by addCellsFromRowArray method!'); }
	foreach( $rowArray as $key=>$value ) {
	    if( $key != '_child_rows' ) {
			// '_child_rows' - reserved key for child rows
			$cell = new Cell( $this->_dataTypes[$key], $value );
			$this->_cells[$key] = $cell;
		} else {
			// recursively adding child (collapsible) rows
			if( !is_array($value) || empty($value) ) continue;
			$child_table = new PHPDataTable();
			$child_table->buildByArray( $value );
			$this->setChildTable( $child_table );
	    }
	}
	return true;
    }
    
    /**
     * Get a cell reference by index
     * @param mixed $index
     * @return object|boolean The cell instance, or false if requested index is out of bounds
     */
    public function getCell( $index ) {
		//if( ( $index > $this->_columnCount ) || ( $index < 0 ) ) throw new Exception('Cell index out of bounds!');
		if( !array_key_exists($index, $this->_cells) ) throw new Exception('Cell index does not exist!');
		$cell_ref = &$this->_cells[$index];
		return $cell_ref;
    }
    
    /**
     * Returns the cells, contained in the row
     * @return type 
     */
    public function getCells() {
		return $this->_cells;
    }
    
    
}


?>
