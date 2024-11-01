<?php

/**
 * Description of YumJamMasonryInstall
 *
 * @author Matt
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'YumJamMasonryInstall' ) ) :

class YumJamMasonryInstall {
    /**
     * Install YumJam Masonry.
     */
    public static function install() {
        //self::create_options();
        
        //include custom post type classes here
        include_once YJ_MASONRY_PLUGIN_PATH . "/class/masonry_custom_cpt.php";
                  
        // Clear the permalinks after the post type has been registered
        flush_rewrite_rules();
        
    }

    public static function uninstall() {
        //self::remove_options();

        // Clear the permalinks
        flush_rewrite_rules();
    }
    
    /**
     * plugin activated perform installation and setup 
     */
    private static function create_options() {
        //add all options/settings and default values to wp_options db table
        /*
        foreach (YJM()->settings as $s) {
            add_option($s['id'], $s['default']);
        }
        */
    }
    
    /**
     * plugin activated perform installation and setup 
     */
    private static function remove_options() {
        //tidy up options/settings and default values from wp_options db table
        /*
        foreach (YJM()->settings as $s) {
            delete_option($s['id']);
        }
        */
    }    
}

endif;