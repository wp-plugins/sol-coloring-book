<?php
/*
Plugin Name:    Sol Coloring Book
Plugin URI:     http://www.mysummersol.com/wp-plugin-coloring-book/
Description:    A Coloring Book for SVG's
Version:        1.1
Author:         Dave Graham
Author URI:     http://www.mysummersol.com 
License:        Copyright 2015 by Dave Graham
Copyright       2015  (email : davidg@mysummersol.com)

 */
  
if ( ! class_exists('colorbook')) {
    global $sol_db_version,$wpdb;
     
    class colorbook {
            
        function sol_form() {
            include('admin/colorbook_form.php');
        }
        function sol_settings() {
            include('admin/settings.php');
        }
        function sol_faq() {
            include('admin/faq.php');
        }

        function menu() {
            add_menu_page('SOL Colorbook Options', 'SOL Coloring Book', 'moderate_comments', 'colorbook_slug', array($this, 'sol_form'), plugins_url('/assets/images/favicon.png', __FILE__));
            add_submenu_page( 'colorbook_slug', 'Settings', 'Settings', 'manage_options', 'colorbook_settings_slug', array($this, 'sol_settings'));
            add_submenu_page( 'colorbook_slug', 'FAQs', 'FAQs', 'manage_options', 'colorbook_FAQ_slug', array($this, 'sol_faq'));
        }

        function shortcode($atts) {
            global $wpdb;
            extract( shortcode_atts( array(
                //'active' => ''            //displays 'active' colorpages by == '1' or '0' -but default is '1'
            ), $atts ) );
            
            //active is an enum (y,n), colorpageurl(file location) and colorpagename(the label) are varchar
            $arr_pages = $wpdb->get_results("SELECT colorpagename FROM sol_colorpages WHERE active=1",ARRAY_A);
                //var_dump($arr_pages);//****
 
            if ( ! $current_colorpage = $_REQUEST['colorpage']) {//if a page isn't specified by click, then default to the first one
                $current_colorpage = $arr_pages[0]['colorpagename'];
            }
            if ($current_colorpage != false) {
                $arr_pagedata = $wpdb->get_row("SELECT colorpageurl FROM sol_colorpages WHERE active=1 AND colorpagename='".$current_colorpage."'",ARRAY_A);
                //var_dump($arr_pagedata);//*****
                extract($arr_pagedata);//produces $colorpageurl with its data of the current page
                $html='';
            }
            if ( ! empty($colorpageurl) && $current_colorpage != false) {
                require(plugin_dir_path(__FILE__)."/show_shortcode.php");
            } else {
                $html='Sorry, there are no coloring book pages available.';
            }
            
            $html=str_replace("\n","",$html);
            return $html;
        }

        function load_scripts() {
            wp_enqueue_script('jquery');
            //wp_enqueue_script('jquery-ui-core',array('jquery'));
            wp_enqueue_script('coloring_book', plugins_url('/assets/js/coloring_book.js', __FILE__), array('jquery'), '1.0', true);

            //styles
            wp_register_style('sol-colorbook-css',plugins_url('/assets/css/style.css', __FILE__));
            wp_enqueue_style('sol-colorbook-css');
            //need this via wp_head to pull the plugin_url in these specific styles
            add_action('wp_head','hook_css');
            function hook_css(){
                $output="<style type=\"text/css\">
    input.marker {background-image: url('".plugins_url('/assets/images/marker.png', __FILE__)."'); }
    input.brush {background-image: url('".plugins_url('/assets/images/brush.png', __FILE__)."'); }
    input.crayon {background-image: url('".plugins_url('/assets/images/crayon.png', __FILE__)."'); }
    input.pencil {background-image: url('".plugins_url('/assets/images/pencil.png', __FILE__)."'); }</style>";
                echo $output;
            }
        }
        
        public static function sol_activate() {
            global $wpdb,$sol_db_version;
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            //#rem: must have 1 field per line, 2 spaces between PRIMARY KEY and definition
            //#must use KEY rather than INDEX and it must include at least 1 KEY
            //#must NOT use any apostrophes or backticks around field names
            $sql = file_get_contents(plugin_dir_path(__FILE__).'admin/colorbook.sql');
            dbDelta( $sql );
            $wpdb->insert('sol_colorpages', 
                array('colorpagefile'=>plugins_url('/assets/images/beach.svg', __FILE__),'colorpageurl'=>plugins_url('/assets/images/beach.svg', __FILE__),'colorpagename'=>'Beach','active'=>1), 
                array('%s','%s','%s','%d')
            );
            $wpdb->insert('sol_colorpages', 
                array('colorpagefile'=>plugins_url('/assets/images/family.svg', __FILE__),'colorpageurl'=>plugins_url('/assets/images/family.svg', __FILE__),'colorpagename'=>'Family Beach','active'=>1), 
                array('%s','%s','%s','%d')
            );
            $wpdb->insert('sol_colorpages', 
                array('colorpagefile'=>plugins_url('/assets/images/waves.svg', __FILE__),'colorpageurl'=>plugins_url('/assets/images/waves.svg', __FILE__),'colorpagename'=>'Waves','active'=>1), 
                array('%s','%s','%s','%d')
            );

            add_option( "sol_db_version", $sol_db_version );
            update_option( "sol_db_version", $sol_db_version );
        }
        
        public static function sol_deactivate() {
            global $wpdb;
            $arr_tables = array("sol_colorpages");
            foreach ($arr_tables as $table_name) {
                $sql = "DROP TABLE IF EXISTS $table_name;";
                $wpdb->query($sql);
            }
            delete_option("sol_db_version");
        }

    }
}
register_activation_hook(__FILE__, array('colorbook', 'sol_activate'));
register_deactivation_hook(__FILE__, array('colorbook', 'sol_deactivate'));

$colorbook = new colorbook();
add_action('admin_menu', array($colorbook,'menu'));
add_shortcode('colorbook', array($colorbook, 'shortcode'));//[colorbook]
add_action('init', array($colorbook,'load_scripts'));

function myplugin_update_db_check() {
    global $sol_db_version,$colorbook;
    if (get_site_option( 'sol_db_version' ) != $sol_db_version) {
        $colorbook->install_data();//*******
    }
}
add_action( 'plugins_loaded', 'myplugin_update_db_check' );
function save_error(){
    update_option('plugin_error',  ob_get_contents());
}
add_action('activated_plugin','save_error');

?>
