<?php
/*
  Plugin Name: Hire Quote
  Plugin URI: https://www.freelancer.com/u/hina2329.html
  Description: Custom Product Quote / Order System for Wordpress. Use <code>[hire-quote]</code> shortcode to display the system on front end.
  Version: 1.0
  Author: Hina Farid
  Author URI: https://www.freelancer.com/u/hina2329.html
 */

// Main Plugin Class
class HireQuote {

    protected $wpdb;
    protected $page;
    protected $products_tbl;
    protected $categories_tbl;
    protected $options_tbl;
    protected $coupons_tbl;
    protected $postcodes_tbl;
    protected $orders_tbl;
    protected $customers_tbl;

    public function __construct() {

        // Globalizing $wpdb variable
        global $wpdb;
        $this->wpdb = $wpdb;

        // Table names
        $this->products_tbl = $this->wpdb->prefix . 'hq_products';
        $this->categories_tbl = $this->wpdb->prefix . 'hq_categories';
        $this->options_tbl = $this->wpdb->prefix . 'hq_options';
        $this->coupons_tbl = $this->wpdb->prefix . 'hq_coupons';
        $this->postcodes_tbl = $this->wpdb->prefix . 'hq_postcodes';
        $this->orders_tbl = $this->wpdb->prefix . 'hq_orders';
        $this->customers_tbl = $this->wpdb->prefix . 'hq_customers';

        // User HTTP request for class
        $this->page = filter_input(INPUT_GET, 'page');

        // Adding the main page
        add_action('admin_menu', array($this, 'hq_menu'));

        // Installing new tables in the database
        add_action('plugins_loaded', array($this, 'install_tables'));

        // Loading plugin resources for admin
        add_action('admin_head', array($this, 'register_admin_resources'));

        // Loading plugin resources for front end
        add_action('wp_head', array($this, 'register_frontend_resources'));

        // Registering plugin's settings
        add_action('admin_init', array($this, 'register_plugin_settings'));

        // Regestring schortcode
        add_shortcode('hire-quote', array($this, 'hq_shortcode'));

        // Allow redirection
        ob_start();
    }

    // Register plugin's settings
    public function register_plugin_settings() {
        register_setting('hq_settings', 'hq_settings');
    }

    // Menu items
    public function hq_menu() {
        add_menu_page('Hire Quote', 'Hire Quote', 'manage_options', 'manage_orders', array($this, 'hq_main'), 'dashicons-format-aside');
        add_submenu_page('manage_orders', 'Manage Products', 'Manage Products', 'manage_options', 'manage_products', array($this, 'hq_main'));
        add_submenu_page('manage_orders', 'Manage Categories', 'Manage Categories', 'manage_options', 'manage_categories', array($this, 'hq_main'));
        add_submenu_page('manage_orders', 'Manage Post Codes', 'Manage Post Codes', 'manage_options', 'manage_postcodes', array($this, 'hq_main'));
        add_submenu_page('manage_orders', 'Manage Coupons', 'Manage Coupons', 'manage_options', 'manage_coupons', array($this, 'hq_main'));
        add_submenu_page('manage_orders', 'Manage Options', 'Manage Options', 'manage_options', 'manage_options', array($this, 'hq_main'));
        add_submenu_page('manage_orders', 'Customers List', 'Customers List', 'manage_options', 'customers_list', array($this, 'hq_main'));
        add_submenu_page('manage_orders', 'Settings', 'Settings ', 'manage_options', 'settings', array($this, 'hq_main'));
    }

    // Mian page
    public function hq_main() {
        ?>
        <div id="hq-wrap" class="wrap">

            <?php
            // Requesting appropriate object
            require_once $this->page . '.php';
            $obj = new $this->page;

            // User HTTP request for method
            $action = filter_input(INPUT_GET, 'action');

            if (!isset($action)) {
                $action = 'init';
            }

            $obj->$action();
            ?>

        </div>
        <?php
    }

    // Registering plugin admin resources
    public function register_admin_resources() {
        // Admin Stylesheet
        wp_register_style('hq-style-admin', plugins_url('hire-quote/css/hq-style-admin.css'));
        wp_enqueue_style('hq-style-admin');
        wp_enqueue_style('thickbox');

        // Admin JavaScript
        wp_register_script('hq-script-admin', plugins_url('hire-quote/js/hq-script-admin.js'));
        wp_enqueue_script('hq-script-admin');
        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');
    }

    // Registering plugin front end resources
    public function register_frontend_resources() {
        // Stylesheet
        wp_register_style('hq-style', plugins_url('hire-quote/css/hq-style.css'));
        wp_enqueue_style('hq-style');
        wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

        // JawaScript
        wp_register_script('hq-script-frontend', plugins_url('hire-quote/js/hq-script.js'));
        wp_enqueue_script('hq-script-frontend');
        wp_enqueue_script('jquery-ui-datepicker');
    }

    // Notifications
    public function notify($module) {
        $msg = filter_input(INPUT_GET, 'update');
        $settings = filter_input(INPUT_GET, 'settings-updated');
        if (isset($msg)) {
            echo '<div id="message" class="updated notice notice-success is-dismissible"><p>' . $module . ' ' . $msg . ' successfully!</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        } else if (isset($settings)) {
            echo '<div id="message" class="updated notice notice-success is-dismissible"><p>' . $module . ' updated successfully!</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
        }
    }

    // Shortcode
    public static function hq_shortcode() {
        require_once 'shortcode.php';
        new shortcode;
    }

    // Tables queries for database
    public function install_tables() {

        // Queries to create tables
        $categories_table = "CREATE TABLE $this->categories_tbl (
            cat_id INT(5) NOT NULL AUTO_INCREMENT,
            cat_name VARCHAR(100) NOT NULL,
            cat_allowed VARCHAR(256) NOT NULL,
            cat_not_allowed VARCHAR(256) NOT NULL,
            PRIMARY KEY (cat_id)
        ) COLLATE = 'utf8_general_ci', ENGINE = 'InnoDB';";

        $options_table = "CREATE TABLE $this->options_tbl (
            opt_id INT(5) NOT NULL AUTO_INCREMENT,
            opt_name VARCHAR(100) NOT NULL,
			opt_price INT(5) NOT NULL,
            PRIMARY KEY (opt_id)
        ) COLLATE = 'utf8_general_ci', ENGINE = 'InnoDB';";

        $coupons_table = "CREATE TABLE $this->coupons_tbl (
            copn_id INT(5) NOT NULL AUTO_INCREMENT,
            copn_code VARCHAR(100) NOT NULL,
            copn_discount FLOAT NOT NULL,
            PRIMARY KEY (copn_id)
        ) COLLATE = 'utf8_general_ci', ENGINE = 'InnoDB';";

        $postcodes_table = "CREATE TABLE $this->postcodes_tbl (
            pc_id INT(5) NOT NULL AUTO_INCREMENT,
            pc_code INT(8) NOT NULL,
            pc_suburb VARCHAR(100) NOT NULL,
			pc_state VARCHAR(100) NOT NULL,
            PRIMARY KEY (pc_id)
        ) COLLATE = 'utf8_general_ci', ENGINE = 'InnoDB';";
        
        $customers_table = "CREATE TABLE $this->customers_tbl (
            cust_id INT(5) NOT NULL AUTO_INCREMENT,
            cust_name VARCHAR(100)NOT NULL,
            cust_address VARCHAR(256) NOT NULL,
            cust_suburb VARCHAR(100) NULL,
            cust_postcode INT(8) NOT NULL,
            cust_phone VARCHAR(100) NOT NULL,
            cust_email VARCHAR(100) NOT NULL,
            PRIMARY KEY (cust_id)
        ) COLLATE = 'utf8_general_ci', ENGINE = 'InnoDB';";
        
        $orders_table = "CREATE TABLE $this->orders_tbl (
            odr_id INT(5) NOT NULL AUTO_INCREMENT,
            odr_cust_id INT(5) NOT NULL,
            odr_prod_id INT(5) NOT NULL,
            odr_cat_id INT(5) NOT NULL,
            odr_options VARCHAR(255) NOT NULL,
            odr_d_date VARCHAR(100) NOT NULL,
            odr_c_date VARCHAR(100) NOT NULL,
            odr_pfr_time VARCHAR(2) NOT NULL,
            odr_postcode INT(8) NOT NULL,
            odr_status VARCHAR(100) NOT NULL,
            PRIMARY KEY (odr_id),
            FOREIGN KEY (odr_cust_id) REFERENCES $this->customers_tbl(cust_id)
        ) COLLATE = 'utf8_general_ci', ENGINE = 'InnoDB';";

        $products_table = "CREATE TABLE $this->products_tbl (
            prod_id INT(5) NOT NULL AUTO_INCREMENT,
            prod_name VARCHAR(256) NOT NULL,
            prod_img VARCHAR(256) NOT NULL,
            prod_cat INT(5) NOT NULL,
            prod_desc LONGTEXT NOT NULL,
            prod_rate VARCHAR(10) NOT NULL,
            prod_width VARCHAR(50) NOT NULL,
            prod_height VARCHAR(50)NOT NULL,
            prod_length VARCHAR(50) NOT NULL,
            PRIMARY KEY (prod_id),
            FOREIGN KEY (prod_cat) REFERENCES $this->categories_tbl(cat_id) ON UPDATE CASCADE ON DELETE CASCADE
        ) COLLATE = 'utf8_general_ci', ENGINE = 'InnoDB';";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($categories_table);
        dbDelta($options_table);
        dbDelta($coupons_table);
        dbDelta($postcodes_table);
        dbDelta($customers_table);
        dbDelta($orders_table);
        dbDelta($products_table);
        
    }

}

new HireQuote;
