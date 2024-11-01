<?php

/**
 * YumJam Masonry Layout for Posts and Custom Post Types
 *
 * @package     YumJamMasonry
 * @author      Matt Burnett, Thomas W
 * @copyright   2018 YumJam
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: YumJam Masonry Layout for Posts and Custom Post Types
 * Plugin URI: https://www.yumjam.co.uk/yumjam-wordpress-plugins/wp-masonry-plugin/
 * Description: Easily display Wordpress posts or custom post type entries within a tidy masonry style wall layout.
 * Version: 0.8.7
 * Author: YumJam
 * Author URI: https://www.yumjam.co.uk
 * Text Domain: yumjam-masonry-layout-for-posts-and-custom-post-types
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Tags: masonry, wall, post display, brick layout, wall layout, blog layout, shortcode, pintrest, news layout, lazy loading, ajax loading
 * Requires at least: 4.0
 * Tested up to: 4.9.7
 * Stable tag: 4.9.7
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('YumJamMasonry')) :

    /**
     * Main YumJam Masonry Class
     * 
     */
    final class YumJamMasonry {

        private $slug = 'yumjam_masonry';
        private $ver = '0.8.5';
        protected static $_instance = null;
        protected $prefix = 'yj_';
        public $settings = array(); //Default settings
        public $options = array(); //Current options

        /**
         * Singleton self instantiation
         * @return type
         */
        public static function instance() {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        private function define_constants() {
            define('YJ_MASONRY_PLUGIN_PATH', __DIR__);
            define('YJ_MASONRY_PLUGIN_URL', plugin_dir_url(__FILE__));
        }

        public function __construct() {
            $this->define_constants();
            $this->setup_plugin_options();

            $this->includes();
            $this->init_hooks();

            //Frontend Scripts
            add_action('wp_enqueue_scripts', array($this, 'yj_frontend_scripts'));

            //Ajax hooks
            add_action('wp_ajax_nopriv_more_post_ajax', array($this, 'more_post_ajax'));
            add_action('wp_ajax_more_post_ajax', array($this, 'more_post_ajax'));

            add_action('wp_ajax_nopriv_yj_defaults', array($this, 'option_defaults'));
            add_action('wp_ajax_yj_defaults', array($this, 'option_defaults'));

            //Admin 
            if (is_admin()) {
                $this->admin_hooks();
            }
        }

        /**
         * Initialisation hooks
         */
        private function init_hooks() {

            add_action('after_setup_theme', array($this, 'custom_theme_setup'));

            add_filter( 'bulk_actions-edit-post', array($this, 'masonry_bulk_actions' ));
            add_filter( 'handle_bulk_actions-edit-post', array($this, 'masonry_bulk_actions_handler'), 10, 3 );
            
            //ON WP Init
            add_action('init', array($this, 'init'), 0);
        }
        /**
        * Adds a new item into the Bulk Actions dropdown.
        */
        public function masonry_bulk_actions($bulk_actions) {
            $bulk_actions['enable_masonry'] = __( 'Enable in masonry', 'enable_masonry');
            return $bulk_actions;
        }        
        
        public function masonry_bulk_actions_handler( $redirect_to, $action, $post_ids ) {
            if ( $action !== 'enable_masonry' ) {
                    return $redirect_to;
            }

            foreach ( $post_ids as $post_id ) {
                update_post_meta($post_id, 'custom_enabled', 'on');
            }

            $redirect_to = add_query_arg( 'bulk_enable_masonry', count( $post_ids ), $redirect_to );

            return $redirect_to;
        }

        /**
         * On Wp Init
         */
        public function init() {
            load_plugin_textdomain('yumjam-masonry', false, dirname(plugin_basename(__FILE__)) . '/lang');

            //Shortcodes
            add_shortcode('yumjam-masonry', array($this, 'yumjam_masonry_function'));

        }

        protected function setup_plugin_options() {
            // Populate Array of Setting to add to the settings page
            // array('id', 'tab', 'type', 'name', 'default', 'values')
            $this->settings = array(
                //Main Options
                'break1' => array('type' => 'html', 'value' => 'Layout Options', 'tab' => 'main_options'),
                'post_types' => array('id' => 'post_types', 'tab' => 'main_options', 'type' => 'multi-select', 'name' => 'Choose post types', 'default' => array('custom'), 'values' => 'callback', 'desc' => 'choose all the post types you would like to see in this Masonry Wall ( default : custom )'),
                'post_cats' => array('id' => 'post_cats', 'tab' => 'main_options', 'type' => 'multi-select', 'name' => 'Filter by category', 'default' => '', 'values' => 'callback', 'desc' => 'choose all the categories you would like to see in this Masonry Wall ( default : all )'),
                'brick_layout' => array('id' => 'brick_layout', 'tab' => 'main_options', 'type' => 'radio', 'name' => 'Pre-Built Brick Layouts', 'default' => '6:6:3:6:3:4:4:4', 'values' => 'callback'),
                'default_per_page' => array('id' => 'default_per_page', 'tab' => 'main_options', 'type' => 'ui-slider', 'name' => 'Default items per page', 'default' => '10', 'values' => array('min' => 1, 'max' => 50), 'desc' => 'how many post per page load?'),
                'default_column_width' => array('id' => 'default_column_width', 'tab' => 'main_options', 'type' => 'ui-slider', 'name' => 'Default Column width (1 - 12)', 'default' => '5', 'values' => array('min' => 3, 'max' => 12)),
                'default_gap_fill' => array('id' => 'default_gap_fill', 'tab' => 'main_options', 'type' => 'checkbox', 'name' => 'Fill gaps', 'default' => 'on', 'desc' => 'intelligently fill the gaps between odd sized bricks'),
                'page_background_color' => array('id' => 'page_background_color', 'tab' => 'main_options', 'type' => 'colour-picker', 'name' => 'Mortar colour', 'default' => '#ffffff', 'desc' => 'colour of the gap between bricks'),
                'block_spacing' => array('id' => 'block_spacing', 'tab' => 'main_options', 'type' => 'ui-slider', 'name' => 'Mortar size', 'default' => '15', 'values' => array('min' => 0, 'max' => 50), 'desc' => 'size of the gap between bricks e.g.( mortar ) '),
                'gap_background_color' => array('id' => 'gap_background_color', 'tab' => 'main_options', 'type' => 'colour-picker', 'name' => 'Background colour', 'default' => '#ffffff', 'desc' => 'masonry wall background colour'),
                'break2' => array('type' => 'html', 'value' => 'Other Options', 'tab' => 'main_options'),
                //'brick_debug' => array('id' => 'brick_debug', 'tab' => 'main_options', 'type' => 'checkbox', 'name' => 'Brick Debug', 'default' => 'off'),
                'masonry_id' => array('id' => 'masonry_id', 'tab' => 'main_options', 'type' => 'textbox', 'name' => 'Masonry ID', 'default' => '1', 'desc' => 'change this if you plan to have more than 1 masonry on a page'),
                //Load More
                'break3' => array('type' => 'html', 'value' => 'Brick Load Type', 'tab' => 'postload_options'),
                'load_type' => array('id' => 'load_type', 'tab' => 'postload_options', 'type' => 'radio', 'name' => 'Lazy Load / Button / No Loading', 'default' => 'button', 'values' => array('button' => 'Button', 'lazy' => 'Lazy Load', 'none' => 'No Loading'), 'desc' => 'how will the next page of bricks be loaded?'),
                'break4' => array('type' => 'html', 'value' => 'Button options', 'tab' => 'postload_options'),
                'load_more_text' => array('id' => 'load_more_text', 'tab' => 'postload_options', 'type' => 'textbox', 'name' => 'Text', 'default' => 'LOAD MORE', 'desc' => 'the text displayed on the load more button'),
                'button_font_size' => array('id' => 'button_font_size', 'tab' => 'postload_options', 'type' => 'textbox', 'name' => 'Font size', 'default' => '16px', 'desc' => 'font size including unit e.g. px, em, pt'),
                'button_font_color' => array('id' => 'button_font_color', 'tab' => 'postload_options', 'type' => 'colour-picker', 'name' => 'Text colour', 'default' => '#ffffff', 'desc' => 'colour of the button text (click to pick)'),
                'button_padding' => array('id' => 'button_padding', 'tab' => 'postload_options', 'type' => 'textbox', 'name' => 'Padding', 'default' => '16px', 'desc' => 'padding size include unit e.g. px, %'),
                'button_background' => array('id' => 'button_background', 'tab' => 'postload_options', 'type' => 'colour-picker', 'name' => 'Background colour', 'default' => '#bdb99b', 'desc' => 'colour of the button (click to pick)'),
                //Content Options
                'break5' => array('type' => 'html', 'value' => 'Brick Content Options', 'tab' => 'brick_content'),
                'content_link_content' => array('id' => 'content_link_content', 'tab' => 'brick_content', 'type' => 'checkbox', 'name' => 'Link Content', 'default' => 'on', 'desc' => 'Link Content'),
                'content_show_excerpt' => array('id' => 'content_show_excerpt', 'tab' => 'brick_content', 'type' => 'checkbox', 'name' => 'Shows post excerpt', 'default' => 'off', 'desc' => 'use the post excerpt text instead of post body'),
                'content_over_image' => array('id' => 'content_over_image', 'tab' => 'brick_content', 'type' => 'checkbox', 'name' => 'Overlay image?', 'default' => 'off', 'desc' => 'content overlays image'),
                'content_image_is_link' => array('id' => 'content_image_is_link', 'tab' => 'brick_content', 'type' => 'checkbox', 'name' => 'Image is a Link?', 'default' => 'off', 'desc' => 'should clicking the image linke to the post item'),
                'content_show_excerpt_words' => array('id' => 'content_show_excerpt_words', 'tab' => 'brick_content', 'type' => 'ui-slider', 'name' => 'Maximum words in excerpt', 'default' => '500', 'values' => array('min' => 1, 'max' => 200), 'desc' => 'limit excerpt to a max words'),
                'content_use_featured' => array('id' => 'content_use_featured', 'tab' => 'brick_content', 'type' => 'checkbox', 'name' => 'Use featured image', 'default' => 'on', 'desc' => 'use the posts featured image as background (if it is available)'),
                'content_minimum_height' => array('id' => 'content_minimum_height', 'tab' => 'brick_content', 'type' => 'textbox', 'name' => 'Minimum height', 'default' => '280px', 'desc' => 'bricks content will be at least this high, include unit e.g. px, %'),
                'content_font_size' => array('id' => 'content_font_size', 'tab' => 'brick_content', 'type' => 'textbox', 'name' => 'Font size', 'default' => '16px', 'desc' => 'content font size including unit e.g. px, em, pt'),
                'content_font_color' => array('id' => 'content_font_color', 'tab' => 'brick_content', 'type' => 'colour-picker', 'name' => 'Text colour', 'default' => '#ffffff', 'desc' => 'colour of the content text (click to pick)'),
                'content_padding' => array('id' => 'content_padding', 'tab' => 'brick_content', 'type' => 'textbox', 'name' => 'Padding', 'default' => '11px', 'desc' => 'padding size around the content, include unit e.g. px, %'),
                'background_color' => array('id' => 'background_color', 'tab' => 'brick_content', 'type' => 'colour-picker', 'name' => 'Background colour', 'default' => '#363730', 'desc' => 'colour behind the content text, may be overlayed by image if selected (click to pick)'),
                //'background_opacity' => array('id' => 'background_opacity', 'tab' => 'brick_content', 'type' => 'ui-slider', 'name' => 'Background opacity', 'default' => '100', 'values' => array('min' => 0, 'max' => 100), 'desc' => 'Background Opacity', 'desc' => 'colour of the content text (click to pick)'),
                'break6' => array('type' => 'html', 'value' => 'Alternate Style (every other)', 'tab' => 'brick_content'),
                'alternate_background_color' => array('id' => 'alternate_background_color', 'tab' => 'brick_content', 'type' => 'colour-picker', 'name' => 'Background colour', 'default' => '#363730', 'desc' => 'colour behind the alternate content text (click to pick)'),
                //Brick Header
                'break7' => array('type' => 'html', 'value' => 'Header Styling', 'tab' => 'brick_header'),
                'header_show' => array('id' => 'header_show', 'tab' => 'brick_header', 'type' => 'checkbox', 'name' => 'Show header?', 'default' => 'on', 'desc' => 'Hides header block'),
                'header_position' => array('id' => 'header_position', 'tab' => 'brick_header', 'type' => 'radio', 'name' => 'Header Position', 'default' => 'top', 'values' => array('top' => 'Above', 'bottom' => 'Below'), 'desc' => 'Header above or below content / image?'),
                'header_padding' => array('id' => 'header_padding', 'tab' => 'brick_header', 'type' => 'textbox', 'name' => 'Padding', 'default' => '11px', 'desc' => 'space around title including unit e.g. px'),
                'header_background_color' => array('id' => 'header_background_color', 'tab' => 'brick_header', 'type' => 'colour-picker', 'name' => 'Background colour', 'default' => '#00c5df', 'desc' => 'color of the main header (click to pick)'),
                'break9' => array('type' => 'html', 'value' => 'Brick Title', 'tab' => 'brick_header'),
                'content_link_title' => array('id' => 'content_link_title', 'tab' => 'brick_header', 'type' => 'checkbox', 'name' => 'Link Title', 'default' => 'on', 'desc' => 'Link Title'),
                'content_show_title_words' => array('id' => 'content_show_title_words', 'tab' => 'brick_header', 'type' => 'ui-slider', 'name' => 'Maximum words', 'default' => '5', 'values' => array('min' => 1, 'max' => 50), 'desc' => 'Maximum number of words in title'),
                'header_font_size' => array('id' => 'header_font_size', 'tab' => 'brick_header', 'type' => 'textbox', 'name' => 'Font size', 'default' => '20px', 'desc' => 'size of the title text including unit e.g. px, em, pt'),
                'header_font_color' => array('id' => 'header_font_color', 'tab' => 'brick_header', 'type' => 'colour-picker', 'name' => 'Text colour', 'default' => '#ffffff', 'desc' => 'colour of the title text (click to pick)'),
                'header_font_uppercase' => array('id' => 'header_font_uppercase', 'tab' => 'brick_header', 'type' => 'checkbox', 'name' => 'Uppercase?', 'default' => 'on', 'desc' => 'force title tex to be upper case'),
                'header_spacing' => array('id' => 'header_spacing', 'tab' => 'brick_header', 'type' => 'textbox', 'name' => 'Spacing', 'default' => '8px', 'desc' => 'space between title, date and author (include unit e.g. px)'),
                //Alternate
                'break8' => array('type' => 'html', 'value' => 'Alternate Header Styling (every other)', 'tab' => 'brick_header'),
                'alternate_header_background_color' => array('id' => 'alternate_header_background_color', 'tab' => 'brick_header', 'type' => 'colour-picker', 'name' => 'Background colour', 'default' => '#bdb99b', 'desc' => 'colour of the alternate header (click to pick)'),
                'alternate_header_font_color' => array('id' => 'alternate_header_font_color', 'tab' => 'brick_header', 'type' => 'colour-picker', 'name' => 'Text colour', 'default' => '#ffffff', 'desc' => 'colour of the alternate text (click to pick)'),
                //Post Author
                'break10' => array('type' => 'html', 'value' => 'Author', 'tab' => 'brick_header'),
                'content_show_author' => array('id' => 'content_show_author', 'tab' => 'brick_header', 'type' => 'checkbox', 'name' => 'Enable', 'default' => 'off', 'desc' => 'show the post author in the header'),
                'header_author_font_size' => array('id' => 'header_author_font_size', 'tab' => 'brick_header', 'type' => 'textbox', 'name' => 'Font size', 'default' => '12px', 'desc' => 'font size including unit e.g. px, em, pt'),
                'header_author_font_color' => array('id' => 'header_author_font_color', 'tab' => 'brick_header', 'type' => 'colour-picker', 'name' => 'Text colour', 'default' => '#ffffff', 'desc' => 'colour of the button text (click to pick)'),
                //Post Data
                'break11' => array('type' => 'html', 'value' => 'Date', 'tab' => 'brick_header'),
                'content_show_date' => array('id' => 'content_show_date', 'tab' => 'brick_header', 'type' => 'checkbox', 'name' => 'Enable', 'default' => 'on', 'desc' => 'show the post date in the header'),
                'header_date_font_size' => array('id' => 'header_date_font_size', 'tab' => 'brick_header', 'type' => 'textbox', 'name' => 'Font size', 'default' => '12px', 'desc' => 'font size including unit e.g. px, em, pt'),
                'header_date_font_color' => array('id' => 'header_date_font_color', 'tab' => 'brick_header', 'type' => 'colour-picker', 'name' => 'Text colour', 'default' => '#ffffff', 'desc' => 'colour of the text (click to pick)'),
                //Brick Footer 
                //Read More
                'break12' => array('type' => 'html', 'value' => 'Brick Footer Options', 'tab' => 'brick_footer'),
                'content_show_readmore' => array('id' => 'content_show_readmore', 'tab' => 'brick_footer', 'type' => 'checkbox', 'name' => 'Shows read more', 'default' => 'on'),
                'content_show_readmore_bold' => array('id' => 'content_show_readmore_bold', 'tab' => 'brick_footer', 'type' => 'checkbox', 'name' => 'Bold read more', 'default' => 'on'),
                'content_show_readmore_icon' => array('id' => 'content_show_readmore_icon', 'tab' => 'brick_footer', 'type' => 'textbox', 'name' => 'Read more icon', 'default' => 'fa-eye', 'desc' => 'fontawesome icon to display before the text, e.g. fa-rocket, fa-wifi'),                
                'content_show_readmore_text' => array('id' => 'content_show_readmore_text', 'tab' => 'brick_footer', 'type' => 'textbox', 'name' => 'Read more text', 'default' => 'READ MORE'),
                'content_show_readmore_font_size' => array('id' => 'content_show_readmore_font_size', 'tab' => 'brick_footer', 'type' => 'textbox', 'name' => 'Read more font size', 'default' => '10px', 'desc' => 'include unit e.g. px'),
                'content_show_readmore_font_color' => array('id' => 'content_show_readmore_font_color', 'tab' => 'brick_footer', 'type' => 'colour-picker', 'name' => 'Read more font colour', 'default' => '#5d5b35'),
                'content_show_readmore_background' => array('id' => 'content_show_readmore_background', 'tab' => 'brick_footer', 'type' => 'colour-picker', 'name' => 'Background colour of read more', 'default' => '#ffffff'),
                'content_show_readmore_padding' => array('id' => 'content_show_readmore_padding', 'tab' => 'brick_footer', 'type' => 'textbox', 'name' => 'Read more padding', 'default' => '10px'),
                'content_show_readmore_alignment' => array('id' => 'content_show_readmore_alignment', 'tab' => 'brick_footer', 'type' => 'radio', 'name' => 'Read More Alignment', 'default' => 'left', 'values' => array('left' => 'Left', 'right' => 'Right')),
            );

            foreach ($this->settings as $setting) {
                if ($setting['type'] != 'html') {
                    $this->options[$setting['id']] = $setting['default'];
                }
            }
            
        }

        public function option_defaults() {
            wp_send_json_success($this->options);
        }

        /**
         * Include required core files used in admin and on the frontend.
         */
        public function includes() {
            //Custom post types
            include_once YJ_MASONRY_PLUGIN_PATH . "/class/masonry_custom_cpt.php";

            //Installation and Activation stuff
            include_once YJ_MASONRY_PLUGIN_PATH . "/class/installer.php";
        }

        /**
         * CSS options set in backend, output in wp_head
         */
        public function dynamic_custom_css($atts) {
            $css_options = array();
            $css_tags = array(
                0 => '@background_color',
                //1 => '@background_opacity',
                2 => '@content_font_size',
                3 => '@content_font_color',
                4 => '@content_padding',
                5 => '@content_minimum_height',
                6 => '@content_show_readmore_padding',
                7 => '@content_show_readmore_background',
                8 => '@content_show_readmore_font_size',
                9 => '@content_show_readmore_font_color',
                10 => '@content_show_readmore_alignment',
                11 => '@content_show_readmore_bold',
                12 => '@block_spacing',
                13 => '@page_background_color',
                14 => '@gap_background_color',
                15 => '@header_background_color',
                16 => '@header_font_size',
                17 => '@header_font_color',
                18 => '@header_font_uppercase',
                19 => '@header_padding',
                20 => '@header_spacing',
                21 => '@header_author_font_size',
                22 => '@header_author_font_color',
                23 => '@header_date_font_size',
                24 => '@header_date_font_color',
                25 => '@alternate_header_background_color',
                26 => '@alternate_header_font_color',
                27 => '@alternate_background_color',
                28 => '@button_font_size',
                29 => '@button_font_color',
                30 => '@button_padding',
                31 => '@button_background',
               // 32 => '@block_spacing_tr',
               // 33 => '@block_spacing_bl',
                34 => '@header_show',
                35 => '@masonry_id',
                36 => '@content_over_image',
                37 => '@content_link_content',
                //35 => '@header_position'
            );
          
            foreach ($css_tags as $value) {
                $tag_name = substr($value, 1);

                if (!empty($atts[$tag_name])) {
                    //use shortcode val
                    $css_options[$tag_name] = $atts[$tag_name];
                } else {
                    //not set
                    $css_options[$tag_name] = $this->options[$tag_name];
                }
            }

            //header_show
            if (!empty($css_options['header_show']) && $css_options['header_show'] == 'on') {
                $css_options['header_show'] = "block";
            } else {
                $css_options['header_show'] = "none";
            }

            //split block spacing top-right and bottom-left
           
            if (!empty($css_options['block_spacing']) || $css_options['block_spacing'] =="0") {
                $bs = $css_options['block_spacing'];
                $css_options['block_spacing_tr'] = round($bs / 2, 0, PHP_ROUND_HALF_DOWN) . "px";
                $css_options['block_spacing_bl'] = round($bs / 2, 0, PHP_ROUND_HALF_UP) . "px";
                unset($css_tags[12]);
                unset($css_options['block_spacing']);
                $css_tags[32] = '@block_spacing_tr';
                $css_tags[33] = '@block_spacing_bl';
            }

            //header_font_uppercase
            if (!empty($css_options['header_font_uppercase']) && $css_options['header_font_uppercase'] == 'on') {
                $css_options['header_font_uppercase'] = "uppercase";
            } else {
                $css_options['header_font_uppercase'] = "normal";
            }

            //readmore bold
            if (!empty($css_options['content_show_readmore_bold']) && $css_options['content_show_readmore_bold'] == 'on') {
                $css_options['content_show_readmore_bold'] = "bold";
            } else {
                $css_options['content_show_readmore_bold'] = "normal";
            }

            //background opacity
            /*if (!empty($css_options['background_opacity'])) {
                $css_options['background_opacity'] = $css_options['background_opacity'] / 100;
            } else {
                $css_options['background_opacity'] = 100;
            }*/

            $css = file_get_contents(YJ_MASONRY_PLUGIN_PATH . '/css/dynamic.less');

            $built_css = str_replace($css_tags, $css_options, $css);

            return '<style type="text/css">' . $built_css . '</style>';
        }

        /**
         * Force post-format support for themes that do not declare support for it
         */
        public function custom_theme_setup() {
            add_theme_support('post-formats', array('image', 'video', 'gallery', 'quote'));
            add_post_type_support('post', 'post-formats');
            add_post_type_support('custom', 'post-formats');
        }

        /**
         * Values for post type setting select list callback
         * @return array
         */
        public function post_types_values() {
            $cpts = get_post_types(array('_builtin' => false, 'public' => true));
            $cpts[] = 'post';
            return $cpts;
        }

        /**
         * Values for post cats setting select list callback
         * @return array
         */
        public function post_cats_values() {
            $out = array();
            $other_tax = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
            $category = get_taxonomies(array('name'=>'category'), 'objects');
            $taxonomies = array_merge($other_tax, $category);
            foreach ($taxonomies as $tax) {
                $terms = get_terms(array(
                    'taxonomy' => $tax->name,
                    'hide_empty' => false,
                        ));
                if (count($terms) > 0) {
                    $out[$tax->name] = array('name' => $tax->name, 'post_type' => $tax->object_type[0]);
                    foreach ($terms as $term) {
                        $out[$tax->name]['terms'][$term->term_id] = $term->name;
                    }
                }
            }
            return $out;
        }

        /**
         * Radio button values for pre-build brick layout radio buttons callback
         * @return array
         */
        public function brick_layout_values() {
            return array(
                'custom' => 'Custom Layout',
                '6:3:3:4:4:4:3:3:6' => 'Style-1',
                '3:3:3:3' => 'Style-2',
                '6:6:3:3:3:3:6:6:3:3:3:3' => 'Style-3',
                '8:4:4:8:7:5' => 'Style-4',
                '6:6:3:6:3:4:4:4' => 'Style-5',
                '6:3:3:3:6:3:3:6:3' => 'Style-6',
                '12:12' => 'Style-7',
                '8:2:2:6:2:2:2:5:5:2:6:2:2:2:2:6:2:2:2:2:2:2:2:2' => 'Style-8',
                '8:4:4:4:4:4:4:4:4:4:4' => 'Style-9',
                '9:3:9:3:9:3' => 'Style-10',
                '6:6' => 'Style-11',
            );
        }

        /**
         * Hooks only actioned on admin login
         */
        public function admin_hooks() {
            add_action('admin_init', array($this, 'yj_admin_init'));
            add_action('admin_enqueue_scripts', array($this, 'yj_backend_scripts'));
            add_action('admin_menu', array($this, 'yj_register_menu_page'));

            //Quick Edit
            add_filter('manage_custom_posts_columns', array($this, 'manage_custom_posts_columns'));
            add_filter('manage_edit-custom_columns', array($this, 'remove_dummy_column'));
            add_action('bulk_edit_custom_box', array($this, 'on_bulk_edit_custom_box'), 10, 2);
        }

        function manage_custom_posts_columns($columns) {
            $columns['custom_do_not_show'] = esc_html__('Hide in Masonry');
            return $columns;
        }
        
        function remove_dummy_column($columns){
            unset($columns['custom_do_not_show']);
            return $columns;
        }
        
        function on_bulk_edit_custom_box($column_name, $post_type){
            if ('custom_do_not_show' == $column_name) {
                echo esc_html__('Hide in Masonry');
            }
        }
        
        
        /**
         * doing admin stuff - initialise
         */
        public function yj_admin_init() {
            $this->configure_settings_options();
            add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'plugin_action_links'));
        }

        /**
         * Add extra links to pluigns page, by active/deactivate link
         * @param type $links
         * @return string
         */
        public function plugin_action_links($links) {
            $links[] = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=' . $this->slug)) . '">Settings</a>';
            $links[] = '<a href="http://www.yumjam.co.uk" target="_blank">More by YumJam</a>';
            $links[] = '<a href="https://www.yumjam.co.uk/docs/masonry-plugin/" target="_blank">Help Docs</a>';

            return $links;
        }

        /**
         * Output the html to generate setting/options input boxes
         * @param type $args
         */
        public function yj_output_settings_field($args) {
            $html = '';
            if (!empty($args['values'])) {
                if ($args['values'] == 'callback') {
                    $values = call_user_func(array($this, $args['id'] . '_values'));
                } else if (is_array($args['values'])) {
                    $values = $args['values'];
                }
            }

            switch ($args['type']) {
                case 'textbox':
                    $html .= "<input type='text' id='{$args['id']}' name='{$args['id']}' class='tab-{$args['tab']}' value='{$this->settings[$args['id']]['default']}' />";
                    break;
                case 'ui-slider':
                    $html .= "<div class='setting-slider' data-min='{$values['min']}' data-max='{$values['max']}' data-value='{$this->settings[$args['id']]['default']}' style='width:172px;'>{$values['min']}<div style='float:right;'>{$values['max']}</div><div id='{$args['id']}_slide' class='ui-slider'></div>";
                    $html .= "<input type='text' id='{$args['id']}' name='{$args['id']}' class='tab-{$args['tab']}' value='{$this->settings[$args['id']]['default']}' size='2' style='float:left' /></div>";
                    break;
                case 'colour-picker':
                    $html .= "<input type='text' id='{$args['id']}' name='{$args['id']}' class='tab-{$args['tab']} colour-picker' value='{$this->settings[$args['id']]['default']}' />";
                    break;
                case 'checkbox':
                    $html .= "<input type='checkbox' id='{$args['id']}' name='{$args['id']}' class='tab-{$args['tab']}' value='on'" . checked('on', $this->settings[$args['id']]['default'], false) . "/>";
                    //$html .= "<label for='{$args['id']}'></label>";                    
                    break;
                case 'radio':
                    $val = $this->settings[$args['id']]['default'];
                    $html .= "<div class='help-{$args['id']} help-container'></div>";
                    foreach ($values as $value => $label) {
                        $html .= "<div id='radio-{$label}' class='{$args['id']}'> <input type='radio' id='{$args['id']}' name='{$args['id']}' class='tab-{$args['tab']}' value='{$value}' " . checked($val, $value, false) . " />{$label}</div>";
                    }
                    break;
                case 'media-select':
                    $html .= "<input type='text' id='{$args['id']}' name='{$args['id']}' value='{$this->settings[$args['id']]['default']}' class='regular-text tab-{$args['tab']}' />";
                    $html .= "<input type='button' name='{$this->prefix}media_select' id='yj_media_select' class='button-secondary tab-{$args['tab']}' value='Choose Logo' / >";
                    break;
                case 'multi-select':
                    $html .= "<select multiple='true' class='chosen tab-{$args['tab']}' id='{$args['id']}' name='{$args['id']}[]' style='width:200px;'>";

                    foreach ($values as $value) {
                        if (!empty($value['terms']) && is_array($value['terms'])) {
                            !empty($value['post_type'])?$pt=$value['post_type']:$pt='';
                            $html .= "<optgroup label=\"{$value['name']}\" data-post_type=\"{$pt}\">";
                            foreach ($value['terms'] as $term_id => $term_name) {
                                $selected = '';
                                $val = $this->settings[$args['id']]['default'];
                                if (!empty($val)) {
                                    $selected = in_array($term_id, $val) ? ' selected="selected"' : '';
                                }
                                $html .= "<option value='{$term_id}'{$selected}>{$term_name}</option>";
                            }
                            $html .= "</optgroup>";
                        } else {
                            $selected = '';
                            $val = $this->settings[$args['id']]['default'];
                            if (!empty($val)) {
                                $selected = in_array($value, $val) ? ' selected="selected"' : '';
                            }
                            $html .= "<option value='{$value}'{$selected}>{$value}</option>";
                        }
                    }
                    $html .= "</select>";
                    break;
            }
            $html .= "<p class='description' id='tagline-description'>";
            if (!empty($args['desc'])) {
                $html .= "{$args['desc']}";
            }
            if (is_string($args['default'])) {
                $html .= " ( default : {$args['default']} )";
            }
            $html .= "</p>";

            echo $html;
        }

        /**
         * Load plugins CSS and JSS on site frontend view
         */
        public function yj_frontend_scripts() {
            global $wp_query;

            /* CSS */
            wp_enqueue_style('bootstrap-style', YJ_MASONRY_PLUGIN_URL . 'css/bootstrap.min.css');
            wp_enqueue_style('fontawesome-front-style', YJ_MASONRY_PLUGIN_URL . 'css/font-awesome.min.css');
            wp_enqueue_style('masonry-front-style', YJ_MASONRY_PLUGIN_URL . 'css/front.css');
            //wp_enqueue_style('masonry-dynamic-style', YJ_MASONRY_PLUGIN_URL . 'css/dynamic.php');
            

            /* JS */
            wp_enqueue_script('masonry-front', YJ_MASONRY_PLUGIN_URL . 'js/front.js', array('jquery'), $this->ver, true);
            wp_enqueue_script('bootstrap-js', YJ_MASONRY_PLUGIN_URL . 'js/bootstrap.min.js', array('jquery'), '3.3.5', true);

            wp_localize_script('masonry-front', 'ajax_posts', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'yj_nonce' => wp_create_nonce("yj-posts_nonce"),
                'noposts' => 'No older posts found',
            ));
        }

        /**
         * Display column span count in the brick corner
         * @param type $cols
         * @param type $post_type
         * @return boolean
         */
        public function brick_debug($cols, $post_type) {
            if (!empty($this->options['brick_debug']) && $this->options['brick_debug'] == 'on') {
                echo "<div class='dev-cols {$post_type}' title='Post Type: {$post_type}' data-cols='{$cols}'>{$cols}</div>";
            }
            return false;
        }

        public function build_tax_query($terms) {
            $tax = $tq = array();
            if (strstr($terms, ",")) {
                $terms = explode(",", $terms);
            } else {
                $terms = array($terms);
            }

            foreach ($terms as $term) {
                $trm = get_term((int) $term); // 'field' => 'term_id'
                if (!empty($tax[$trm->taxonomy])) {
                    $tax[$trm->taxonomy]['terms'][] = $trm->term_id;
                } else {
                    $tax[$trm->taxonomy] = array(
                        'taxonomy' => $trm->taxonomy,
                        'field' => 'term_id',
                        'terms' => array($trm->term_id)
                    );
                }
            }

            if (count($tax) > 1) {
                $tq['relation'] = 'OR';
            }

            if (count($tax) > 0) {
                foreach ($tax as $filter) {
                    $tq[] = $filter;
                }
                return $tq;
            }

            return false;
        }

        /**
         * Shortcode [yumjam-masonry] Call back and Ajax response
         * 
         * @param type $atts
         */
        public function yumjam_masonry_function($atts, $ajax = false) {
            $out = '';
            $this->setup_plugin_options();
            if (!isset($atts["block_spacing"])){ $atts["block_spacing"] = "15"; }
            
            //combine default settings with attribs from shortcode and set $this current options
            foreach ($this->settings as $key => $value) {
                //if (!empty($atts[$key])) {
                    if (!empty($atts[$key]) || ($atts["block_spacing"]=="0" && $key=="block_spacing")) {
                        $this->options[$key] = $atts[$key];
                    }
                //}
            }

            //build wp_query args
            $args = array(
                'post_type' => $this->maybeExplode(',', $this->options['post_types']),
                'posts_per_page' => $this->options['default_per_page'],
                'paged' => empty($atts['paged']) ? '' : ($atts['paged']),
            );
            
            //Ignore the show hide check box if wp meta box is disabled
            if (get_option('yjm_disable_post_metabox') != 1) {
                $args['meta_query'] = array(
                    'relation' => 'OR',
                    array(
                        'key' => 'custom_do_not_show',
                        'value' => 'off',
                        'compare' => '=='
                    ),
                    array(
                        'key' => 'custom_do_not_show',
                        'compare' => 'NOT EXISTS', 
                        'value' => ''
                    )                    
                );
            }

            if (!empty($this->options['post_cats']) && $this->options['post_cats'] != 'all') {
                $tq = $this->build_tax_query($this->options['post_cats']);
                $args['tax_query'] = $tq;
            }

            //brick classes array
            $classes = array();
            if ($this->options['default_gap_fill'] == "on") {
                $classes[] = "gapfill";
            }

            if (!$ajax) {
                
                //"yukky" in-body <style>, how else can we do shortcode based dynamic styles?            
                $out .= $this->dynamic_custom_css($atts);
                $out .= "<div id='yj-posts-" . $this->options['masonry_id'] . "' class='yj-posts " . $this->maybeImplode(' ', $classes) . "' data-template={$this->options['brick_layout']} data-slug='" . $this->maybeImplode(',', $this->options['post_types']) . "' data-postsperpage='{$this->options['default_per_page']}' data-blockspacing='{$this->options['block_spacing']}' data-loadtype='{$this->options['load_type']}' data-disabled='false' data-pagenumber='1'>";
                $out .= "<div id='atts' class='atts' data-atts='' style='display:none'>" . json_encode($atts) . "</div>";
            }
            $query = new WP_Query($args);
            //bricks
            $out .= $this->handleQuery($query);

            if (!$ajax) {
                if ($this->options['load_type'] == "button") {
                    $out .= "</div><div id='yj-more-posts-" . $this->options['masonry_id'] . "' class='yj-more-posts'><a>{$this->options['load_more_text']}</a></div>";
                } else if($this->options['load_type'] == "lazy") {
                    $out .= "</div><div id='yj-more-posts-" . $this->options['masonry_id'] . "' class='yj-more-posts readmorehidden'><a>{$this->options['load_more_text']}</a></div>";
                } else {
                    $out .= "</div>";                    
                }
            }
            return $out;
        }

        private function maybeImplode($sep, $input) {
            if (!empty($input) && is_array($input)) {
                return implode($sep, $input);
            }
            return $input;
        }

        private function maybeExplode($sep, $input) {
            if (!empty($input) && !is_array($input)) {
                return explode($sep, $input);
            }
            return $input;
        }

        /**
         * Decide how many columns this brick should span
         * 
         * @param type $post_id
         * @param type $iteration
         * @return type
         */
        public function columnCount($post_id, $iteration) {
            $cols = get_post_meta($post_id, 'custom_columns', true);
            
            if (!$this->options['brick_layout'] == 'custom' || (!empty($cols) && $cols != '0')  ) {
                !empty($cols)? : $cols = $this->options['yj_default_column_width'];
                return array('count' => $cols, 'override' => true);
            } else if (!empty($this->options['brick_layout']) && strstr($this->options['brick_layout'], ':')) {
                $col_array = explode(':', $this->options['brick_layout']);
                $col_count = count($col_array);

                if (is_array($col_array)) {
                    //Handle pages here as iteration will be dependant on previous page count
                    if ($iteration >= $col_count) {
                        $iteration = $iteration % $col_count;
                    }
                    return array('count' => $col_array[$iteration], 'override' => false);
                }
            }
            //fail over
            $cols = !empty($this->options['yj_default_column_width']) ? $this->options['yj_default_column_width'] : 3;
            return array('count' => $cols, 'override' => false);
        }

        /**
         * Load plugins CSS and JSS on site backend/admin view
         * 
         * @param type $hook
         * @return type
         */
        public function yj_backend_scripts() {
            /*
             * allow use of media library 
             */
            wp_enqueue_media();

            /* CSS */
            wp_enqueue_style('yj-back-style', YJ_MASONRY_PLUGIN_URL . 'css/admin.css');
            
            if (wp_style_is( 'chosen', 'registered' )) {
                //remove old chosen style
                wp_deregister_style('chosen');
            }
            
            wp_register_style('chosen', YJ_MASONRY_PLUGIN_URL . 'lib/chosen/chosen.css');
            wp_enqueue_style('chosen');
            wp_enqueue_style('fontawesome-back-style', YJ_MASONRY_PLUGIN_URL . 'css/font-awesome.min.css');
            //wp_enqueue_style('bootstrap-style', YJ_MASONRY_PLUGIN_URL . 'css/bootstrap.min.css');
            wp_enqueue_style('wp-color-picker');

            /* JS */
            wp_enqueue_script('yj-back', YJ_MASONRY_PLUGIN_URL . 'js/admin.js', array('jquery'), $this->ver, true);
            wp_enqueue_script('yj-chosen', YJ_MASONRY_PLUGIN_URL . 'lib/chosen/chosen.jquery.js', array('jquery', 'wp-color-picker', 'jquery-ui-core', 'jquery-ui-slider'), '1.5.1', true);
        }

        /**
         * register new setting page under Dashboard->Settings->
         */
        public function yj_register_menu_page() {
            $iconb64 = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiICAgeG1sbnM6aW5rc2NhcGU9Imh0dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUiICAgd2lkdGg9IjIwIiAgIGhlaWdodD0iMjAiICAgdmlld0JveD0iMCAwIDUuMjkxNjY2NSA1LjI5MTY2NjgiICAgdmVyc2lvbj0iMS4xIiAgIGlkPSJzdmc4IiAgIHNvZGlwb2RpOmRvY25hbWU9Inl1bWphbS1pY29uLWdyZXktMjB4MjBfMi5zdmciICAgaW5rc2NhcGU6dmVyc2lvbj0iMC45Mi4xIHIxNTM3MSI+ICA8ZGVmcyAgICAgaWQ9ImRlZnMyIiAvPiAgPHNvZGlwb2RpOm5hbWVkdmlldyAgICAgaWQ9ImJhc2UiICAgICBwYWdlY29sb3I9IiNmZmZmZmYiICAgICBib3JkZXJjb2xvcj0iIzY2NjY2NiIgICAgIGJvcmRlcm9wYWNpdHk9IjEuMCIgICAgIGlua3NjYXBlOnBhZ2VvcGFjaXR5PSIwLjAiICAgICBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIiAgICAgaW5rc2NhcGU6em9vbT0iMTAuMzU0NDMiICAgICBpbmtzY2FwZTpjeD0iMjAiICAgICBpbmtzY2FwZTpjeT0iOS41IiAgICAgaW5rc2NhcGU6ZG9jdW1lbnQtdW5pdHM9Im1tIiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0iZzM2OTgtNSIgICAgIHNob3dncmlkPSJmYWxzZSIgICAgIGlua3NjYXBlOndpbmRvdy13aWR0aD0iMTY4MCIgICAgIGlua3NjYXBlOndpbmRvdy1oZWlnaHQ9Ijk4NyIgICAgIGlua3NjYXBlOndpbmRvdy14PSItOCIgICAgIGlua3NjYXBlOndpbmRvdy15PSItOCIgICAgIGlua3NjYXBlOndpbmRvdy1tYXhpbWl6ZWQ9IjEiICAgICB1bml0cz0icHgiIC8+ICA8bWV0YWRhdGEgICAgIGlkPSJtZXRhZGF0YTUiPiAgICA8cmRmOlJERj4gICAgICA8Y2M6V29yayAgICAgICAgIHJkZjphYm91dD0iIj4gICAgICAgIDxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PiAgICAgICAgPGRjOnR5cGUgICAgICAgICAgIHJkZjpyZXNvdXJjZT0iaHR0cDovL3B1cmwub3JnL2RjL2RjbWl0eXBlL1N0aWxsSW1hZ2UiIC8+ICAgICAgICA8ZGM6dGl0bGU+PC9kYzp0aXRsZT4gICAgICA8L2NjOldvcms+ICAgIDwvcmRmOlJERj4gIDwvbWV0YWRhdGE+ICA8ZyAgICAgaW5rc2NhcGU6bGFiZWw9IkxheWVyIDEiICAgICBpbmtzY2FwZTpncm91cG1vZGU9ImxheWVyIiAgICAgaWQ9ImxheWVyMSIgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAsLTI5MS43MDgzMikiPiAgICA8ZyAgICAgICBpZD0iZzM2OTgtNSIgICAgICAgdHJhbnNmb3JtPSJtYXRyaXgoMC4yNjQ1ODMzMywwLDAsMC4yNjQ1ODMzMywxMDMuMTkwOTgsMTQ2LjE5ODU1KSI+ICAgICAgPGcgICAgICAgICB0cmFuc2Zvcm09Im1hdHJpeCgwLjYyNTEzMjM0LDAsMCwwLjYyNTAwMDA0LC0zOTAuMjM2NzcsNTQ5LjcxNDgpIiAgICAgICAgIHN0eWxlPSJmaWxsOiM3ZDdkN2Q7ZmlsbC1vcGFjaXR5OjEiICAgICAgICAgaWQ9ImczNjg2LTciPiAgICAgICAgPHBhdGggICAgICAgICAgIGlua3NjYXBlOmNvbm5lY3Rvci1jdXJ2YXR1cmU9IjAiICAgICAgICAgICBzdHlsZT0iZmlsbDojN2Q3ZDdkO2ZpbGwtb3BhY2l0eToxIiAgICAgICAgICAgaWQ9InBhdGgzNjgyLTIiICAgICAgICAgICBkPSJtIDguNjczLDQuNzkxIGggMTUuNjU0IGMgMCwwIDEuNTA4LC0wLjcxOCAxLjUwOCwtMi4wOSAwLC0xLjk4OSAtMS44NTUsLTIuMjAxIC0yLjg0MiwtMi4yMDEgLTAuOTg1LDAgLTYuNDkzLDAgLTYuNDkzLDAgMCwwIC01LjUwOCwwIC02LjQ5MywwIC0wLjk4NiwwIC0yLjg0MiwwLjIxMiAtMi44NDIsMi4yMDEgMCwxLjM3MiAxLjUwOCwyLjA5IDEuNTA4LDIuMDkgeiIgLz4gICAgICAgIDxwYXRoICAgICAgICAgICBpbmtzY2FwZTpjb25uZWN0b3ItY3VydmF0dXJlPSIwIiAgICAgICAgICAgc3R5bGU9ImZpbGw6IzdkN2Q3ZDtmaWxsLW9wYWNpdHk6MSIgICAgICAgICAgIGlkPSJwYXRoMzY4NC0yIiAgICAgICAgICAgZD0iTSAyNC45OTgsNS44NjQgSCA4LjAwMSBMIDYuNDk3LDguMjcxIGMgMi41LDAgMi41LDEuODU2IDUsMS44NTYgMi41LDAgMi41LC0xLjg1NiA1LC0xLjg1NiAyLjUwMiwwIDIuNTAyLDEuODU2IDUuMDA0LDEuODU2IDIuNTAyLDAgMi41MDIsLTEuODU2IDUuMDAyLC0xLjg1NiB6IiAvPiAgICAgIDwvZz4gICAgICA8cGF0aCAgICAgICAgIHNvZGlwb2RpOm5vZGV0eXBlcz0ic3NjY3Nzc2Njc3NzY3NzIiAgICAgICAgIHN0eWxlPSJmaWxsOiM3ZDdkN2Q7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlLXdpZHRoOjAuNjI1MDY2MTYiICAgICAgICAgaW5rc2NhcGU6Y29ubmVjdG9yLWN1cnZhdHVyZT0iMCIgICAgICAgICBpZD0icGF0aDM2ODgtNiIgICAgICAgICBkPSJtIC0zNzIuMTM0MTksNTY1Ljg5NTQyIHYgLTMuMDA4NzQgYyAwLC0zLjA0Njg3IC0wLjEyMTksLTQuNDAxMjUgLTEuMzA1MjgsLTUuOTAxMjUgLTAuMzE1MDYsLTAuNDAwNjIgLTAuNTcxMzcsLTAuODExODggLTAuNzgzMjksLTEuMjE4NzUgLTAuOTMyMDcsMC4yODM3NSAtMS4xNjg5OSwxLjA3ODEyIC0yLjQ4ODY1LDEuMDc4MTIgLTEuNjA1OTYsMCAtMS45MjI5MSwtMS4xNzgxMiAtMy4yMTA2OCwtMS4xNzgxMiAtMS4yODc3NywwIC0xLjYwNTk2LDEuMTc4MTIgLTMuMjExOTMsMS4xNzgxMiAtMS4zMTg0LDAgLTEuNTU1MzMsLTAuNzkzNzQgLTIuNDg4MDMsLTEuMDc4MTIgLTAuMjExMjksMC40MDYyNSAtMC40Njc1OSwwLjgxODEzIC0wLjc4MjY2LDEuMjE4NzUgLTEuMTgzMzgsMS41IC0xLjMwNTI4LDIuODU0MzggLTEuMzA1MjgsNS45MDEyNSB2IDMuMDA4NzQgYyAwLDAuOTM0MzkgLTAuMjkwNjgsNC4xMzE4OCAzLjU1MjYzLDQuMTMxODggaCA0LjIzNTI3IDQuMjM1MjcgYyAzLjg0MjY5LDAgMy41NTI2MywtMy4xOTc0OSAzLjU1MjYzLC00LjEzMTg4IHoiIC8+ICAgICAgPHRleHQgICAgICAgICBpZD0idGV4dDM3MDktOSIgICAgICAgICB5PSIxNy4wMjMxIiAgICAgICAgIHg9IjExLjMzNDA4MSIgICAgICAgICBzdHlsZT0iZm9udC1zdHlsZTpub3JtYWw7Zm9udC12YXJpYW50Om5vcm1hbDtmb250LXdlaWdodDpub3JtYWw7Zm9udC1zdHJldGNoOm5vcm1hbDtmb250LXNpemU6MjUuMDA1MjkyODlweDtsaW5lLWhlaWdodDoxLjI1O2ZvbnQtZmFtaWx5OidGcmFua2xpbiBHb3RoaWMgSGVhdnknOy1pbmtzY2FwZS1mb250LXNwZWNpZmljYXRpb246J0ZyYW5rbGluIEdvdGhpYyBIZWF2eSwgJztsZXR0ZXItc3BhY2luZzowcHg7d29yZC1zcGFjaW5nOjBweDtmaWxsOiM3ZDdkN2Q7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlOm5vbmU7c3Ryb2tlLXdpZHRoOjAuNjI1MTMyMzIiICAgICAgICAgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+PHRzcGFuICAgICAgICAgICBpZD0idHNwYW4zNzE5LTIiICAgICAgICAgICBzdHlsZT0iZm9udC1zaXplOjQuNTg0MzAzODZweDtsaW5lLWhlaWdodDowO2ZpbGw6IzdkN2Q3ZDtmaWxsLW9wYWNpdHk6MTtzdHJva2Utd2lkdGg6MC42MjUxMzIzMiIgICAgICAgICAgIHk9IjM5LjgxODMxNCIgICAgICAgICAgIHg9IjExLjMzNDA4MSIgICAgICAgICAgIHNvZGlwb2RpOnJvbGU9ImxpbmUiIC8+PC90ZXh0PiAgICAgIDx0ZXh0ICAgICAgICAgaWQ9InRleHQzNzA5LTItNCIgICAgICAgICB5PSIxMy4yOTc1NiIgICAgICAgICB4PSIxMS4zMzQ3MDgiICAgICAgICAgc3R5bGU9ImZvbnQtc3R5bGU6bm9ybWFsO2ZvbnQtdmFyaWFudDpub3JtYWw7Zm9udC13ZWlnaHQ6bm9ybWFsO2ZvbnQtc3RyZXRjaDpub3JtYWw7Zm9udC1zaXplOjI1LjAwNTI5Mjg5cHg7bGluZS1oZWlnaHQ6MS4yNTtmb250LWZhbWlseTonRnJhbmtsaW4gR290aGljIEhlYXZ5JzstaW5rc2NhcGUtZm9udC1zcGVjaWZpY2F0aW9uOidGcmFua2xpbiBHb3RoaWMgSGVhdnksICc7bGV0dGVyLXNwYWNpbmc6MHB4O3dvcmQtc3BhY2luZzowcHg7ZmlsbDojN2Q3ZDdkO2ZpbGwtb3BhY2l0eToxO3N0cm9rZTpub25lO3N0cm9rZS13aWR0aDowLjYyNTEzMjMyIiAgICAgICAgIHhtbDpzcGFjZT0icHJlc2VydmUiPjx0c3BhbiAgICAgICAgICAgaWQ9InRzcGFuMzcxOS02LTgiICAgICAgICAgICBzdHlsZT0iZm9udC1zaXplOjQuNTg0MzAzODZweDtsaW5lLWhlaWdodDowO2ZpbGw6IzdkN2Q3ZDtmaWxsLW9wYWNpdHk6MTtzdHJva2Utd2lkdGg6MC42MjUxMzIzMiIgICAgICAgICAgIHk9IjM2LjA5Mjc3MyIgICAgICAgICAgIHg9IjExLjMzNDcwOCIgICAgICAgICAgIHNvZGlwb2RpOnJvbGU9ImxpbmUiIC8+PC90ZXh0PiAgICAgIDx0ZXh0ICAgICAgICAgaWQ9InRleHQzNzU1LTAiICAgICAgICAgeT0iMTcuMDI4NDg2IiAgICAgICAgIHg9IjcuOTE3OTE5MiIgICAgICAgICBzdHlsZT0iZm9udC1zdHlsZTpub3JtYWw7Zm9udC12YXJpYW50Om5vcm1hbDtmb250LXdlaWdodDpub3JtYWw7Zm9udC1zdHJldGNoOm5vcm1hbDtmb250LXNpemU6NC41ODQzMDM4NnB4O2xpbmUtaGVpZ2h0OjEuMjU7Zm9udC1mYW1pbHk6J0ZyYW5rbGluIEdvdGhpYyBIZWF2eSc7LWlua3NjYXBlLWZvbnQtc3BlY2lmaWNhdGlvbjonRnJhbmtsaW4gR290aGljIEhlYXZ5LCAnO2xldHRlci1zcGFjaW5nOjBweDt3b3JkLXNwYWNpbmc6MHB4O2ZpbGw6IzdkN2Q3ZDtmaWxsLW9wYWNpdHk6MTtzdHJva2U6bm9uZTtzdHJva2Utd2lkdGg6MC42MjUxMzIzMiIgICAgICAgICB4bWw6c3BhY2U9InByZXNlcnZlIj48dHNwYW4gICAgICAgICAgIHN0eWxlPSJmaWxsOiM3ZDdkN2Q7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlLXdpZHRoOjAuNjI1MTMyMzIiICAgICAgICAgICB5PSIyMS4yMDc2MDkiICAgICAgICAgICB4PSI3LjkxNzkxOTIiICAgICAgICAgICBpZD0idHNwYW4zNzUzLTYiICAgICAgICAgICBzb2RpcG9kaTpyb2xlPSJsaW5lIiAvPjwvdGV4dD4gICAgICA8dGV4dCAgICAgICAgIGlkPSJ0ZXh0Mzc1OS0xIiAgICAgICAgIHk9IjE3LjAyNTIzOCIgICAgICAgICB4PSI1LjA5NjU5MSIgICAgICAgICBzdHlsZT0iZm9udC1zdHlsZTpub3JtYWw7Zm9udC12YXJpYW50Om5vcm1hbDtmb250LXdlaWdodDpub3JtYWw7Zm9udC1zdHJldGNoOm5vcm1hbDtmb250LXNpemU6NC41ODQzMDM4NnB4O2xpbmUtaGVpZ2h0OjEuMjU7Zm9udC1mYW1pbHk6J0ZyYW5rbGluIEdvdGhpYyBIZWF2eSc7LWlua3NjYXBlLWZvbnQtc3BlY2lmaWNhdGlvbjonRnJhbmtsaW4gR290aGljIEhlYXZ5LCAnO2xldHRlci1zcGFjaW5nOjBweDt3b3JkLXNwYWNpbmc6MHB4O2ZpbGw6IzdkN2Q3ZDtmaWxsLW9wYWNpdHk6MTtzdHJva2U6bm9uZTtzdHJva2Utd2lkdGg6MC42MjUxMzIzMiIgICAgICAgICB4bWw6c3BhY2U9InByZXNlcnZlIj48dHNwYW4gICAgICAgICAgIHN0eWxlPSJmaWxsOiM3ZDdkN2Q7ZmlsbC1vcGFjaXR5OjE7c3Ryb2tlLXdpZHRoOjAuNjI1MTMyMzIiICAgICAgICAgICB5PSIyMS4yMDQzNjEiICAgICAgICAgICB4PSI1LjA5NjU5MSIgICAgICAgICAgIGlkPSJ0c3BhbjM3NTctNyIgICAgICAgICAgIHNvZGlwb2RpOnJvbGU9ImxpbmUiIC8+PC90ZXh0PiAgICA8L2c+ICA8L2c+PC9zdmc+';
            add_menu_page(__('Masonry Options', 'yumjam-masonry-layout-for-posts-and-custom-post-types'), __('YumJam Masonry', 'yumjam-masonry-layout-for-posts-and-custom-post-types'),
                    'manage_options', $this->slug, array($this, $this->slug . '_landing'),$iconb64, 30 );
            add_submenu_page($this->slug, __('Shortcode Builder', 'yumjam-masonry-layout-for-posts-and-custom-post-types'), __('Shortcode Builder', 'yumjam-masonry-layout-for-posts-and-custom-post-types'), 
                    'manage_options', $this->slug. '_', array($this, $this->slug . '_options') );
        }

        /**
         * include the setting page template
         * 
         */
        public function yumjam_masonry_landing() {
            if (current_user_can('manage_options')) {
                include(YJ_MASONRY_PLUGIN_PATH . '/templates/landing-page.php');
            }
        }

        /**
         * include the setting page template
         * 
         */
        public function yumjam_masonry_options() {
            if (current_user_can('manage_options')) {
                include(YJ_MASONRY_PLUGIN_PATH . '/templates/options.php');
            }
        }        
        
        /**
         * Populate main configurable setting on landing page
         */
        public function configure_settings_options() {
            $prefix = 'yjm_';
            $section = array('id' => $prefix . 'options_group1', 'name' => 'Configurable Settings');

            /* Array of Setting to add to the main configurable setting on landing page */
            $settings = array(
                array('id' => $prefix . 'disable_bricks', 'type' => 'checkbox', 'name' => 'Remove Bricks CPT', 'desc' => 'I dont need the Bricks custom post type please remove it'),
                array('id' => $prefix . 'disable_post_metabox', 'type' => 'checkbox', 'name' => 'Hide Masonry Data for Posts', 'desc' => 'All the Post entries will be the same, I dont need the masonry meta box on the each post edit page'),
            );

            if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
                $settings[] = array('id' => $prefix . 'woocommerce_support', 'type' => 'checkbox', 'name' => 'WooCommerce Support', 'desc' => 'allow woocommerce products to show in masonry walls');
            }

            add_settings_section($section['id'], $section['name'], '', $prefix . 'options');
            foreach ($settings as $s) {
                register_setting($section['id'], $s['id']);
                add_settings_field($s['id'], $s['name'], 
                        array($this, 'yjm_output_settings_field'), 
                        $prefix . 'options', $section['id'], 
                        array('id' => $s['id'], 'type' => $s['type'], 'values' => (!empty($s['values'])?$s['values']:false), 'tab' => 'main', 'desc' => $s['desc'])
                    );
            }
        }        
        
        /**
         * Build form input elements for main configurable setting on landing page
         * @param type $args
         */
        public function yjm_output_settings_field($args) {
            if (!empty($args['values'])) {
                if ($args['values'] == 'callback') {
                    $values = call_user_func(array($this, $args['id'] . '_values'));
                } else if (is_array($args['values'])) {
                    $values = $args['values'];
                }
            }            
            
            switch ($args['type']) {
                case 'break':
                    $html = "<hr />";
                    break;
                case 'textbox':
                    $html = "<input type='text' id='{$args['id']}' name='{$args['id']}' value='" . get_option($args['id']) . "' />";
                    break;
                case 'checkbox':
                    $html = "<input type='checkbox' id='{$args['id']}' name='{$args['id']}' value='1'" . checked(1, get_option($args['id']), false) . "/>";
                    //$html .= "<label for='{$args['id']}'></label>";                    
                    break;
                case 'radio':
                    $option = get_option($args['id']);
                    if (is_array($values)) {
                        $html = '';
                        foreach ($values as $value => $label) {
                            $html .= "<div id='radio-{$value}' class='{$args['id']}'> <input type='radio' id='{$args['id']}-{$value}' name='{$args['id']}' value='{$value}' " . checked($option, $value, false) . " />{$label}</div>";
                        }
                    }
                    break;
                case 'media-select':
                    $html = "<input type='text' id='{$args['id']}' name='{$args['id']}' value='" . get_option($args['id']) . "' class='regular-text' />";
                    $html .= "<input type='button' name='rl_media_select' id='rl_media_select' class='button-secondary' value='Choose Logo' / >";
                    break;
                case 'multi-select':
                    if (is_array($values)) {
                        $html = "<select multiple='true' class='chosen' id='{$args['id']}' name='{$args['id']}[]' style='width:200px;'>";
                        $options = get_option($args['id']);
                        foreach ($values as $value) {
                            $selected = '';
                            if (!empty($options) && is_array($options)) {
                                $selected = in_array($value, $options) ? ' selected="selected"' : '';
                            }
                            $html .= "<option value='{$value}'{$selected}>".ucfirst($value)."</option>";
                        }
                        $html .= "</select>";
                    }
                    break;                
            }
            $html .= "<p class='description' id='tagline-description'>";
            if (!empty($args['desc'])) {
                $html .= "{$args['desc']}";
            }
            $html .= "</p>";
            
            echo $html;
        }        
        
        public function printArray($array) {
            $vargle = "";
            foreach ($array as $key => $value) {
                $vargle .= "$key => $value";
                if (is_array($value)) { //If $value is an array, print it as well!
                    printArray($value);
                }
            }
            return $vargle;
        }

        /**
         * Load more posts function   
         */
        public function more_post_ajax() {

            $post_data = filter_input_array(INPUT_POST);
            $atts = json_decode($post_data['atts'], true);
            empty($post_data['paged'])? : $atts['paged'] = $post_data['paged'];

            header("Content-Type: text/html");
            die($this->yumjam_masonry_function($atts, true));
        }

        public function handleQuery($query) {
            //start output buffer
            ob_start();

            if ($query->have_posts()) {
                while ($query->have_posts()) {
                    $query->the_post();

                    //calc the absolute "current_post" number 
                    if (!empty($query->query['paged'])) {
                        $cur_post = (($query->query['paged'] - 1) * $query->query['posts_per_page']) + $query->current_post;
                    } else {
                        $cur_post = $query->current_post;
                    }

                    //number of columns the brick should span - array('count', 'override')
                    $cols = $this->columnCount($query->post->ID, $cur_post);

                    //css classes to add to the brick outer div 
                    $classes = array('griditem', 'col-sm-' . $cols['count'], 'count-' . $cur_post);

                    $image = wp_get_attachment_image_src(get_post_thumbnail_id(), "large");

                    include ('templates/template-brick.php');
                }
                wp_reset_postdata();
            }

            $out = ob_get_contents();
            ob_end_clean();
            return $out;
        }

    }

    endif;

function YJM() {
    return YumJamMasonry::instance();
}

// Global for backwards compatibility.
$GLOBALS['YJMasonry'] = YJM();

register_activation_hook(__FILE__, array('YumJamMasonryInstall', 'install'));

register_deactivation_hook(__FILE__, array('YumJamMasonryInstall', 'uninstall'));
