<?php
/**
* Settings file for the wpDataTables plugin
* 
* @author Alexander Gilmanov cjbug@ya.ru
* @since 26.01.2013
*
**/


/**
 * Path settings.
 * Paths are relative by default, but you may change it as you wish
 */
define('PDT_TEMPLATE_PATH', PDT_ROOT_PATH.'templates/'); // path to wpDataTables templates. You should not change this setting if you use default templates
define('PDT_ASSETS_PATH', PDT_ROOT_URL.'assets/'); // path to wpDataTables assets directory. You should not change this setting if you don't change default CSS/JS
define('PDT_CSS_PATH', PDT_ROOT_URL.'assets/css/'); // path to wpDataTables CSS styles. You should not change this setting if you use default CSS
define('PDT_JS_PATH', PDT_ROOT_URL.'assets/js/'); // path to wpDataTables javascript. You should not change this setting if you use default javascripts.


/**
 * Settings which define whether we include the JS files
 * from the plugin build or not
 * (if user already has them included in the page)
 */ 
define('PDT_INCLUDE_DATATABLES_CORE', true); // Whether to include link to jQuery DataTables plugin javascript to the generated page. Set to false if you already have DataTables included in your project (version used in wpDataTables is 1.9.1, newer version will be provided with updates).

/**
 * MySQL settings for query-based tables
 */ 
define('PDT_ENABLE_MYSQL', true); // Whether to use MySQL in wpDataTables. Disable if you are not going to access MySQL directly from wpDataTables.
if(get_option('wdtUseSeparateCon')){
    define('PDT_MYSQL_HOST', get_option('wdtMySqlHost')); // Name or address of MySQL host
    define('PDT_MYSQL_DB', get_option('wdtMySqlDB')); // Name of MySQL database to use
    define('PDT_MYSQL_USER', get_option('wdtMySqlUser')); // Name of MySQL user
    define('PDT_MYSQL_PASSWORD', get_option('wdtMySqlPwd')); // Password to use in MySQL
}

?>
