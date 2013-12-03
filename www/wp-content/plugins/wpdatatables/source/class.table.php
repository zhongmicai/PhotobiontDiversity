<?php
/**
 * Class Table contains methods and properties for generating tables
 * plain HTML or interactive with jQuery dataTables plugin 
 *
 * @author cjbug@ya.ru
 * 
 * @since September 2012
 */

class PHPDataTable {

    /************************************
     *            CONSTANTS             *
     ************************************/

	/** Table types **/

    /**
     * Plain table type represents standard HTML table
     */
    const PLAIN_TABLE	= 0;

    /**
     * Js datatable type represents interactive table built with jQuery dataTables plugin
     */
    const JS_DATATABLE	= 1;
	
    /**
     * Child table render type is a const for setting the rendering 
	 * of collapsible rows as a child table
     */
    const CHILD_TABLE	= 0;	

    /**
     * Collapsible rows render type is a const for setting the rendering
     * of collapsible rows as "built-in" rows
     */
    const CHILD_ROWS	= 1;		
    
    /**
     * The constant AUTODETECT_NUM_ROWS represents how much rows 
     * from the input data will be used to automatically detect
     * column datatypes
     */
    const AUTODETECT_NUM_ROWS = 10;
    
    /************************************
     *            PROPERTIES            *
     ************************************/
    
        /**
         * Static ID count, is used to auto-generate IDs if not provided by user
         * @var int
         */
        public static $id_count = 0;
        
        /**
         * The Memcached connection (if enabled)
         */
        public static $mc = null;
	
	/**
	 * How to render child tables. We use static to be able to access it from
	 * the row class.
	 * @var int
	 */
	private $_childTablesType = self::PLAIN_TABLE;	
	
	/**
	 * Do we display child tables as tables, or as built-in rows
	 */
	private $_childTablesRender = self::CHILD_ROWS;

        /**
        * Display and presentational properties
        */
	
	/**
	 * Type of table. May be a js datatable (default) or a usual plain HTML table
	 * @var int
	 */
        private $_tableType = self::JS_DATATABLE;
	
	/**
	 * Private flag for rendering the toolbar
	 * @var bool
	 */
        private $_toolbar = true;
	
	/**
	 * Private flag for rendering pagination in phpDataTables
	 * @var bool
	 */
	private $_pagination = true;
	
	/**
	 * Private flag for showing the filter in phpDataTables
	 * @var bool
	 */
    private $_showFilter = true;
    
    /**
     * Private flag for showing the advanced filter in phpDataTables
     *
     */
    private $_showAdvancedFilter = false;
	
	/**
	 * Private flag for enabling the sort option in dataTables
	 * @var bool
	 */
        private $_tableSort = true;
	
	/**
	 * Private flag for handling filtering, sorting and pagination by server
	 * NOT IN USE YET
	 * @var bool
	 */
        private $_serverProcessing = false;
	
	/**
	 * Private flag for redering the dataTables tableTools
	 * @var type 
	 */
        private $_showTableTools = true;
	
	/**
	 * The amount of rows on a page
	 * @var int
	 */
	private $_displayLength = 10;
	
	
	/**
	 * Private flag for rendering a gradient on table
	 * NOT IN USE YET
	 * @var bool 
	 */
        private $_showGradient = false;
	
	/**
	 * Default sort column for datatables
	 * @var int
	 */
	private $_defaultSortColumn = null;
		
	/**
	 * Private flag for rendering the <thead>
	 * @var bool
	 */
	private $_showHeader = true;
	
	/**
	 * Private flag for rendering the <tfoot>
	 * @var bool
	 */
        private $_showFooter = false;

	/**
	 * Name of the table
	 * @var string
	 */
    private $_name = '';
    
    /**
     * Language parameter
     * @var string
     */
     private $_interfaceLanguage;
        
    /**
     * Width of table 
     */
    private $_width = '';
	
	/**
	 * Class for header icon
	 * @var string
	 */
    private $_headerIconClass = '';
	
	/**
	 * Class for header
	 * @var string
	 */
    private $_headerClass = '';
	
	/**
	 * Private flag for making the header fixed
	 * @var bool
	 */
    private $_fixedHeader = false;
	
	
	/**
	 * Private flag for making columns fixed
	 * @var mixed
	 */
	private $_fixedColumns = false;
        
        /**
         * Left offset for fixed columns 
         * @var int
         */
        private $_leftFixedOffset = 0;
        
        /**
         * Top offset for fixed columns 
         */
        private $_topFixedOffset = 0;
        
	
	/**
	 * Private indicator for collapsible rows
	 * @var bool
	 */
        private $_hasCollapsible = false;
	
	/**
	 * Private indicator if grouping is enabled
	 * @var bool 
	 */
	private $_groupingEnabled = false;
	
	/**
	 * Private index of the group
	 * @var type 
	 */
	private $_groupingColumnIndex = 0;

	/**
	 * Generic template (around js and html)
	 * @var string
	 */
	private $_genericTemplate = 'generic_table.inc';
	
	/**
	 * Template for plain HTML table
	 * @var string
	 */
        private $_plainTemplate = 'plain_html_table.inc';
        
	/**
	 * Template for javascript included with plain HTML
	 * @var bool
	 */
        private $_plainJsTpl = 'js_plain.inc';
	
	/**
	 * Template for javascript included with dataTable
	 * @var string
	 */
        private $_dataTableJsTpl = 'js_datatable.inc';
	
	/**
	 * Template for plain HTML table
	 * @var string
	 */
        private $_chartJsTpl = 'chart_js_template.inc';
        
        /**
	 * CSS classes array
	 * @var array
	 */
        private $_classes = array( );
	
	/**
	 * In-line CSS style for the table
	 * @var string
	 */
        private $_style	 = '';

	/**
	 * ID of the table on the page
	 * @var string
	 */
        private $_id = '';
        
        
    /**
     * wpDataTable ID
     * @var string
     */
     	private $_wpId = '';
    
    /**
     * Data processing properties 
     * @var bool
     */
    private $_detectDataType = true;

    /**
     * Data containers 
     */
	
	/**
	 * Array holding column definitions
	 * @var array
	 */
        private $_columns = array( );
	
	/**
	 * Reference to columns by assoc keys
	 * @var array
	 */
        private $_columnsByKeys = array( );
    
	/**
	 * Array of column data types (int, string, date, etc)
	 * @var string
	 */
        private $_dataTypes = array( );
	
	/**
	 * Array of the rows in the table
	 * @var array
	 */
        private $_rows = array( );
        
        /**
         * Flag indicating if the table is stored in cache.
         * @var bool
         */
        private $_fromCache = false;
        
        /** 
         * The hash for cache 
         * @var string
         */
        public $_cacheHash = '';
        
        /**
         * Flag indicating that the table is first on the page 
         * @var bool
         */
        private $_isFirst = false;
        
        
        /**
         * Key of the column which will be chart horizontal axis 
         * @var string
         */
        private $_horAxisCol = '';

        /**
         * Key of the column which will be chart vertical axis 
         * @var string
         */
        private $_verAxisCol = '';
        
        /**
         * Type of the chart. Supported types: Area, Bar, Column, Line
         * @var string
         */
        private $_chartType = '';
        
        
        /**
         * Title of the chart
         * @var string
         */
        private $_chartTitle = '';
        
        /**
         * Array of the column keys, which will be chart's series
         */
        private $_chartSeriesArr = array();
        
    
        /**
        * Resources
        */
    
        /**
         * Db object.
         *
         * @var object
         */
        private $_db;
	
	/**
	 * Allowed column types.
	 * Can be extended from outside to add new column subclasses.
	 */
	public static $allowedColumnTypes = array('int', 'float', 'date', 'email', 'string', 'link', 'image', 'formatnum');
    
    /************************************
     *             METHODS              *
     ************************************/
    
    /**
     * Constructor
     * 
     * @param res db The instance of db class.
     */
    public function __construct( ) {
        // connect to MySQL if enabled
        if(PDT_ENABLE_MYSQL && get_option('wdtUseSeparateCon')){
            $this->_db = new PDTSql(PDT_MYSQL_HOST, PDT_MYSQL_DB, PDT_MYSQL_USER, PDT_MYSQL_PASSWORD);
        }
        // check if the instance of table is the first on page
        if(self::$id_count == 0){
            $this->_isFirst = true;
        }
        // try to enable Memcached
        if($this->_isFirst && PDT_ENABLE_MEMCACHE){
            if(class_exists('Memcache')){
                self::$mc = new Memcache();
                self::$mc->connect(PDT_MEMCACHE_HOST, PDT_MEMCACHE_PORT);
                // clear the cache if requested
                if(!empty($_GET['clearcache'])){
                    self::$mc->flush();
                }
            }
        }
        // try to enable GC
        gc_enable();
	$this->_id = 'table_'.self::$id_count++;
    }
    
    /******** Getters / setters ********/
    
    /**
     * Method returns current table type
     * 
     * @param void
     *
     * @return int Current table type
     */
    public function getTableType() {
		return $this->_tableType;
    }
    
    /**
     * Method sets the table type.
     * Allowed table types are defined in constants
     * (self::PLAIN_TABLE, self::JS_DATATABLE)
     * 
     * @param int table type
     *
     * @return bool Result of setting table type
     */
    public function setTableType( $tableType ) {
		if( ( $tableType != self::PLAIN_TABLE ) && ( $tableType != self::JS_DATATABLE ) ) {
			throw new Exception('Unknown type of table!');
		}
		$this->_tableType = $tableType;
		return true;
    }
    
    /**
     * Returns the width of table
     */ 
    public function getWidth(){
        return $this->_width;
    }
    
    /**
     * Sets the width of table 
     */
    public function setWidth( $width ){
        if(isset($width)){
            $this->_width = $width;
        }
    }
    
    /**
     * Method returns the current table id
     */
    public function getId() {
		return $this->_id;
    }
    
    /**
     * Sets the table id
     */
    public function setId( $id ) {
		$this->_id = $id;
    }
    
    /**
     * Method returns the current table id
     */
    public function getWpId() {
		return $this->_wpId;
    }
    
    /**
     * Sets the table id
     */
    public function setWpId( $wpId ) {
		$this->_wpId = $wpId;
    }
        
    /**
     * Returns the currently set CSS classes in an array
     */
    public function getClassesArray(){
		return $this->_classes;
    }
    
    /**
     * Returns the currently set CSS classes in a string
     */
    public function getClasses(){
		return implode(' ', $this->_classes);
    }
    
    /**
     * Adds a CSS class to the table
     */
    public function addClass( $class ) {
		$this->_classes[] = $class;
    }
    
    /**
     * Returns the style string for table
     */
    public function getStyle() {
		return $this->_style;
    }
    
    /**
     * Sets the style string for table
     */
    public function setStyle( $style ){
		$this->_style = $style;
    }
    
    /**
     * Sets the table name (headeline)
     */
    public function setName( $name ) {
		$this->_name = $name;
    }
    
    /**
     * Returns the current table name
     * @return string
     */
    public function getName(){
		return $this->_name;
    }
    
    /**
     * Enable server processing for MySQL-based tables
     */
     public function enableServerProcessing(){
     	$this->_serverProcessing = true;
     }
     
     
     /**
      * Disable server processing for MySQL-based tables
      */
      public function disableServerProcessing(){
      	$this->_serverProcessing = false;
      }
      
      /**
       * Returns the current value of the Server Processing flag
       */
       public function serverSide(){
       	return $this->_serverProcessing;
       }
     
     
    /**
     * Sets the interface language
     * @param string $lang 
     */
     public function setInterfaceLanguage( $lang ) {
     		if( empty($lang) ){
     			throw new Exception('Incorrect language parameter!');
     		}
     		if( !file_exists( PDT_ROOT_PATH.'source/lang/'.$lang ) ){
     			throw new Exception('Language file not found');
     		}
     		$this->_interfaceLanguage = PDT_ROOT_PATH.'source/lang/'.$lang;
     }
     
     /**
      * Returns the interface language
      */
      public function getInterfaceLanguage(){
      		return $this->_interfaceLanguage;
      }
	
    /**
     * Sets the child tables type as JS Datatables
     */
    public function setChildTablesJs() {
            $this->_childTablesType = self::JS_DATATABLE;
    }

    /**
     * Sets the child tables as plain HTML tables
     */
    public function setChildTablesPlain() {
            $this->_childTablesType = self::PLAIN_TABLE;
    }

    /**
     * Returns the child tables type 
     */
    public function getChildTablesType(){
            return $this->_childTablesType;
    }

    /**
     * Sets the rendering of child tables as nested tables.
     * If the columns are set as fixed - disable it.
     */
    public function renderChildAsNested(){
            if($this->columnsFixed()){
                $this->unfixColumns();
            }
            $this->_childTablesRender = self::CHILD_TABLE;
    }

    /**
     * Sets the rendering of child tables as rows of parent table
     */
    public function renderChildAsRows(){
            $this->_childTablesRender = self::CHILD_ROWS;
    }

    /**
     * Returns the render type of child tables
     */
    public function getChildRenderType(){
            return $this->_childTablesRender;
    }
    
    /**
     * Method checks is the pager enabled for the current talbe
     * 
     * @param void
     *
     * @return bool Current ager state.
     */    
    public function pagerEnabled() {
		return $this->_showPager;
    }
    
    /**
     * Method turns on the pager display for current table
     * 
     * @param void
     *
     * @return void
     */
    public function enablePager() {
		$this->_showPager = true;
    }
    
    /**
     * Method turns off the pager display for current table
     * 
     * @param void
     *
     * @return void
     */
    public function disablePager() {
		$this->_showPager = false;
    }
    
    /**
     * Checks if the toolbar is visible
     */
    public function toolbarVisible() {
		return $this->_toolbar;
    }
    
    /**
     * Sets toolbar as visible
     */
    public function showToolbar() {
		$this->_toolbar = true;
    }
	
    /**
     * Enable table tools (for dataTables only)
     */
    public function enableTableTools() {
            $this->_showTableTools = true;
    }
	
    /**
     * Disable table tools (dataTables only)
     */
    public function disableTableTools() {
            $this->_showTableTools = false;
    }

    /**
     * Get if table tools is enabled 
     */
    public function tableToolsEnabled() {
            return $this->_showTableTools;
    }
    
    /**
     * Hides the toolbar 
     */
    public function hideToolbar() {
		$this->_toolbar = false;
    }
    
	
    /**
     * Sets the default sort column 
     * @param mixed $key Index or assoc key of the column which should be
     * used for sorting by default
     */
    public function setDefaultSortColumn( $key ){
            if(!isset($this->_columns[$key]) && !isset($this->_columnsByKeys[$key])) {
                    throw new Exception('Incorrect column index');
            }

            // if assoc key provided, converting it to numeric
            // because javascript accepts only numeric keys
            if(!is_numeric($key)){
                    $key = array_search($key, array_keys($this->_columnsByKeys));
            }
            $this->_defaultSortColumn = $key;
    }
	
    /**
     * Gets the numeric index of default sort column
     * If it's not set, returns null 
     * @return mixed
     */
    public function getDefaultSortColumn(){
            // if there are collapsible rows, returning the index increased by one 
            // (because of the [+] column)
            if($this->hasCollapsible()){
                    return $this->_defaultSortColumn+1;
            }else{
            // if there are no collapsible rows, returning just the index
                    return $this->_defaultSortColumn;
            }
    }

    /**
     * Fixes the table headers
     */
    public function fixHeaders() {
            $this->_fixedHeader = true;
    }

    /**
     * Unfixes the table headers
     */
    public function unfixHeaders() {
            $this->_fixedHeader = false;
    }

    /**
     * Returns if headers are fixed
     * @return bool Flag if headers are fixed
     */
    public function headersFixed() {
            return $this->_fixedHeader;
    }

    /**
     * Makes the  columns fixed
     * DISABLED FOR INTERNET EXPLORER and iPad/iPod/iPhone
     * @param int $count number of columns to be fixed
     */
    public function fixColumns($count = 1) {
            if($this->groupingEnabled()){
                $this->disableGrouping();
            }
            if($this->advancedFilterEnabled()){
            	$this->disableAdvancedFilter();
            }
            if (isset($_SERVER['HTTP_USER_AGENT']) && 
                (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') === false) &&
                (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') === false) &&
                (strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') === false) &&
                (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') === false)) {
                $this->_fixedColumns = $count;
            }
    }
    
    /**
     * Sets the left offset for the fixed headers/columns
     * in pixels
     * 
     * @param int $offset 
     */
    public function setLeftOffset($offset) {
        $this->_leftFixedOffset = $offset;
    }
    
    /**
     * Gets the left offset for the fixed headers/columns
     * in pixels
     */
    public function getLeftOffset() {
        return $this->_leftFixedOffset;
    }    
    
    /**
     * Sets the top offset for the fixed headers/columns
     * in pixels
     * 
     * @param int $offset 
     */
    public function setTopOffset($offset) {
        $this->_topFixedOffset = $offset;
    }

    /**
     * Gets the top offset for the fixed headers/columns
     * in pixels
     */
    public function getTopOffset() {
        return $this->_topFixedOffset;
    }

    /**
     * Disables the columns fixation
     */
    public function unfixColumns() {
            $this->_fixedColumns = false;
    }
	
    /**
     * Returns whether there are fixed columns.
     * If there are no fixed columns, returns false.
     * Otherwise returns the number of fixed columns
     */
    public function columnsFixed() {
            return $this->_fixedColumns;
    }

    /**
     * Enables the pagination
     * Only for datatables 
     */
    public function enablePagination(){
            $this->_pagination = true;
    }
	
    /**
     * Disables the pagination in datatables
     */
    public function disablePagination(){
      $this->_pagination = false;
    }
	
    /**
        * Returns true if pagination is enabled for current table
        */
    public function paginationEnabled(){
            return $this->_pagination;
    }
	
    /**
     * Returns the display length for datatables
     * @return int
     */
    public function getDisplayLength(){
            return $this->_displayLength;
    }
	
    /**
     * Sets the display length (number of rows per page) for datatables
     * @param int $length Length. Allowed vals: 5, 10, 20, 30, 50, 100, 200, -1
     */
    public function setDisplayLength( $length ){
            if(!in_array($length, array(5, 10, 20, 25, 30, 50, 100, 200, -1))){
                    throw new Exception('Invalid length!');
            }		
            $this->_displayLength = $length;
    }
    
    /**
     * Helper function to check is array assoc or indexed
     * @param type $arr
     * @return type 
     */
    private function _isAssoc($arr) {
		return array_keys($arr) !== range(0, count($arr) - 1);
    }
    
    /**
     * Sets the columns width from an array
     * accepts the indexed array to ('3%', '5%', ... )
     * or associative by column keys ('id' => '3%', 'name' => '5%', ...)
     * 
     * @param type $array
     * @throws Exception 
     */
    public function setColumnsWidth( $array ) {
		if( empty($this->_columns) ) {
			throw new Exception('No columns in the table!');
		}
		if( !is_array($array) ) {
			throw new Exception('Valid array of width values is required!');
		}
		if( $this->_isAssoc($array) ) {
			// if width is provided by assoc keys
			foreach( $array as $key=>$value ) {
				if(!isset($this->_columnsByKeys[$key])) { continue; }
				$this->_columnsByKeys[$key]->setWidth($value);
			}
		} else{
			// if width is provided in indexed array
			foreach( $array as $key=>$value ) {
				$this->_columns[$key]->setWidth($value);
			}
		}
    }
	
	public function getHiddenColumnCount(){
		$count = 0;
		foreach($this->_columns as $column){
			if(!$column->isVisible()){
				$count++;
			}
		}
		return $count;
	}
       
    /**
     * Method checks is the filtering enabled for the current talbe
     * 
     * @param void
     *
     * @return bool Current filtering state.
     */    
    public function filterEnabled() {
		return $this->_showFilter;
    }
    
    /**
     * Method turns on the filter display for current table
     * 
     * @param void
     *
     * @return void
     */
    public function enableFilter() {
		$this->_showFilter = true;
    }
    
    /**
     * Method turns off the filter display for current table
     * 
     * @param void
     *
     * @return void
     */
    public function disableFilter() {
		$this->_showFilter = false;
    }    
    
       
    /**
     * Method checks is the advanced column filtering enabled for the current talbe
     * 
     * @param void
     *
     * @return bool Current filtering state.
     */    
    public function advancedFilterEnabled() {
		return $this->_showAdvancedFilter;
    }
    
    /**
     * Method turns on the advanced filter display for current table
     * Turns off the fixed column;
     * 
     * @param void
     *
     * @return void
     */
    public function enableAdvancedFilter() {
    	if($this->columnsFixed()){
            $this->unfixColumns();
        }
		$this->_showAdvancedFilter = true;
    }
    
    /**
     * Method turns off the filter display for current table
     * 
     * @param void
     *
     * @return void
     */
    public function disableAdvancedFilter() {
		$this->_showAdvancedFilter = false;
    }    
    
    /**
     * Method checks is the sorting enabled for the current talbe
     * 
     * @param void
     *
     * @return bool Current ager state.
     */    
    public function sortingEnabled() {
		return $this->_tableSort;
    }
    
    /**
     * Method turns on sorting option for current table
     * 
     * @param void
     *
     * @return void
     */
    public function enableSorting() {
		$this->_tableSort = true;
    }
    
    /**
     * Method turns off the pager display for current table
     * 
     * @param void
     *
     * @return void
     */
    public function disableSorting() {
		$this->_tableSort = false;
    }
	
    /**
     * Enables grouping for the current table 
     */
    public function enableGrouping() {
            $this->_groupingEnabled = true;
    }
	
    /**
     * Disables grouping for the current table
     */
    public function disableGrouping() {
            $this->_groupingEnabled = false;
    }

    /**
     * Returns if grouping is enabled 
     */
    public function groupingEnabled() {
            return $this->_groupingEnabled;
    }
	
    /**
     * Sets grouping by particular column
     * Can accept assoc key, or index
     * 
     * @param mixed $columnKey
     */
    public function groupByColumn($key) {
        if(!isset($this->_columns[$key]) && !isset($this->_columnsByKeys[$key])){
            throw new Exception('Column not found!');
        }

        if(!is_numeric($key)){
            $key = array_search($key, array_keys($this->_columnsByKeys));
        }
        
        if($this->columnsFixed()){
            $this->unfixColumns();
        }
        
        // enable grouping only if table doesn't have child rows
        if($this->hasCollapsible()) {
            return false;
        }

        $this->enableGrouping();
        $this->_groupingColumnIndex = $key;
    }

    /**
     * Returns the index of grouping column 
     */
    public function groupingColumnIndex(){
            return $this->_groupingColumnIndex;
    }

    /**
     * Returns the grouping column index
     */
    public function groupingColumn(){
            return $this->_groupingColumnIndex;
    }
	
    
    /**
     * Method returns the number of added columns in the table
     * 
     * @return int
     */
    public function getColumnCount() {
		return count($this->_columns);
    }
    
    /**
     * Method returns the hash-keys for columns
     * 
     * @return type 
     */
    public function getColumnKeys() {
		return array_keys( $this->_columnsByKeys );
    }
    
    /**
     * Method returns the column list for the table
     * @return array
     */
    public function getColumns() {
		return $this->_columns;
    }
    
    /**
     * Method returns the column list for the table indexed by keys
     * @return array
     */
    public function getColumnsByKeys() {
		return $this->_columnsByKeys;
    }
		
    /**
     * Returns the dataTables column definitions 
     */
    public function getColumnDefs() {
            $defs = array();
            if($this->hasCollapsible()){
                    // inserting definition for the collapsible icon column
                    $def = new stdClass();
                    $def->aTargets = array(0);
                    $def->sWidth = '6px';
                    $defs[] = json_encode($def);
            }
            foreach($this->_columns as $key=>$column){
                    $def = $column->getJsDefinition();
                    // $i is an index for dataTables aTargets pointer.
                    // if we have collapsible rows, reserving [0] key for the 
                    // column with [+] and inc everything by 1
                    $i = ($this->hasCollapsible() ? $key+1 : $key);
                    if($this->columnsFixed() && ($i < $this->columnsFixed())){
                            $def->sClass .= " fixed_column";
                    }
                    if($this->columnsFixed() && $this->groupingEnabled() && ($this->groupingColumnIndex() == $key)){
                            $def->bVisible = false;
                    }
                    $def->aTargets = array($i);
                    $defs[] = json_encode($def);
            }
            return implode(', ', $defs);
    }
    
    /**
     * Returns the descriptions of advanced filter types for columns
     */
     public function getColumnFilterDefs() {
            $defs = array();
            if($this->hasCollapsible()){
                    // inserting definition for the collapsible icon column
                    $def = new stdClass();
                    $def->type = 'null';
                    $defs[] = json_encode($def);
            }
             foreach($this->_columns as $key=>$column){
                    $def = $column->getFilterType();
                    $defs[] = json_encode($def);
            }
            return implode(', ', $defs);    	
     }
    
    /**
     * Method returns a single column by a provided index 
     * (int or string)
     * 
     * @param mixed $columnKey 
     */
    public function getColumn( $columnKey ) {
		if( !isset($columnKey) 
			|| ( !isset($this->_columnsByKeys[$columnKey])
			&& !isset($this->_columns[$columnKey]) ) ) {
			throw new Exception('Invalid columnKey provided!');
		}
		if(!is_int($columnKey)){
			return $this->_columnsByKeys[$columnKey];
		} else {
			return $this->_columns[$columnKey];
		}
    }

    /**
     * Method sets a new column type for existing column
     * 
     * @param mixed $columnKey the key for the column we change
     * @param string $columnType the type for column
     */
    public function setColumnType( $columnKey, $columnType ) {
            // checking
            if( !isset($columnKey) || !isset($this->_columnsByKeys[$columnKey]) ) {
                    throw new Exception('Invalid columnKey provided!');
            }
            if( !isset( $columnType ) || !in_array( $columnType, self::$allowedColumnTypes ) ) {
                    throw new Exception('Invalid columnType provided!');
            }
            // Creating a column of new type
            $newColumn = Column::factory($columnType);

            // Transferring all settings from the old column
            $newColumn->setHeader($this->_columnsByKeys[$columnKey]->getHeader());
            foreach( $this->_columnsByKeys[$columnKey]->getClassesArray() as $class ){
                    $newColumn->addClass($class);
            }
            $newColumn->setWidth( $this->_columnsByKeys[$columnKey]->getWidth() );
            $newColumn->setStyle( $this->_columnsByKeys[$columnKey]->getStyle() );
            if( $this->_columnsByKeys[$columnKey]->sortingEnabled() ){
                    $newColumn->enableSorting();
            }
            if( $this->_columnsByKeys[$columnKey]->searchingEnabled() ){
                    $newColumn->enableSearching();
            }

            // switching to new column and unsetting the old one
            $oldColumn =& $this->_columns[array_search($columnKey, $this->getColumnKeys())];
            $this->_columns[array_search($columnKey, $this->getColumnKeys())] = $newColumn;
            unset($oldColumn);
    }

    /**
     * Method to make a column with provided index invisible
     * 
     * @param mixed $columnKey
     * @throws Exception 
     */
    public function hideColumn( $columnKey ) {
		if( !isset($columnKey) || !isset($this->_columnsByKeys[$columnKey]) ) {
			throw new Exception('Invalid columnKey provided!');
		}
		$this->_columnsByKeys[$columnKey]->hide();
    }

    /**
     * Method to make a column with provided index visible
     * 
     * @param mixed $columnKey
     * @throws Exception 
     */
    public function showColumn( $columnKey ) {
		if( !isset($columnKey) || !isset($this->_columnsByKeys[$columnKey]) ) {
			throw new Exception('Invalid columnKey provided!');
		}
		$this->_columnsByKeys[$columnKey]->show();
    }
    
    /**
     * Reorder columns accordingly to provided values
     * 
     * @param array $posArray Array, where the keys correspond to column positions,
     * and the values to the existing column keys
     */
     public function reorderColumns( $posArray ) {
     	if( !is_array( $posArray )){
     		throw new Exception('Invalid position data provided!');
     	}
     	$resultArray = array();
     	$resultByKeys = array();
     	foreach( $posArray as $pos=>$columnKey ){
     		$resultArray[$pos] = $this->_columnsByKeys[$columnKey];
     		$resultByKeys[$columnKey] = $this->_columnsByKeys[$columnKey];
     	}
     	$this->_columns = $resultArray;
     	$this->_columnsByKeys = $resultByKeys;
     }
    
    /**
     * Returns the Cell instance located at the provided column and row indexes.
     * $columnKey may be integer index, or string key.
     * 
     * @param mixed $columnKey Column key (index for indexed arrays, or string)
     * @param int $rowKey Row index
     * @return Cell
     * @throws Exception if column or row keys aren't provided, or out of bounds
     */
    public function getCell( $columnKey, $rowKey ) {
		if( !isset( $columnKey ) || !isset( $rowKey ) ) {
			throw new Exception('Please provide the column key and the row key');
		}
		if( !isset( $this->_rows[$rowKey]) ) {
			throw new Exception('Row index out of bounds!');
		}
		if( !isset($this->_columnsByKeys[$columnKey]) && !isset($this->_columns[$columnKey]) ) {
			throw new Exception('Column index out of bounds!');
		}
		return $this->_rows[$rowKey]->getCell( $columnKey );
    }
    
    public function getFormattedCellVal( $cell, $columnKey ) {
		if( !isset($cell) || !( $cell instanceof Cell ) ) { 
			throw new Exception('Valid Cell object not provided!');
		}
		if( !isset($columnKey) ) { 
			throw new Exception('Column index not provided!');
		}
		if( !isset( $this->_columnsByKeys[$columnKey] ) ) {
			throw new Exception('Column index out of bounds!');
		}
		return $this->_columnsByKeys[$columnKey]->getFormattedCellVal( $cell );
    }
    
    /**
     * Method returns the row list for the table
     */
    public function getRows() {
		return $this->_rows;
    }
    
    public function getRow( $index ) {
                if( self::$mc && $this->_fromCache ) return false;
		if( !isset($index) || !isset($this->_rows[$index]) ) {
			throw new Exception('Invalid row index!');
		}
                // if we are getting data from cachejust skip the call
		$link = &$this->_rows[$index];
		return $link;
    }
    
    
    /**
     * Method turns on the footer displaying
     */
    public function showFooter() {
		$this->_showFooter = true;
    }
    
    /**
     * Method turns off the footer displaying 
     */
    public function hideFooter() {
		$this->_showFooter = false;
    }
    
    /**
     * Method returns the state of footer visibility
     */
    public function footerVisible() {
		return $this->_showFooter;
    }
	
    
    /**
     * Method turns on the header displaying
     */
    public function showHeader() {
		$this->_showHeader = true;
    }
    
    /**
     * Method turns off the footer displaying 
     */
    public function hideHeader() {
		$this->_showHeader = false;
    }
    
    /**
     * Method returns the state of footer visibility
     */
    public function headerVisible() {
		return $this->_showHeader;
    }	
    
    public function getFooter() {
		return 'table footer';
    }
    
    /**
     * Checks if table has collapsible rows
     */
    public function hasCollapsible() {
		return $this->_hasCollapsible;
    }
    
    /**
     * Sets collapsible rows visible 
     */
    public function showCollapsible() {
		$this->_hasCollapsible = true;
    }
    
    /**
     * Sets collapsible rows invisible
     */
    public function hideCollapsible() {
		$this->_hasCollapsible = false;
    }
    
    /**
     * Data processing methods
     */
    
    /**
     * Method adds a column to the table.
     * Works only with instances of the Column child classes.
     * Otherwise returns false;
     * 
     * @param object $column 
     * 
     * @return bool Result of adding the column.
     */
    public function addColumn( &$column ) {
		if( !($column instanceof Column) ) {
			throw new Exception('Only Column objects are allowed!');
		}
		$this->_columns[] = &$column;
		return true;
    }
    
    /**
     * Method adds a prepared group of columns to the table
     * Calls the addColumn() method
     * 
     * @param array $columns
     * @return void|boolean 
     */
    public function addColumns( &$columns ) {
		if( !is_array( $columns ) ) {
			throw new Exception('Parameter must be an array of columns!');
		}
		foreach( $columns as &$column ) {
			$this->addColumn( $column );
		}
    }
    
    /**
     * Method to add a prepared row to the table
     * 
     * @param Row $row the prepared instance of the Row class
     * @return void|boolean
     * @throws Exception if count of cells in the row mismatches the count of columns in the table
     */
    public function addRow( $row ) {
		if( count($this->_columns) == 0 ) {
			throw new Exception('You should define the columns first!');
		}
		if( !($row instanceof Row) ) {
			throw new Exception('Only Row object type is allowed!');
		}
		if( $row->getCellCount() != $this->getColumnCount() ) {
			throw new Exception('Column count mismatch');
		}
		if( $row->hasChildTable() ) {
			$this->_hasCollapsible = true;
		}
		$this->_rows[] = &$row;
    }
    
    /**
     * Method adds a prepared group of rows to the table
     * Calls the addRow() method
     * 
     * @param type $rows
     * @return void|boolean 
     */
    public function addRows( &$rows ) {
		if( !is_array( $rows ) ) {
			throw new Exception('Parameter must be an array of rows!');
		}
		foreach( $rows as &$row ) {
			$this->addRow( $row );
		}
    }
    
    /**
     * Method which sets the horizontal axis from one of the table's columns
     * @param type $columnKey 
     */
    public function setChartHorizontalAxis($columnKey){
        // if we are getting the data from cache - just skip the call
        if(self::$mc && $this->_fromCache){
            return false;
        }        
        if( !isset($columnKey) || !isset($this->_columnsByKeys[$columnKey]) ) {
                throw new Exception('Invalid columnKey provided!');
        }
        $this->_horAxisCol = $columnKey;          
    }
    
    /**
     * Method which sets the horizontal axis from one of the table's columns
     * @param type $columnKey 
     */
    public function setChartVerticalAxis($columnKey){
        // if we are getting the data from cache - just skip the call
        if(self::$mc && $this->_fromCache){
            return false;
        }        
        if( !isset($columnKey) || !isset($this->_columnsByKeys[$columnKey]) ) {
                throw new Exception('Invalid columnKey provided!');
        }
        $this->_verAxisCol = $columnKey;          
    }
    
    /**
     * Method which sets the chart title
     * @param string $title
     */
    public function setChartTitle($title){
        if(empty($title)) { return false; }; 
        $this->_chartTitle = $title;
    }
    
    /**
     * Method which returns the chart title
     */
    public function getChartTitle(){
        return $this->_chartTitle;
    }
    
    /**
     * Method which sets the chart type
     * @param string $type
     */
    public function setChartType($type){
        if(empty($type) 
                || (!in_array($type, array('Area', 'Bar', 'Column', 'Line', 'Pie')))) { 
            throw new Exception('Invalid chart type provided!'); 
        }; 
        $this->_chartType = $type;
    }
    
    /**
     * Method which returns the chart type
     */
    public function getChartType(){
        return $this->_chartType;
    }
    
    /**
     * Method which adds a chart series by column key 
     */
    public function addChartSeries($columnKey){
        // if we are getting the data from cache - just skip the call
        if(self::$mc && $this->_fromCache){
            return false;
        }
        if( !isset($columnKey) || !isset($this->_columnsByKeys[$columnKey]) ) {
                throw new Exception('Invalid columnKey provided!');
        }
        $this->_chartSeriesArr[] = $columnKey;
    }
    
    /**
     * Method builds the data structure of the Table object from an input array.
     * 
     * For child (collapsible) rows the key in the parent array must be called 
     * $array['_child_rows'], and must be an 2D-array of rows (even if there is only
     * one child row).
     * 
     * $tableParams[] keys might be the following:
     * 
     * - 'column_names' : array of headers for columns, binded to the $array keys, 
     * e.g.: array('clt' => 'Client Name', 'bdg' => 'Budget', ... )
     * If no column name is provided, the array key will be used as a column
     * header.
     * 
     * - 'data_types' : array of data types in the array rows. Data types might 
     * be the following: 'int', 'float', 'email', 'string', 'date', 'currency_eur', 
     * 'currency_usd', 'percentage'. Data types should be binded to the $array keys,
     * and the element count must match.
     * e.g. array('clt' => 'string', 'id' => 'int', 'bdg' => 'float', ...)
     * 
     * @param array $array
     * @param array $tableParams 
     */
	public function buildByArray( $array, $tableParams = array() ) {
		if(empty($array)){
			throw new Exception('Input array is empty!');
		}
        // checking if the table is existing in cache
        // and setting the flag if it does
        if( self::$mc ) {
            $this->_cacheHash = 'bby_'.md5( serialize( $array ) );
            if( @self::$mc->get( $this->_cacheHash ) ) {
                $this->_fromCache = $this->_cacheHash;
                return true;
            }
        }
          
		// Extracting the header keys
		reset($array);
                if(!is_array($array[key($array)])){
                    throw new Exception('2D-array required, 1D-array provided.');
                }
		$header_keys = array_keys($array[key($array)]);
		
		// '_child_row' - reserved key for the collapsible rows
		if( in_array('_child_rows', $header_keys ) ) { unset($header_keys[array_search('_child_rows', $header_keys)]); }

		// if provided, extracting the dataTypes
		$dataTypes = isset($tableParams['data_types']) ? $tableParams['data_types'] : array( );
		
		// if autodetect of column rows is on
		// and $tableParams['dataTypes'] is not provided
		if( ( $this->_detectDataType ) && ( empty( $dataTypes ) ) ){
			// Building the autodetect data array
			$autodetectData		 = array();
			$autodetectRowsCount = (self::AUTODETECT_NUM_ROWS > count($array)) ? 
				count($array)-1 : self::AUTODETECT_NUM_ROWS-1;
			for( $i = 0; $i <= $autodetectRowsCount; $i++){
				foreach($header_keys as $key) {
					$cur_val = current($array);
					if(!is_array($cur_val[$key])){
						$autodetectData[$key][] = $cur_val[$key];
					}else{
						if(array_key_exists('value',$cur_val[$key])){
							$autodetectData[$key][] = $cur_val[$key]['value'];
						}else{
							// if we don't find the 'value' key in the cell array,
							// throw an exception
							throw new Exception('Unknown array format for the cell!');
						}
					}
				}
				next($array);
			}
			
			// detecting the dataTypes
			foreach( $header_keys as $key ){
				$dataTypes[$key] = $this->_autoDetectDataType( $autodetectData[$key] );
			}
		}

		// if dataTypes still aren't set, setting all as string
		if( empty( $dataTypes ) ) {
			foreach( $header_keys as $key ){
				$dataTypes[$key] = 'string';
			}
		}

		// Creating the columns one by one
		foreach($header_keys as $key) {

			// setting up the new column parameters
			$columnParams = array( );
			$columnParams['header']	= isset($tableParams['column_names'][$key]) ? $tableParams['column_names'][$key] : $key;
			$columnParams['width']	= !empty($tableParams['columns_width'][$key]) ? $tableParams['columns_width'][$key] : '';
			$columnParams['sorting'] = $this->_tableSort; // set the sorting for column by default same as parent

			// creating a new column by column factory
			$column = Column::factory($dataTypes[$key], $columnParams);

			// inserting the column to the array
			$this->_columns[] = $column;

			// creating a reference to be able to access the column by key
			$this->_columnsByKeys[$key] = &$this->_columns[count($this->_columns)-1];

		}
		
		// Updating the global dataTypes property
		$this->_dataTypes = $dataTypes;

		// Setting row object parameters
		$rowParams = array( );
		$rowParams['dataTypes']		= $dataTypes;
		$rowParams['parentTable']	= $this;

		// Adding the rows
		foreach( $array as $arr_row ) {
			$row = new Row( $rowParams );
			if( !$row->addCellsFromRowArray( $arr_row ) ) return false;
			$this->addRow( $row );
		}

		return true;

    }
    
    /**
     * Method builds a table by provided SQL-query.
     * 
     * Passes the $query and $queryParams to the db instance, takes the same 
     * $tableParams format as buildByArray @see PHPDataTable::buildByArray()
     * 
     * @param string $query
     * @param array $queryParams
     * @param array $tableParams
     * @return boolean Result of the operation
     */
    public function buildByQuery($query, $queryParams = array(), $tableParams = array ()) {
         // checking if the table is existing in cache
         // and setting the flag if it does
         if( self::$mc ) {
             $this->_cacheHash = 'bbq_'.md5( $query );
             if( @self::$mc->get( $this->_cacheHash ) ){
                 $this->_fromCache = $this->_cacheHash;
                 return true;
             }
         }
         
        // Sanitizing query
        $query = str_replace('DELETE', '', $query);
        $query = str_replace('DROP', '', $query);
        $query = str_replace('INSERT', '', $query);
       	$query = stripslashes($query);
        
        // Adding limits if necessary
        if(!empty($tableParams['limit']) 
        	&& (strpos(strtolower($query), 'limit') === false)){
       		$query .= ' LIMIT '.$tableParams['limit'];
        }
        
        // Server-side requests
        if($this->serverSide()) {
	        if(!isset($_GET['sEcho'])) {
				$query .= ' LIMIT '.$this->getDisplayLength();
	        } else {
	        	// Server-side params
		        $limit = '';
		        $orderby = '';
		        $search = '';
		        $aColumns = array_keys($tableParams['column_names']);
		        
				if ( isset( $_GET['iDisplayStart'] ) && $_GET['iDisplayLength'] != '-1' ) {
						$limit = "LIMIT ".mysql_real_escape_string( $_GET['iDisplayStart'] ).", ".
							mysql_real_escape_string( $_GET['iDisplayLength'] );
					}        	
					
		        // Adding sort parameters for AJAX if necessary
				if ( isset( $_GET['iSortCol_0'] ) )
				{
					$orderby = "ORDER BY  ";
					for ( $i=0 ; $i<intval( $_GET['iSortingCols'] ) ; $i++ )
					{
						if ( $_GET[ 'bSortable_'.intval($_GET['iSortCol_'.$i]) ] == "true" )
						{
							$orderby .= $aColumns[ intval( $_GET['iSortCol_'.$i] ) ]."
							 	".mysql_real_escape_string( $_GET['sSortDir_'.$i] ) .", ";
						}
					}
					
					$orderby = substr_replace( $orderby, "", -2 );
					if ( $orderby == "ORDER BY" )
					{
						$orderby = "";
					}
				}      
				
				// filtering
				if ( $_GET['sSearch'] != "" )
				{
					$search = " (";
					for ( $i=0 ; $i<count($aColumns) ; $i++ )
					{
						$search .= $aColumns[$i]." LIKE '%".mysql_real_escape_string( $_GET['sSearch'] )."%' OR ";
					}
					$search = substr_replace( $search, "", -3 );
					$search .= ')';
				}
				
				/* Individual column filtering */
				for ( $i=0 ; $i<count($aColumns) ; $i++ )
				{
					if ( ($_GET['bSearchable_'.$i] == "true") 
						&& ($_GET['sSearch_'.$i] != '')
						&& ($_GET['sSearch_'.$i] != '~') )
					{
						if(($i > 0) && !empty($search)){
							$search .= ' AND ';
						}
						switch($tableParams['filter_types'][$aColumns[$i]]) {
							case 'number':
								$search .= $aColumns[$i]." = ".$_GET['sSearch_'.$i]." ";
								break;
							case 'number-range':
								list($left, $right) = explode('~', $_GET['sSearch_'.$i]);
								if($left){
									$search .= $aColumns[$i]." >= $left ";
								}
								if($right){
									$search .= ' AND '.$aColumns[$i]." <= $right ";
								}
								break;
							case 'date-range':
								list($left, $right) = explode('~', $_GET['sSearch_'.$i]);
								$date_format = str_replace('m', '%m', get_option('wdtDateFormat'));
								$date_format = str_replace('Y', '%Y', $date_format);
								$date_format = str_replace('d', '%d', $date_format);
								if($left && $right){
									$search .= $aColumns[$i]." BETWEEN STR_TO_DATE('$left', '$date_format') AND STR_TO_DATE('$right', '$date_format') ";
								}elseif($left){
									$search .= $aColumns[$i]." >= STR_TO_DATE('$left', '$date_format') ";
								}elseif($right){
									$search .= $aColumns[$i]." <= STR_TO_DATE('$right', '$date_format') ";
								}
								break;
							case 'select':
								$search .= $aColumns[$i]." = ".$_GET['sSearch_'.$i]." ";
								break;
							case 'text':
							default:
								$search .= $aColumns[$i]." LIKE '%".mysql_real_escape_string($_GET['sSearch_'.$i])."%' ";
						}
					}
				}
	        }
        }
        
        // The serverside return scenario
        if(isset($_GET['action']) && ($_GET['action'] == 'get_wdtable')){
        	
	        /**
	         * 1. Forming the query
	         */
	        $query = str_replace('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $query);
	        if($search){
		        if(strpos($query, 'WHERE')){
		        	$query .= ' AND '.$search;
		        }else{
		        	$query .= ' WHERE '.$search;
		        }
	        }
	        $query .= ' '.$orderby;
	        $query .= ' '.$limit;
	        
	        /**
	         * 2. Executing the queries
	         */
	        // The main query
			if(get_option('wdtUseSeparateCon')){
				$main_res_rows = $this->_db->getAssoc($query, $queryParams);
			}else{
				global $wpdb;
				// querying using the WP driver otherwise
				$main_res_rows = $wpdb->get_results( $query, ARRAY_A );
			}
			// result length after filtering
			if(get_option('wdtUseSeparateCon')){
				$res_length = $this->_db->getField('SELECT FOUND_ROWS()');
			}else{
				global $wpdb;
				// querying using the WP driver otherwise
				$res_length = $wpdb->get_row( 'SELECT FOUND_ROWS()', ARRAY_A );
				$res_length = $res_length['FOUND_ROWS()'];
			}
			// total data length
			// get the table name
			$table_name = substr($query, strpos(strtolower($query), 'from')+5);
			$table_name = substr($table_name, 0, strpos($table_name, ' '));
			$table_name = trim($table_name);
			if(get_option('wdtUseSeparateCon')){
				$total_length = $this->_db->getField('SELECT COUNT('.$aColumns[0].') FROM '.$table_name);
			}else{
				global $wpdb;
				// querying using the WP driver otherwise
				$total_length = $wpdb->get_row( 'SELECT COUNT('.$aColumns[0].') as cnt_total FROM '.$table_name, ARRAY_A );
				$total_length = $total_length['cnt_total'];
			}
			
			/**
			 * 3. Forming the output
			 */
			// base array
			$output = array(
				"sEcho" => intval($_GET['sEcho']),
				"iTotalRecords" => $total_length,
				"iTotalDisplayRecords" => $res_length,
				"aaData" => array()
			);
			
			// create the supplementary array of column objects 
			// which we will use for formatting
			$col_objs = array();
			foreach($tableParams['data_types'] as $column_key=>$column_type){
				$col_objs[$column_key] = Column::factory( $column_type );
			}
			// reformat output array and reorder as user wanted
			if(!empty($main_res_rows)){
				foreach($main_res_rows as $res_row){
					$row = array();
					foreach($tableParams['column_order'] as $column_key){
						$cell = new Cell($tableParams['data_types'][$column_key]);
						$cell->setContent($res_row[$column_key]);
						$row[] = $col_objs[$column_key]->getFormattedCellVal($cell);
						unset($cell);
					}
					$output['aaData'][] = $row;
				}
			}
			/**
			 * 4. Returning the result
			 */
			return json_encode($output);
        }else{
			// Getting the query result
			// getting by own SQL driver if the user wanted a separate connection
			if(get_option('wdtUseSeparateCon')){
				$res_rows = $this->_db->getAssoc($query, $queryParams);
			}else{
				global $wpdb;
				// querying using the WP driver otherwise
				$res_rows = $wpdb->get_results( $query, ARRAY_A );
			}
			// Sending the array to buildByArray
			return $this->buildByArray($res_rows, $tableParams);
        }
    }
    
    /**
     * Method builds the Table object data structure from a json-formatted array.
     * The parameters and requirements are same as for buildByArray 
     * @see PHPDataTable::buildByArray()
     * 
     * @param string $json
     * @param array $tableParams 
     */
    public function buildByJson( $json, $tableParams = array() ) {
         // checking if the table is existing in cache
         // and setting the flag if it does
         if( self::$mc ) {
             $this->_cacheHash = 'bbj_'.md5( $json );
             if( @self::$mc->get( $this->_cacheHash ) ){
                 $this->_fromCache = $this->_cacheHash;
                 return true;
             }
         }
        $json = file_get_contents($json);
		return $this->buildByArray(json_decode($json, true), $tableParams);
    }
    
    /**
     * Method builds the Table object data structure from an XML string.
     * The parameters and requirements are same as for buildByArray 
     * @see PHPDataTable::buildByArray().
     * 
     * Accepts only specific XML format (see examples).
     * 
     * Uses SimpleXML
     * 
     * @param string $xml URL of XML file to parse
     * @param array $tableParams 
     */
    public function buildByXML( $xml, $tableParams = array() ) {
        if(!$xml) {
            throw new Exception('XML file not found!');
        }
        if(strpos($xml, '.xml')===false){
            throw new Exception('Non-XML file provided!');
        }
        // checking if the table is existing in cache
        // and setting the flag if it does
        if( self::$mc ) {
            $this->_cacheHash = 'bbxml_'.md5( $xml );
            if( @self::$mc->get( $this->_cacheHash ) ){
                $this->_fromCache = $this->_cacheHash;
                return true;
            }
        }
                
        $xml_parsed = simplexml_load_file($xml);
        $xml_to_arr = $this->_xmlToArray($xml_parsed);
        // reassigning the content of root XML element
        // to the element itself
        while(is_array($xml_to_arr) && (count($xml_to_arr) == 1)){
            $xml_to_arr = $xml_to_arr[key($xml_to_arr)];
        }
        // loop through the elements of array
        // to extract the attributes of XML nodes to values
        foreach($xml_to_arr as &$xml_el){
            $xml_el = $xml_el['attributes'];
        }
        return $this->buildByArray( $xml_to_arr, $tableParams );
    }
    
    /**
     * Method builds and populates the Table object data structure from an Excel file
     * file.
     * 
     * Supports formats: .xlsx, .xls, .ods, .csv
     * 
     * Uses PHPExcel library.
     * 
     * @param string $xls_url URL for XLS file to parse
     * @param array @tableParams 
     */
    public function buildByExcel( $xls_url, $tableParams = array() ) {
    	ini_set("memory_limit", "2048M");
        if(!$xls_url) {
            throw new Exception('Excel file not found!');
        }
        if(!file_exists($xls_url)){
            throw new Exception('Provided file '.stripcslashes($xls_url).' does not exist!');
        }
        // checking if the table is existing in cache
        // and setting the flag if it does
        if( self::$mc ) {
            $this->_cacheHash = 'bbxls_'.md5( $xls_url );
            if( @self::$mc->get( $this->_cacheHash ) ){
                $this->_fromCache = $this->_cacheHash;
                return true;
            }
        }
        require_once(PDT_ROOT_PATH.'/lib/phpExcel/PHPExcel.php');
        $objPHPExcel = new PHPExcel();
        if(strpos(strtolower($xls_url), '.xlsx')){
            $objReader = new PHPExcel_Reader_Excel2007();
            $objReader->setReadDataOnly(true);
        }elseif(strpos(strtolower($xls_url), '.xls')){
            $objReader = new PHPExcel_Reader_Excel5();
            $objReader->setReadDataOnly(true);
        }elseif(strpos(strtolower($xls_url), '.ods')){
            $objReader = new PHPExcel_Reader_OOCalc();
            $objReader->setReadDataOnly(true);
        }elseif(strpos(strtolower($xls_url), '.csv')){
            $objReader = new PHPExcel_Reader_CSV();
        }else{
            throw new Exception('File format not supported!');
        }
        $objPHPExcel = $objReader->load($xls_url);
        $objWorksheet = $objPHPExcel->getActiveSheet();
		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();
		
		$headingsArray = $objWorksheet->rangeToArray('A1:'.$highestColumn.'1',null, true, true, true);
		$headingsArray = $headingsArray[1];
		
		$r = -1;
		$namedDataArray = array();
		for ($row = 2; $row <= $highestRow; ++$row) {
		    $dataRow = $objWorksheet->rangeToArray('A'.$row.':'.$highestColumn.$row,null, true, true, true);
		    if ((isset($dataRow[$row]['A'])) && ($dataRow[$row]['A'] > '')) {
		        ++$r;
		        foreach($headingsArray as $columnKey => $columnHeading) {
		            $namedDataArray[$r][$columnHeading] = $dataRow[$row][$columnKey];
		        }
		    }
		}
        
        /*
        $rowIterator = $objPHPExcel->getActiveSheet()->getRowIterator();
        $sheet = $objPHPExcel->getActiveSheet();
        $array_data = array();
        $array_entry = array();
        $column_headers = array();
        foreach($rowIterator as $row){
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $rowIndex = $row->getRowIndex();
            if($rowIndex == 1) {
                foreach($cellIterator as $cell){
                    $column_headers[$cell->getColumn()] = $cell->getCalculatedValue();
                }
            }else{
                foreach($column_headers as $column_key=>$column_header){
                    $cell = $sheet->getCell($column_key.$rowIndex);
                    $array_entry[$column_header] = $cell->getCalculatedValue();
                }
                $array_data[] = $array_entry;
            }
        }
        unset($objPHPExcel);
        unset($objReader);
        unset($rowIterator);
        unset($sheet);
        unset($cellIterator);
        */
        return $this->buildByArray($namedDataArray, $tableParams);
    }
    
    /**
     * Used for quick conversion of XML to an array
     * 
     * @param SimpleXML object $xml
     * @param type $root
     * @return type 
     */
    private function _xmlToArray($xml, $root = true) {
	    if (!$xml->children()) {
		    return (string)$xml;
	    }

	    $array = array();
	    foreach ($xml->children() as $element => $node) {
		    $totalElement = count($xml->{$element});

		    if (!isset($array[$element])) {
			    $array[$element] = "";
		    }

		    // Has attributes
		    if ($attributes = $node->attributes()) {
			    $data = array(
				    'attributes' => array(),
				    'value' => (count($node) > 0) ? xmlToArray($node, false) : (string)$node
			    );

			    foreach ($attributes as $attr => $value) {
				    $data['attributes'][$attr] = (string)$value;
			    }

			    if ($totalElement > 1) {
				    $array[$element][] = $data;
			    } else {
				    $array[$element] = $data;
			    }

		    // Just a value
		    } else {
			    if ($totalElement > 1) {
				    $array[$element][] = $this->_xmlToArray($node, false);
			    } else {
				    $array[$element] = $this->xmlToArray($node, false);
			    }
                    }
            }
    
            if ($root) {
                return array($xml->getName() => $array);
            } else {
                return $array;
            }
    }
    
    /**
     * Private function which detects the data type of the values in provided 
     * array.
     * To speed up the work of table class you may provide the dataTypes array
     * manually.
     * 
     * Possible data types: 'int', 'float', 'email', 'string', 'date', 'currency_eur', 
     * 'currency_usd', 'percentage'
     * 
     * Auto-detect only works for int, float, date and string.
     * 
     * @param array $values 
     */
    private function _autoDetectDataType( $values ) {
		// checking if the values are int
		if ( $this->_performCheck( $values, 'ctype_digit' ) ) {
			return 'int';
		}
		// checking if the values are float
		if ( $this->_performCheck( $values, 'is_numeric' ) ) {
			return 'float';
		}
		// checking if the values are date
		if ( $this->_performCheck( $values, 'strtotime' ) ) {
			return 'date';
		}
		// checking if the values are e-mails
		if ( $this->_performCheck( $values, 'preg_match', '/^[A-Z0-9._%-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i' ) ) {
			return 'email';
		}
		// checking if the values are URLs
		if ( $this->_performCheck( $values, 'preg_match', '/^\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]$/i' ) ) {
			return 'link';
		}
		// if no checks returned true, returning default string data type
		return 'string';
    }
    
    /**
     * Service func which performs the datatype check routine.
     * Needed to avoid code repetitions and for possibility of extension.
     * 
     * @param array $values Array of values to perform the check on.
     * @param callback $func The function which returns true/false for datatype check
     * @param type $regex
     * @return string 
     */
    private function _performCheck( $values, $func, $regex = '' ) {
		$count = 0;
		foreach( $values as $value ) {
			// if no regex provided, calling the func with only value parameter
			if( $regex == '' ) {
				if( call_user_func( $func, $value ) ) { $count++; }
				else { return false; }
			} else {
				// if regex provided, calling the func with regex and value parameters
				if( call_user_func( $func, $regex, $value ) ) { $count++; }
				else { return false; }
			}
		}
		if( $count == count($values) ) return true;
    }
    
    /**
     * Returns the object in a format accepted by DataTables
     *
     *
     */
     //public function get
         
    /**
     * Returns the rendered plain HTML table
     * @return string Rendered table
     * @throws Exception 
     */
    private function _getPlainHtmlView() {
	if ( count($this->_columns) == 0 )  throw new Exception('You should add columns first!');
	if ( count($this->_rows) == 0 ) throw new Exception('There are no rows in the table!');
        $tpl = new PDTTpl();
        $tpl->setTemplate('plain_html_table.inc');
        $tpl->addData('table', $this);
        return $tpl->returnData();
    }
    
    /**
     * Returns the JS datatables view
     * @return string Rendered table 
     */
    private function _getJsDataTablesView() {
        $tpl = new PDTTpl();
        if(PDT_INCLUDE_DATATABLES_CORE){
            $tpl->addJs(PDT_JS_PATH.'jquery-datatables/jquery.dataTables.min.js');
        }
        if($this->tableToolsEnabled()){
            $tpl->addJs(PDT_JS_PATH.'jquery-datatables/TableTools.min.js');
        }
        $tpl->addJs(PDT_JS_PATH.'jquery-datatables/jquery.dataTables.editable.js');
        $tpl->addJs(PDT_JS_PATH.'php-datatables/datatables.js');
        $tpl->addJs(PDT_JS_PATH.'jquery-datatables/jquery.dataTables.rowGrouping.js');
        $tpl->addJs(PDT_JS_PATH.'jquery-datatables/FixedHeader.js');
        if($this->columnsFixed() || $this->headersFixed()){
                $tpl->addJs(PDT_JS_PATH.'jquery-datatables/FixedColumns.min.js');
        }
		if($this->filterEnabled()){
				$tpl->addJs(PDT_JS_PATH.'jquery-datatables/jquery.dataTables.columnFilter.js');
		}
        $this->addClass( 'data-t' );
        $tpl->setTemplate('plain_html_table.inc');
        $tpl->addData('table', $this);
        return $tpl->returnData();
    }
    
    /**
     * Generates and renders the table javascript
     * @return string Rendered script block
     */
    private function _getGeneratedJs(){
        $tpl = new PDTTpl();
        $tpl->addData('table',$this);
        if ($this->_tableType == self::PLAIN_TABLE) {
            $tpl->setTemplate($this->_plainJsTpl);
        } else {
            $tpl->setTemplate($this->_dataTableJsTpl);
        }
        return $tpl->returnData();
    }
    
    /**
     * Renderes the tables, or gets it from Memcached, if possible.
     * @return string
     */
    public function renderTable() {
        // trying to get data from cache
        if( ( self::$mc ) && ( $this->_fromCache ) ) {
            $return_data = @self::$mc->get( $this->_cacheHash );
            if($return_data){
                return $return_data;
            }
        }

        $tpl = new PDTTpl();
        // include the styles if the table is first on the page
        if($this->_isFirst) {
            $tpl->addCss(PDT_CSS_PATH.'jquery.dataTables.css');
            $tpl->addCss(PDT_CSS_PATH.'TableTools.css');
            $tpl->addCss(PDT_ASSETS_PATH.'css/phpDataTables.css');
        }
        if ($this->_tableType == self::PLAIN_TABLE) {
            $table_content = $this->_getPlainHtmlView();
        }else{
            $table_content = $this->_getJsDataTablesView();
        }
        $scripts = $this->_getGeneratedJs();
	$tpl->addData( 'rendered_table', $table_content );
	$tpl->adddata( 'scripts', $scripts );
	$tpl->setTemplate( $this->_genericTemplate );
        
        $return_data = $tpl->returnData();
        
        // adding data to cache
        if( ( self::$mc ) ) {
            self::$mc->set( $this->_cacheHash, $return_data, false, PDT_MEMCACHE_TIME );
        }
        
	return $return_data;
    }
    
    /**
     * Prints the rendered table 
     */
    public function printTable() {
        echo $this->renderTable();
    }
    
    /**
     * Renders the chart block, which will be supposed to render in the
     * div with provided ID
     * @param string Container div ID 
     */
    public function renderChart( $divId ) {
        if(!$divId){
            throw new Exception('No div ID provided!');
        }
        
        // trying to get data from cache
        if( ( self::$mc ) && ( $this->_fromCache ) ) {
            $return_data = @self::$mc->get( 'chart_'.$this->_cacheHash );
            if($return_data){
                return $return_data;
            }
        }
        
        $tpl = new PDTTpl();
        $tpl->setTemplate( $this->_chartJsTpl );
        $series_headers = array();
        $series_values = array();

        foreach($this->_chartSeriesArr as $columnKey){
            $series_headers[] = '"'.$this->getColumn($columnKey)->getHeader().'"';
        }
        
        foreach($this->getRows() as $row) {
            $series_values_entry = array();
            foreach($this->_chartSeriesArr as $columnKey){
                $val = $row->getCell($columnKey)->getContent();
                if($this->getColumn($columnKey)->getDataType() != 'string') {
					if($this->getColumn($columnKey)->getDataType() == 'date'){
						$val = '"'.date(get_option('wdtDateFormat'), $val).'"';
					}
                    $series_values_entry[] = $val;
                } else {
                    $series_values_entry[] = '"'.$val.'"';
                }
            }
            $series_values[] = '['.implode(', ', $series_values_entry).']';
        }

        $tpl->addData('chart_title', $this->getChartTitle());
        $tpl->addData('chart_container', $divId);
        $hor_axis = $this->_horAxisCol ? $this->getColumn($this->_horAxisCol)->getHeader() : ''; 
        $ver_axis = $this->_verAxisCol ? $this->getColumn($this->_verAxisCol)->getHeader() : ''; 
        $tpl->addData('hor_axis', $hor_axis);
        $tpl->addData('ver_axis', $ver_axis);
        $tpl->addData('series_headers', implode(', ', $series_headers));
        $tpl->addData('series_values', implode(",\n", $series_values));
        $tpl->addData('chart_type', $this->getChartType());
        
        $return_data = $tpl->returnData();
        
        // adding data to cache
        if( ( self::$mc ) ) {
            self::$mc->set( 'chart_'.$this->_cacheHash, $return_data, false, PDT_MEMCACHE_TIME );
        }        
        
        return $return_data;
    }
    
    /**
     * Prints the rendered chart
     */
    public function printChart( $divId ) {
        if(!$divId){
            throw new Exception('No div ID provided!');
        }        
        echo $this->renderChart( $divId );
    }    
    
    
}
    
    
?>
