<?php
/**
 * YumJam Masonry Brick post type
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('MasonryCustomPostType')) {

    class MasonryCustomPostType {

        private $n; //Names
        private $t; //Taxonomies
        private $caps; //content_show
        protected $col_val = array();
        protected $supported_post_types = array();
        protected $header_position = array();
        protected $custom_header_show = array();

        protected $custom_header_background = "";
        protected $custom_header_color = "";
        protected $custom_header_padding = "";
        protected $custom_header_font_size = "";
        protected $custom_header_spacing = "";
        protected $custom_header_author_show = array();
        protected $custom_header_author_size = "";
        protected $custom_header_author_color = "";
        protected $custom_header_date_show = array();
        protected $custom_header_date_size = "";
        protected $custom_header_date_color = "";

        protected $custom_content_featured = array();
        protected $custom_content_show = array();
        protected $custom_content_overlay = array();
        protected $custom_content_min_height = "";
        protected $custom_content_size = "";
        protected $custom_content_color = "";
        protected $custom_content_padding = "";
        protected $custom_content_background = "";
        protected $custom_content_background_opacity = array();
        protected $custom_content_link = "";
        

        protected $custom_footer_show = array();
        protected $custom_footer_bold = array();
        protected $custom_footer_text = "";
        protected $custom_footer_size = "";
        protected $custom_footer_color = "";
        protected $custom_footer_background_color = "";
        protected $custom_footer_padding = "";
        protected $custom_footer_alignment = array();

        public function __construct() {
            //TODO: Make this a settings page selection of available post types
            $this->supported_post_types = array($this->n['slug'], 'post', 'article');
            
            $this->n = array('slug' => 'custom', 'single' => 'Brick', 'multiple' => 'Bricks');
            $this->t = array(
                array('slug' => 'color', 'name' => 'Colour', 'plural' => 'Colours', 'tree' => true),
            );
            $this->caps = array(
                'read_post' => 'read_' . $this->n['slug'],
                'read_private_posts' => 'read_private_' . $this->n['slug'] . 's',
                'edit_posts' => 'edit_' . $this->n['slug'] . 's',
                'edit_others_posts' => 'edit_others_' . $this->n['slug'] . 's',
                'edit_private_posts' => 'edit_private_' . $this->n['slug'] . 's',
                'edit_published_posts' => 'edit_published_' . $this->n['slug'] . 's',
                'delete_post' => 'delete_' . $this->n['slug'],
                'delete_posts' => 'delete_' . $this->n['slug'] . 's',
                'delete_others_posts' => 'delete_others_' . $this->n['slug'] . 's',
                'delete_private_posts' => 'delete_private_' . $this->n['slug'] . 's',
                'delete_published_posts' => 'delete_published_' . $this->n['slug'] . 's',
                'publish_posts' => 'publish_' . $this->n['slug'] . 's',
                    //'create_posts' => 'create_'.$this->n['slug'].'s',
            );

            $this->col_val = array('0' => 'Use Template', '1' => 'One Column', '2' => 'Two Columns', '3' => 'Quarter Width', '4' => 'Third Width', '5' => 'Five Columns', '6' => 'Half Width',
                '7' => 'Seven Columns', '8' => 'Eight Columns', '9' => 'Nine Columns', '10' => 'Ten Columns', '11' => 'Eleven Columns', '12' => 'Full Width');
            $this->header_position = array('0' => 'Use Template', '1' => 'Top', '2' => 'Bottom');
            $this->custom_header_show = array('0' => 'Use Template', '1' => 'Show', '2' => 'Hide');
            $this->custom_header_background = "";
            $this->custom_header_color = "";
            $this->custom_header_padding = "";
            $this->custom_header_font_size = "";
            $this->custom_header_spacing = "";
            $this->custom_header_author_show = array('0' => 'Use Template', '1' => 'Show', '2' => 'Hide');
            $this->custom_header_author_size = "";
            $this->custom_header_author_color = "";
            $this->custom_header_date_show = array('0' => 'Use Template', '1' => 'Show', '2' => 'Hide');
            $this->custom_header_date_size = "";
            $this->custom_header_date_color = "";

            $this->custom_content_featured = array('0' => 'Use Template', '1' => 'Use Featured Image', '2' => 'Ignore Featured Image');
            $this->custom_content_show = array('0' => 'Use Template', '1' => 'Show', '2' => 'Hide');
            $this->custom_content_overlay = array('0' => 'Use Template', '1' => 'Overlay Image', '2' => 'No Overlay');
            $this->custom_content_min_height = "";
            $this->custom_content_size = "";
            $this->custom_content_color = "";
            $this->custom_content_padding = "";
            $this->custom_content_background = "";
            $this->custom_content_background_opacity = array('0' => 'Use Template', '1' => '0', '2' => '0.1', '3' => '0.2', '4' => '0.3', '5' => '0.4', '6' => '0.5', '7' => '0.6', '8' => '0.7', '9' => '0.8', '10' => '0.9', '11' => '1');
            $this->custom_content_link = "";

            $this->custom_footer_show = array('0' => 'Use Template', '1' => 'Show', '2' => 'Hide');
            $this->custom_footer_bold = array('0' => 'Use Template', '1' => 'Bold', '2' => 'Standard');
            $this->custom_footer_text = "";
            $this->custom_footer_size = "";
            $this->custom_footer_color = "";
            $this->custom_footer_background_color = "";
            $this->custom_footer_padding = "";
            $this->custom_footer_alignment = array('0' => 'Use Template', '1' => 'Left', '2' => 'Right');

            add_action('init', array($this, 'init'), 0);
            add_action('add_meta_boxes', array($this, 'add_meta_boxes'), 30);
            add_action('save_post', array($this, 'save_meta'), 1, 2);

            add_filter('template_include', array($this, 'view_template'), 1);

            //admin post edit/list view
            add_filter('manage_edit-' . $this->n['slug'] . '_columns', array($this, 'edit_columns'));
            add_filter('manage_edit-' . $this->n['slug'] . '_sortable_columns', array($this, 'sortable_columns'));
            add_action('manage_' . $this->n['slug'] . '_posts_custom_column', array($this, 'manage_columns'), 10, 2);
            add_action('load-edit.php', array($this, 'edit_load'));
            add_action('quick_edit_custom_box', array($this, 'custom_add_quick_edit'), 10, 2);

            $admin = new WP_User;
            $admin->init(WP_User::get_data_by('id', 1));

            if (!$admin->has_cap('edit_others_' . $this->n['slug'] . 's')) {
                $this->empower_admin();
            }

            //remove_role($this->n['slug'] . '_contrib');

            //if (!$GLOBALS['wp_roles']->is_role($this->n['slug'] . '_contrib')) {
            //    $this->add_roles();
            //}
        }

        public function init() {
            $bricks_disbaled = get_option('yjm_disable_bricks');
            if ($bricks_disbaled != 1) {
                $this->post_type();
                $this->register_taxonomies();
            }            
        }

        /**
         * 
         */
        public function edit_load() {
            add_filter('request', array($this, 'sort_list'));
        }

        public function sort_list($vars) {
            // Check if we're viewing this post type.
            if (isset($vars['post_type']) && $this->n['slug'] == $vars['post_type']) {

                // Check if 'orderby' is set to 'town' or 'postcode'.
                if (isset($vars['orderby']) && 'Columns' == $vars['orderby']) {

                    // Merge the query vars with our custom variables. 
                    $vars = array_merge(
                            $vars, array(
                        'meta_key' => '_' . strtolower($vars['orderby']),
                        'orderby' => 'meta_value'
                            )
                    );
                }
            }

            return $vars;
        }

        /**
         * Add fields to the Post Quick Edit area
         * @param type $column_name
         * @param type $post_type
         * @return type
         */
        function custom_add_quick_edit($column_name, $post_type) {
            switch ($column_name) {
                case 'columns':
                    ?>
                    <fieldset class="inline-edit-col-right">
                        <div class="inline-edit-group wp-clearfix">
                            <label class="inline-edit-status alignleft">
                                <span class="title">Columns</span>
                                <input type="hidden" name="custom_widget_set_noncename" id="custom_widget_set_noncename" value="" />
                                <?php
                                echo $this->input_box('select', $column_name, '', '', $this->col_val);
                                ?>
                            </label>
                        </div>                        
                        <?php
                        break;
                    case 'header_position':
                    ?>
                    <fieldset class="inline-edit-col-right">
                        <div class="inline-edit-group wp-clearfix">
                            <label class="inline-edit-status alignleft">
                                <span class="title">Header Position</span>
                                <input type="hidden" name="custom_widget_set_noncename" id="custom_widget_set_noncename" value="" />
                                <?php
                                echo $this->input_box('select', $column_name, '', '', $this->header_position);
                                ?>
                            </label>
                        </div>                        
                        <?php
                        break;
                    case 'enabled':
                        ?>
                        <div class="inline-edit-group wp-clearfix">
                            <label class="alignleft">
                                <span class="checkbox-title">Enabled in Masonry</span>                                
                                <?php
                                echo $this->input_box('checkbox', $column_name, '', '', $this->col_val);
                                ?>

                            </label>
                        </div>                        
                    </fieldset>
                    <?php
                    break;
                    case 'custom_header_background':
                    ?>
                    <fieldset class="inline-edit-col-right">
                        <div class="inline-edit-group wp-clearfix">
                            <label class="inline-edit-status alignleft">
                                <span class="title">Header Background Color</span>
                                <input type="hidden" name="custom_widget_set_noncename" id="custom_widget_set_noncename" value="" />
                                <?php
                                echo $this->input_box('text', $column_name, '', '', $this->custom_header_background);
                                ?>
                            </label>
                        </div>                        
                        <?php
                        break;

            }
        }

        /**
         * 
         * @param type $columns
         * @return string
         */
        public function sortable_columns($columns) {
            $columns['columns'] = 'Columns';
            $columns['enabled'] = 'Enabled';
            return $columns;
        }

        public function edit_columns($columns) {
            $new_colums = array();
            foreach ($columns as $key => $value) {
                $new_columns[$key] = $value;
                if ($key == 'title') {
                    $new_columns['columns'] = 'Columns';
                    $new_columns['enabled'] = 'Enabled';
                }
            }

            return $new_columns;
        }

        /**
         * What content to display in the custom meta columns
         * 
         * @global type $post
         * @param type $column
         * @param type $post_id
         */
        public function manage_columns($column, $post_id) {
            global $post;

            switch ($column) {
                case 'columns' :
                    $meta = get_post_meta($post->ID);
                    if (!empty($meta[$this->n['slug'] . '_columns'][0])) {
                        echo __($meta[$this->n['slug'] . '_columns'][0]);
                    } else {
                        echo '—';
                    }
                    break;
                case 'enabled' :
                    $meta = get_post_meta($post->ID);
                    if (!empty($meta[$this->n['slug'] . '_enabled'][0])) {
                        echo __($meta[$this->n['slug'] . '_enabled'][0]);
                    } else {
                        echo 'on';
                    }
                    break;
                default :
                    break;
            }
        }

        public function post_type() {
            // Set UI labels for Custom Post Type
            $labels = array(
                'name' => _x($this->n['multiple'], 'Post Type General Name'),
                'singular_name' => _x($this->n['single'], 'Post Type Singular Name'),
                'menu_name' => __($this->n['multiple']),
                'parent_item_colon' => __('Parent ' . $this->n['single']),
                'all_items' => __('All ' . $this->n['multiple']),
                'view_item' => __('View ' . $this->n['single']),
                'add_new_item' => __('Add New ' . $this->n['single']),
                'add_new' => __('Add New'),
                'edit_item' => __('Edit ' . $this->n['single']),
                'update_item' => __('Update ' . $this->n['single']),
                'search_items' => __('Search ' . $this->n['multiple']),
                'not_found' => __('Not Found'),
                'not_found_in_trash' => __('Not found in Trash'),
            );

            // CPT Options
            $args = array(
                'label' => __($this->n['slug']),
                'description' => $this->n['multiple'],
                'labels' => $labels,
                'supports' => array('title', 'editor', 'thumbnail', 'trackbacks', 'post-formats'), //, 'custom-fields'),
                'hierarchical' => true,
                'public' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_nav_menus' => true,
                'show_in_admin_bar' => true,
                'menu_position' => 32,
                'menu_icon' => 'dashicons-tagcloud',
                'can_export' => true,
                'has_archive' => true,
                'exclude_from_search' => false,
                'publicly_queryable' => true,
                'capabilities' => $this->caps,
                'capability_type' => $this->n['slug'],
                'map_meta_cap' => true,
            );
            register_post_type($this->n['slug'], $args);
        }

        public function register_taxonomies() {
            foreach ($this->t as $tax) {
                // Add new taxonomy, make it hierarchical (like categories)
                $labels = array(
                    'name' => _x($tax['plural'], 'taxonomy general name'),
                    'singular_name' => _x($tax['name'], 'taxonomy singular name'),
                    'search_items' => __('Search ' . $tax['plural']),
                    'all_items' => __('All ' . $tax['plural']),
                    'parent_item' => __('Parent ' . $tax['name']),
                    'parent_item_colon' => __('Parent ' . $tax['name'] . ':'),
                    'edit_item' => __('Edit ' . $tax['name']),
                    'update_item' => __('Update ' . $tax['name']),
                    'add_new_item' => __('Add New ' . $tax['name']),
                    'new_item_name' => __('New ' . $tax['name'] . ' Name'),
                    'menu_name' => __($tax['plural']),
                );

                $args = array(
                    'hierarchical' => $tax['tree'],
                    'labels' => $labels,
                    'show_ui' => true,
                    'show_admin_column' => true,
                    'show_tagcloud' => true,
                    'query_var' => true,
                    'rewrite' => array('slug' => $tax['slug']),
                    'capabilities' => array(
                        'manage_terms' => 'manage_' . $tax['slug'],
                        'edit_terms' => 'edit_' . $tax['slug'],
                        'delete_terms' => 'delete_' . $tax['slug'],
                        'assign_terms' => 'assign_' . $tax['slug'],
                    ),
                );
                register_taxonomy($tax['slug'], array($this->n['slug']), $args);
            }
        }

        /**
         *  Add masonry data meta boxes for custom post and standard post types 
         */
        public function add_meta_boxes() {
            if (get_option('yjm_disable_post_metabox') != 1) {
                add_meta_box('masonry-data', 'Masonry Data', array($this, 'data_meta_box'), 'post', 'normal', 'high');
            }
            
            if (get_option('yjm_disable_bricks') != 1) {
                add_meta_box('masonry-data', 'Masonry Data', array($this, 'data_meta_box'), 'custom', 'normal', 'high');
            }
            
            //support YumJam reasarch plugin
            if (class_exists('Research')) {
                add_meta_box('masonry-data', 'Masonry Data', array($this, 'data_meta_box'), 'article', 'normal', 'high');
            }
            
            //support woocommerce
            if (get_option('yjm_woocommerce_support') == 1) {
                add_meta_box('masonry-data', 'Masonry Data', array($this, 'data_meta_box'), 'product', 'normal', 'high');
            }
        }

        /**
         * display Callback for post meta box
         */
        public function data_meta_box() {
            wp_nonce_field($this->n['slug'] . '_save_data', $this->n['slug'] . '_meta_nonce');

            //Form Input Fields
            $meta = array(
                //('input type', 'name/id', 'placeholder text', 'Label text', 'value')
                'enabled' => array('checkbox', 'enabled', '', 'Customise Styling', 'on'),
                'do_not_show' => array('checkbox', 'do_not_show', '', 'Do not Show in Masonry', 'off'),
                'columns' => array('select', 'columns', '0', 'Columns to span', $this->col_val),
                'header_label' => array('group', '', '', 'Masonry header options', ''),
                'header_show' => array('select', 'header_show', '0', 'Show Header?', $this->custom_header_show),
                'header_position' => array('select', 'header_position', '0', 'Header Position', $this->header_position),
                'header_background' => array('text', 'header_background', '', 'Header Background Colour', $this->custom_header_background, 'colour-picker'),
                'header_color' => array('text', 'header_color', '', 'Header Font Colour', $this->custom_header_color, 'colour-picker'),
                'header_padding' => array('text', 'header_padding', '', 'Header Text Padding', $this->custom_header_padding, '', 'clear'),
                'header_font_size' => array('text', 'header_font_size', '', 'Header Font Size', $this->custom_header_font_size),
                'header_spacing' => array('text', 'header_spacing', '', 'Header Spacing', $this->custom_header_spacing),
                'header_author_show' => array('select', 'header_author_show', '0', 'Show Author?', $this->custom_header_author_show),
                'header_author_size' => array('text', 'header_author_size', '', 'Header Author Font Size', $this->custom_header_author_size, '', 'clear'),
                'header_author_color' => array('text', 'header_author_color', '', 'HeaderAuthor Font Colour', $this->custom_header_author_color, 'colour-picker'),
                'header_date_show' => array('select', 'header_date_show', '0', 'Show Date?', $this->custom_header_date_show),
                'header_date_size' => array('text', 'header_date_size', '', 'Header Date Size', $this->custom_header_date_size),
                'header_date_color' => array('text', 'header_date_color', '', 'Header Date Colour', $this->custom_header_date_color, 'colour-picker', 'clear'),
                
                'content_label' => array('group', '', '', 'Masonry content options', ''),
                'content_featured' => array('select', 'content_featured', '0', 'Use Featured Image?', $this->custom_content_featured),
                'content_show' => array('select', 'content_show', '0', 'Show Excerpt?', $this->custom_content_show),
                'content_overlay' => array('select', 'content_overlay', '0', 'Content Overlay Image?', $this->custom_content_overlay),
                'content_min_height' => array('text', 'content_min_height', '', 'Min Height of content', $this->custom_content_min_height),
                'content_size' => array('text', 'content_size', '', 'Content Font Size', $this->custom_content_size, '', 'clear'),
                'content_color' => array('text', 'content_color', '', 'Content Font Colour', $this->custom_content_color, 'colour-picker'),
                'content_padding' => array('text', 'content_padding', '', 'Content Padding', $this->custom_content_padding),
                'content_background' => array('text', 'content_background', '', 'Content Background Colour', $this->custom_content_background, 'colour-picker'),
                //'content_background_opacity' => array('select', 'content_background_opacity', '0', 'Content Opacity', $this->custom_content_background_opacity, '', 'clear'),
                'content_link' => array('text', 'content_link', '', 'Override link', $this->custom_content_link),

                'footer_label' => array('group', '', '', 'Masonry footer options', ''),
                'footer_show' => array('select', 'footer_show', '0', 'Show Footer?', $this->custom_footer_show),
                'footer_bold' => array('select', 'footer_bold', '0', 'Footer Font Bold?', $this->custom_footer_bold),
                'footer_text' => array('text', 'footer_text', '', 'Footer Text', $this->custom_footer_text),
                'footer_size' => array('text', 'footer_size', '', 'Footer Font Size', $this->custom_footer_size),
                'footer_color' => array('text', 'footer_color', '', 'Footer Font Colour', $this->custom_footer_color, 'colour-picker', 'clear'),
                'footer_background_color' => array('text', 'footer_background_color', '', 'Footer Background Colour', $this->custom_footer_background_color, 'colour-picker'),
                'footer_padding' => array('text', 'footer_padding', '', 'Footer Padding', $this->custom_footer_padding),
                'footer_alignment' => array('select', 'footer_alignment', '0', 'Footer Alignment', $this->custom_footer_alignment),
            );
            
            global $post;
            $enabled = get_post_meta($post->ID, 'custom_enabled', true);
            $do_not_show = get_post_meta($post->ID, 'custom_do_not_show', true); 
            ob_start();
            ?>
            <div id="tab" class="tab_content" style="" >
                <div class="options_group hide_if_grouped">
                    <div class="masonry-col-sm-13 ">
                        <p class="form-field yj-cpt-input">
                            <label for="do_not_show">Do not Show in Masonry</label>
                            <input class="short" type="checkbox" id="custom_do_not_show" name="custom_do_not_show" <?php if ($do_not_show == 'on') { echo ' checked="true"'; }; ?> >                            
                            <label for="enabled">Customise Styling</label>
                            <input class="short" type="checkbox" id="custom_enabled" name="custom_enabled" <?php if ($enabled == 'on') { echo ' checked="true"'; }; ?> >
                        </p>
                    </div>      
                    
                    <div class="row masonry" id="yumjam-masonry-post-options" <?php if ($enabled != 'on') { echo ' style="display:none;"'; }; ?>>
                                          
                    <?php
                    foreach ($meta as $id => $i) {
                        if ($id == 'enabled' || $id == 'do_not_show') {
                            //Skip these in meta box
                        } else {
                            echo $this->input_box($i[0], $i[1], $i[2], $i[3], empty($i[4])?'':$i[4], empty($i[5])?'':$i[5], empty($i[6])?'':$i[6]);                            
                        }
                    }
                    ?>
                        <div class="col-sm-12">
                            <a href="#" id="clear-options" class="clear btn btn-default" role="button">Clear Masonry Options</a>
                        </div>
                    </div>
                </div>
            </div>                
            <?php
            $out = ob_get_contents();
            ob_end_clean();
            echo $out;
        }

        /**
         * Handler for meta box input types
         * 
         * @global type $post
         * @param type $type
         * @param type $id
         * @param type $placeholder
         * @param type $label
         * @param type $default
         * @return string
         */
        public function input_box($type, $id, $placeholder, $label, $default = '', $class = '', $wrapper = '') {
            global $post;
            $meta = get_post_meta($post->ID);
            //get existing input value from post meta
            $val = (empty($meta[$this->n['slug'] . "_" . $id][0]) ? '' : $meta[$this->n['slug'] . "_" . $id][0]);

            switch ($type) {
                case 'group':
                    $html = "<div class='masonry-col-sm-12 {$wrapper}'>";
                    $html .= "<p class='form-label'>";
                    $html .= "<h3>{$label}</h3>";                    
                    $html .= "</p>";
                    $html .= "</div>";
                    break;
                case 'text':
                    $html = "<div class='masonry-col-sm-3 {$wrapper}'>";
                    $html .= "<p class='form-field yj-cpt-input'>";
                    $html .= "<label for='{$id}'>{$label}</label>";
                    $html .= "<input class='short {$class}' type='{$type}' id='{$this->n['slug']}_{$id}' name='{$this->n['slug']}_{$id}' value='{$val}' placeholder='{$placeholder}' />";
                    $html .= "</p>";
                    $html .= "</div>";
                    break;
                case 'select':
                    $html = "<div class='masonry-col-sm-3 {$wrapper}'>";
                    $html .= "<p class='form-field yj-cpt-input'>";
                    $html .= "<label for='{$id}'>{$label}</label>";
                    $html .= "<select class='short' id='{$this->n['slug']}_{$id}' name='{$this->n['slug']}_{$id}' >";
                    
                    if (is_array($default)) {
                        foreach ($default as $value => $name) {
                            $sel = $val != $value ? '' : 'selected';
                            $html .= "<option value='{$value}' {$sel}>{$name}</option>";
                        }
                    }
                    $html .= "</select></p>";
                    $html .= "</div>";
                    break;
                case 'checkbox':
                    $html = "<div class='masonry-col-sm-3 {$wrapper}'>";
                    $html .= "<p class='form-field yj-cpt-input'>";
                    $html .= "<label for='{$id}'>{$label}</label>";
                    $checked = $val != 'off' ? 'checked' : '';
                    $html .= "<input class='short' type='checkbox' id='{$this->n['slug']}_{$id}' name='{$this->n['slug']}_{$id}' {$checked} />";
                    $html .= "</p>";
                    $html .= "</div>";
                    break;
            }
            return $html;
        }

        /**
         * Hook post saving save meta also
         * 
         * @param type $post_id
         * @param type $post
         * @return type
         */
        public function save_meta($post_id, $post) {
            if (isset($post->post_status) && 'auto-draft' == $post->post_status) {
                return;
            }
            $post_data = filter_input_array(INPUT_POST);

            /* if ( ! wp_verify_nonce( $post_data['custom_meta_nonce'], 'custom_save_data' ) ) {
              die( __( 'Security check', 'textdomain' ) );
              } else { */

            if (empty($post_data['custom_enabled'])) {
                update_post_meta($post_id, 'custom_enabled', 'off');
            } else {
                update_post_meta($post_id, 'custom_enabled', $post_data['custom_enabled']);
            }
            
            if (empty($post_data['custom_do_not_show'])) {
                update_post_meta($post_id, 'custom_do_not_show', 'off');
            } else {
                update_post_meta($post_id, 'custom_do_not_show', $post_data['custom_do_not_show']);
            }            

            if (in_array($post->post_type, $this->supported_post_types) && !empty($post_data)) {
                // Update masonry brick post meta
                foreach ($post_data as $element => $data) {
                    if (!empty($data) && preg_match("/" . $this->n['slug'] . "_([a-z0-9]*)/", $element)) {
                        update_post_meta($post_id, $element, $data);
                    }
                }
            }
        }

        /**
         * Single Custom Post template
         * @param type $template
         * @return string
         */
        public function view_template($template) {
            if (get_post_type() == 'cpt') {
                if (is_single()) {
                    // checks if the file exists in the theme first,
                    // otherwise serve the file from the plugin'
                    if ($theme_file = locate_template(array('single-' . $this->n['slug'] . '.php'))) {
                        $template = $theme_file;
                    } else {
                        $template = BIOS()->plugin_path() . '/templates/single-' . $this->n['slug'] . '.php';
                    }
                }
            }
            return $template;
        }

        private function empower_admin() {
            global $wp_roles;

            if (!class_exists('WP_Roles')) {
                return;
            }

            if (!isset($wp_roles)) {
                $wp_roles = new WP_Roles();
            }
            $role = get_role('administrator');

            //Add Administrator custom post permissions
            foreach ($this->caps as $value) {
                $role->add_cap($value);
            }

            //Add Administrator taxonomy permissions
            foreach ($this->t as $tax) {
                $role->add_cap('manage_' . $tax['slug']);
                $role->add_cap('edit_' . $tax['slug']);
                $role->add_cap('design_' . $tax['slug']);
                $role->add_cap('assign_' . $tax['slug']);
            }
        }

        private function add_roles() {
            //Create new role and apply permissions
            add_role($this->n['slug'] . '_contrib', $this->n['single'] . ' Contributer', array(
                'read' => true,
                //'level_0' => true,
                'create_' . $this->n['slug'] . 's' => true,
                'edit_' . $this->n['slug'] => true,
                'edit_' . $this->n['slug'] . 's' => true,
                'read_' . $this->n['slug'] => true,
                'delete_' . $this->n['slug'] => true,
                'assign_' . $this->t[0]['slug'] => true,
                'upload_files' => true, //add attachments
                    )
            );
        }

    }

    return new MasonryCustomPostType();
}