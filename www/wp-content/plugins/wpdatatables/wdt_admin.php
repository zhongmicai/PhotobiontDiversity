<?php
/**
 * @package wpDataTables
 * @version 1.0
 */
/**
 * The admin page
 */
?>
<?php
	// add the page to WP Admin
	add_action( 'admin_menu', 'wpdatatables_admin_menu' );
	// add AJAX-handlers
	add_action('wp_ajax_wdt_save_settings', 'wdt_save_settings');
	add_action('wp_ajax_wdt_save_table', 'wdt_save_table');
	add_action('wp_ajax_wdt_save_columns', 'wdt_save_columns');
	add_action( 'wp_ajax_wdt_get_preview', 'wdt_get_ajax_preview' );
	// add the thickbox CSS and JS
	add_action('admin_print_scripts', 'wdt_admin_scripts');
	add_action('admin_print_styles', 'wdt_admin_styles');
	
	/**
	 * Generates the admin menu in admin panel sidebar
	 */
	function wpdatatables_admin_menu() {
		add_menu_page( 'wpDataTables', 'wpDataTables', 'manage_options', 'wpdatatables-administration', 'wpdatatables_browse');
		add_submenu_page( 'wpdatatables-administration', 'Add a new wpDataTable', 'Add new', 'manage_options', 'wpdatatables-addnew', 'wpdatatables_addnew');
		add_submenu_page( 'wpdatatables-administration', 'wpDataTables settings', 'Settings', 'manage_options', 'wpdatatables-settings', 'wpdatatables_settings');
	}
	
	/**
	 * Adds JS to the admin panel
	 */
	function wdt_admin_scripts() {
		wp_enqueue_script('media-upload');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-widget');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-dialog');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/themes/smoothness/jquery-ui.css'); 
		wp_register_script(
			'wdt-colorpicker',
			plugins_url( 'assets/js/colorpicker/jquery.modcoder.excolor.js' , __FILE__ )
		);		
	}	
	
	/**
	 * Adds CSS styles in the admin
	 */
	function wdt_admin_styles() {
		wp_enqueue_style('thickbox');
	}	

	/**
	 * Function which saves the global settings for the plugin
	 */
	function wdt_save_settings(){
		// Get and write main settings
		$wpUseSeparateCon = ($_POST['wpUseSeparateCon'] == 'true');
		$wpMySqlHost = $_POST['wpMySqlHost'];
		$wpMySqlDB = $_POST['wpMySqlDB'];
		$wpMySqlUser = $_POST['wpMySqlUser'];
		$wpMySqlPwd = $_POST['wpMySqlPwd'];
		$wpRenderCharts = $_POST['wpRenderCharts'];
		$wpRenderFilter = $_POST['wpRenderFilter'];
		$wpInterfaceLanguage = $_POST['wpInterfaceLanguage'];
		$wpDateFormat = $_POST['wpDateFormat'];
		$wpTopOffset = $_POST['wpTopOffset'];
		$wpLeftOffset = $_POST['wpLeftOffset'];
		update_option('wdtUseSeparateCon', $wpUseSeparateCon);
		update_option('wdtMySqlHost', $wpMySqlHost);
		update_option('wdtMySqlDB', $wpMySqlDB);
		update_option('wdtMySqlUser', $wpMySqlUser);
		update_option('wdtMySqlPwd', $wpMySqlPwd);
		update_option('wdtRenderCharts', $wpRenderCharts);
		update_option('wdtRenderFilter', $wpRenderFilter);
		update_option('wdtInterfaceLanguage', $wpInterfaceLanguage);
		update_option('wdtDateFormat', $wpDateFormat);
		update_option('wdtTopOffset', $wpTopOffset);
		update_option('wdtLeftOffset', $wpLeftOffset);
		
		// Get font and color settings
		$wdtFontColorSettings = array();
		$wdtFontColorSettings['wdtHeaderBaseColor'] = $_POST['wdtHeaderBaseColor'];
		$wdtFontColorSettings['wdtHeaderActiveColor'] = $_POST['wdtHeaderActiveColor'];
		$wdtFontColorSettings['wdtHeaderFontColor'] = $_POST['wdtHeaderFontColor'];
		$wdtFontColorSettings['wdtHeaderBorderColor'] = $_POST['wdtHeaderBorderColor'];
		$wdtFontColorSettings['wdtTableOuterBorderColor'] = $_POST['wdtTableOuterBorderColor'];
		$wdtFontColorSettings['wdtTableInnerBorderColor'] = $_POST['wdtTableInnerBorderColor'];
		$wdtFontColorSettings['wdtTableFontColor'] = $_POST['wdtTableFontColor'];
		$wdtFontColorSettings['wdtTableFont'] = $_POST['wdtTableFont'];
		$wdtFontColorSettings['wdtHoverRowColor'] = $_POST['wdtHoverRowColor'];
		$wdtFontColorSettings['wdtOddRowColor'] = $_POST['wdtOddRowColor'];
		$wdtFontColorSettings['wdtEvenRowColor'] = $_POST['wdtEvenRowColor'];
		$wdtFontColorSettings['wdtActiveOddCellColor'] = $_POST['wdtActiveOddCellColor'];
		$wdtFontColorSettings['wdtActiveEvenCellColor'] = $_POST['wdtActiveEvenCellColor'];
		
		// Serialize settings and save to DB
		update_option('wdtFontColorSettings',serialize($wdtFontColorSettings));
		
	}
	
	/**
	 * Get all tables for the browser
	 */
	 function wdt_get_all_tables(){
	 		global $wpdb;
	 		$query = "SELECT id, title, table_type
	 					FROM {$wpdb->prefix}wpdatatables";
	 		$all_tables = $wpdb->get_results( $query, ARRAY_A );
	 		return $all_tables;
	 }
	 
	/**
	 * Helper method which creates the
	 * columnset in the DB from a PHPDataTable object
	 */
	function wdt_create_columns_from_table( $table, $table_id ){
		global $wpdb;
		$columns = $table->getColumns();
		foreach($columns as $key=>&$column){
			$column->table_id = $table_id;
			$column->orig_header = $column->getHeader();
			$column->display_header = $column->getHeader();
			$column->filter_type = $column->getFilterType()->type;
			$column->column_type = $column->getDataType();
			$column->use_in_chart = false;
			$column->horiz_axis = false;
			$column->group_column = false;
			$column->pos = $key;
			$column->width = '';
			$column->visible = 1;
			$wpdb->insert($wpdb->prefix .'wpdatatables_columns',
							array(
								'table_id' => $table_id,
								'orig_header' => $column->orig_header,
								'display_header' => $column->display_header,
								'filter_type' => $column->filter_type,
								'column_type' => $column->column_type,
								'group_column' => 0,
								'use_in_chart' => (int)$column->use_in_chart,
								'chart_horiz_axis' => (int)$column->chart_horiz_axis,
								'pos' => $column->pos,
								'width' => $column->width,
								'visible' => $column->visible
								)
							);
			$column->id = $wpdb->insert_id;
		}
		return $columns;
	}
	
	/**
	 * Saves the general settings for the table, tries to generate the table 
	 * and default settings for the columns
	 */
	function wdt_save_table(){
		global $wpdb;
		$table_id = $_POST['table_id'];
		$table_title = $_POST['table_title'];
		$table_type = $_POST['table_type'];
		if(($table_type == 'csv') || ($table_type == 'xls')){
			$table_content = str_replace(site_url(), ABSPATH, $_POST['table_content']);
		}else{
			$table_content = $_POST['table_content'];
		}
		$table_advanced_filtering = ($_POST['table_advanced_filtering'] == 'true');
		$table_tools = ($_POST['table_tools'] == 'true');
		$table_sorting = ($_POST['table_sorting'] == 'true');
		$table_fixed_layout = ($_POST['fixed_layout'] == 'true');
		$table_word_wrap = ($_POST['word_wrap'] == 'true');
		$table_display_length = $_POST['table_display_length'];
		$table_fixheader = ($_POST['table_fixheader'] == 'true');
		$table_fixcolumns = $_POST['table_fixcolumns'];
		$table_chart = $_POST['table_chart'];
		$table_charttitle = $_POST['table_charttitle'];
		$table_serverside = ($_POST['table_serverside'] == 'true');
		
		if(!$table_fixheader){
			$table_fixcolumns = -1;
		}else{
			$table_fixcolumns = (int)$table_fixcolumns;
		}
		if(!$table_id){
			// adding new table
			// trying to generate a phpDataTable
			$res = wdt_try_generate_table( $table_type, $table_content );
			if(!empty($res['error'])){
				// if phpDataTables returns an error, replying to the page
				echo json_encode( $res ); die();
			}else{
				// if no problem reported, first saving the table parameters to DB
				$wpdb->insert($wpdb->prefix .'wpdatatables',
								array(
									'title' => $table_title,
									'table_type' => $table_type,
									'content' => $table_content,
									'filtering' => (int)$table_advanced_filtering,
									'sorting' => (int)$table_sorting,
									'fixed_layout' => (int)$table_fixed_layout,
									'word_wrap' => (int)$table_word_wrap,
									'tools' => (int)$table_tools,
									'display_length' => $table_display_length,
									'fixed_columns' => $table_fixcolumns,
									'chart' => $table_chart,
									'chart_title' => $table_charttitle,
									'server_side' => (int)$table_serverside
									)
								);
				// get the newly generated table ID
				$table_id = $wpdb->insert_id;
				$res['table_id'] = $table_id;
				// creating default columns for the new table
				$res['columns'] = wdt_create_columns_from_table($res['table'], $table_id);
				echo json_encode($res); die();
			}
		}else{
			// editing existing table
			$query = 'SELECT * 
						FROM '.$wpdb->prefix.'wpdatatables
						WHERE id='.$table_id;
			$table_data = $wpdb->get_row($query, ARRAY_A);
			// checking if table type or content has changed
			if(($table_data['content'] == $table_content)
				&& ($table_data['table_type'] == $table_type)){
				// if it didn't change only updating the record
				// and receiving the columnset
				$wpdb->update($wpdb->prefix.'wpdatatables',
								array(
									'title' => $table_title,
									'table_type' => $table_type,
									'content' => $table_content,
									'filtering' => (int)$table_advanced_filtering,
									'sorting' => (int)$table_sorting,
									'fixed_layout' => (int)$table_fixed_layout,
									'word_wrap' => (int)$table_word_wrap,
									'tools' => (int)$table_tools,
									'display_length' => $table_display_length,
									'fixed_columns' => $table_fixcolumns,
									'chart' => $table_chart,
									'chart_title' => $table_charttitle,
									'server_side' => (int)$table_serverside
									),
								array(
									'id' => $table_id
									)
								);
				 $res['table_id'] = $table_id;
				 $res['columns']  = wdt_get_columns_by_table_id( $table_id );
				 echo json_encode($res); die();
			}else{
				// if it changed trying to rebuild the table and reloading the columnset
				$res = wdt_try_generate_table( $table_type, $table_content );
				if(!empty($res['error'])){
					// if phpDataTables returns an error, replying to the page
					echo json_encode( $res ); die();
				}else{
					// otherwise updating the table
					$wpdb->update($wpdb->prefix.'wpdatatables',
								array(
									'title' => $table_title,
									'table_type' => $table_type,
									'content' => $table_content,
									'filtering' => (int)$table_advanced_filtering,
									'sorting' => (int)$table_sorting,
									'fixed_layout' => (int)$table_fixed_layout,
									'word_wrap' => (int)$table_word_wrap,
									'tools' => (int)$table_tools,
									'display_length' => $table_display_length,
									'fixed_columns' => $table_fixcolumns,
									'chart' => $table_chart,
									'chart_title' => $table_charttitle,
									'server_side' => (int)$table_serverside
									),
								array(
									'id' => $table_id
									)
								);
				 	$res['table_id'] = $table_id;
				 	// deleting all existing columns
				 	$query = 'DELETE FROM '.$wpdb->prefix .'wpdatatables_columns
				 				WHERE table_id = '.$table_id;
				 	$wpdb->query( $query );
				 	// rebuilding the columnset
					$res['columns'] = wdt_create_columns_from_table($res['table'], $table_id);
					echo json_encode($res); die();				 	
				}
				
			}
		}
	}
	
	/**
	 * Saves the settings for columns
	 */
	function wdt_save_columns(){
		global $wpdb;
		$table_id = $_POST['table_id'];
		$columns = $_POST['columns'];
		foreach($columns as $column){
			$wpdb->update($wpdb->prefix.'wpdatatables_columns',
							array(
								'display_header' => $column['display_header'],
								'filter_type' => $column['filter_type'],
								'column_type' => $column['column_type'],
								'group_column' => (int)($column['group_column'] == 'true'),
								'use_in_chart' => (int)($column['use_in_chart'] == 'true'),
								'chart_horiz_axis' => (int)($column['chart_horiz_axis'] == 'true'),
								'visible' => (int)($column['visible'] == 'true'),
								'width' => $column['width'],
								'pos' => $column['pos']
								),
							array(
								'id' => $column['id']
								)
							);
		}
		$res['columns'] = wdt_get_columns_by_table_id( $table_id );
		echo json_encode($res); exit();
	}
	
	
	/**
	 * Tries to generate a PHPDataTable object by user's setiings
	 */
	function wdt_try_generate_table( $type, $content ) {
		$tbl = new PHPDataTable();
		$result = array();
		$table_params = array( 'limit' => '10' );
		switch($type){
			case 'mysql' :
				try {
					$tbl->buildByQuery( $content, array(), $table_params );
					$result['table'] = $tbl;
				}catch( Exception $e ) {
					$result['error'] = $e->getMessage();
					return $result;
				} 
				break;
			case 'csv' :
			case 'xls' :
				try {
					$tbl->buildByExcel( $content, $table_params );
					$result['table'] = $tbl;
				} catch( Exception $e ) {
					$result['error'] = $e->getMessage();
					return $result;
				} 
				break;
			case 'xml' :
				try {
					$tbl->buildByXML( $content, $table_params );
					$result['table'] = $tbl;
				} catch( Exception $e ) {
					$result['error'] = $e->getMessage();
					return $result;
				} 
				break;
			case 'json' :
				try {
					$tbl->buildByJson( $content, $table_params );
					$result['table'] = $tbl;
				} catch( Exception $e ) {
					$result['error'] = $e->getMessage();
					return $result;
				} 
				break;
			case 'serialized' :
				try {
					$array = unserialize( file_get_contents ( $content ) );
					$tbl->buildByArray( $array, $table_params );
					$result['table'] = $tbl;
				} catch( Exception $e ) {
					$result['error'] = $e->getMessage();
					return $result;
				} 
				break;
		}
		return $result;		
	}
	
	/**
	 * Renders the browser of existing tables
	 */
	function wpdatatables_browse() {
		global $wpdb;
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$action = $_GET['action'];
		if($action == 'edit'){
			$id = $_GET['table_id'];
			$tpl = new PDTTpl();
			$tpl->setTemplate( 'edit_table.inc' );
			$tpl->addData('wpShowTitle', 'Edit wpDataTable');
			$tpl->addData('table_id', $id);
			$tpl->addData('table_data', wdt_get_table_by_id($id));
			$tpl->addData('column_data', wdt_get_columns_by_table_id($id));
			$tpl->showData();
		}else{
			if($action == 'delete') {
				$id = $_GET['table_id'];
				$wpdb->query("DELETE 
								FROM {$wpdb->prefix}wpdatatables
								WHERE id={$id}");
			}			
			$tpl = new PDTTpl();
			$tpl->setTemplate( 'browse.inc' );
			$tpl->addData( 'wpAllTables', wdt_get_all_tables() );
			$tpl->showData();
		}
	}
	
	function wpdatatables_addnew() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}
		$tpl = new PDTTpl();
		$tpl->setTemplate( 'edit_table.inc' );
		$tpl->addData('wpShowTitle', 'Add a new wpDataTable');
		$tpl->showData();
	}
	
	function wpdatatables_settings() {
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		wp_enqueue_script('wdt-colorpicker');
		
		$languages = array();
		
		foreach(glob( PDT_ROOT_PATH .'source/lang/*.inc') as $lang_filename) {
			$lang_filename = str_replace(PDT_ROOT_PATH .'source/lang/', '', $lang_filename);
			$name = ucwords(str_replace('_', ' ', $lang_filename));
			$name = str_replace('.inc', '', $name);
			$languages[] = array('file' => $lang_filename, 'name' => $name);
		}
		
		$tpl = new PDTTpl();
		$tpl->setTemplate( 'settings.inc' );
		$tpl->addData('wpUseSeparateCon', get_option('wdtUseSeparateCon'));
		$tpl->addData('wpMySqlHost', get_option('wdtMySqlHost'));
		$tpl->addData('wpMySqlDB', get_option('wdtMySqlDB'));
		$tpl->addData('wpMySqlUser', get_option('wdtMySqlUser'));
		$tpl->addData('wpMySqlPwd', get_option('wdtMySqlPwd'));
		$tpl->addData('wpRenderCharts', get_option('wdtRenderCharts'));
		$tpl->addData('wpRenderFilter', get_option('wdtRenderFilter'));
		$tpl->addData('wpDateFormat', get_option('wdtDateFormat'));
		$tpl->addData('wpTopOffset', get_option('wdtTopOffset'));
		$tpl->addData('wpLeftOffset', get_option('wdtLeftOffset'));
		$tpl->addData('languages', $languages);
		$tpl->addData('wpInterfaceLanguage', get_option('wdtInterfaceLanguage'));
		$tpl->addData('wdtFonts', wdt_get_system_fonts());
		$wpFontColorSettings = get_option('wdtFontColorSettings');
		if(!empty($wpFontColorSettings)){
			$wpFontColorSettings = unserialize($wpFontColorSettings);
		}else{
			$wpFontColorSettings = array();
		}
		$tpl->addData('wdtFontColorSettings', $wpFontColorSettings);
		$tpl->showData();
	}	

?>
