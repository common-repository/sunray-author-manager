<?php

$sam_version = "1.0.18"; // REMEMBER TO CHANGE BELOW TOO!

/**
 * Plugin Name: Sunray Author Manager
 * Plugin URI: http://www.sunraycomputer.com/plugins
 * Description: A versatile plugin for writers to highlight their work.
 * Version: 1.0.18
 * Author: Matthew Kressel
 * Author URI: https://www.matthewkressel.net
 * License:     GPL2
 *
 * Sunray Author Manager is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Sunray Author Manager is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Sunray Author Manager. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


// create new custom type
add_action('init', 'sam_register');

// register admin menu
add_action('admin_menu', 'sam_admin_menu');

// register meta boxes
add_action('add_meta_boxes', 'sam_meta_boxes');

// enqueue datepicker in admin
add_action('admin_enqueue_scripts', 'sam_enqueue_admin');

// enqueue css for users
add_action('wp_enqueue_scripts', 'sam_enqueue');

// save post data
add_action('save_post', 'sam_save');

// filter for posts
add_filter('the_content', 'sam_content');

//add_shortcode('sam_cover_tiles','sam_cover_tiles');
add_shortcode('sam_slider', 'sam_slider');
add_shortcode('sam_biblio', 'sam_biblio');

#add_filter('posts_where', 'sam_posts_where', 10, 2);

// update function
add_action('plugins_loaded', 'sam_update_db_check');

// refresh permalinks when loading and unloading
register_deactivation_hook(__FILE__, 'sam_deactivate');
register_activation_hook(__FILE__, 'sam_activate');

register_uninstall_hook(__FILE__, 'sam_deactivate');

// return array of bookstores
function sam_get_bookstores()
{
    return explode("|", "amazon|barnes_and_noble|google_play|kobo|indiebound|custom");
}

// flush rewrite rules on deactivate
function sam_deactivate()
{
    flush_rewrite_rules();
}

// flush rewrite rules on activate
function sam_activate()
{
    sam_register();
    flush_rewrite_rules();
}

// settings page link
function sam_add_settings_link($links)
{
    $settings_link = '<a href="admin.php?page=sam-options-settings">' . __('Settings') . '</a>';
    array_push($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'sam_add_settings_link');


// load scripts
function sam_enqueue_admin($hook)
{
    global $sam_version;

    //wp_enqueue_script('jquery-ui', '//code.jquery.com/ui/1.11.4/jquery-ui.js', array(), '1.0.0', true);
    //wp_enqueue_style('jquery-ui-style', '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
    wp_enqueue_script('jquery-ui-datepicker');
    //wp_enqueue_style( 'jquery-ui-datepicker' );
    wp_enqueue_style('jquery-ui-theme', plugin_dir_url(__FILE__) . '/css/jquery-ui.min.css');
    wp_enqueue_script('sam-admin-js', plugin_dir_url(__FILE__) . 'js/sam-admin.js?sam-version=' . $sam_version, array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'));
    wp_enqueue_style('sam-admin-css', plugin_dir_url(__FILE__) . 'css/sam-admin.css?sam-version=' . $sam_version);

}

function sam_enqueue($hook)
{
    wp_enqueue_style('slick-css', plugin_dir_url(__FILE__) . 'js/vendor/slick-master/slick/slick.css');
    wp_enqueue_style('slick-theme-css', plugin_dir_url(__FILE__) . 'js/vendor/slick-master/slick/slick-theme.css');

    wp_register_script('slick-js', plugin_dir_url(__FILE__) . 'js/vendor/slick-master/slick/slick.js', array('jquery'));
    wp_enqueue_script('slick-js');

    wp_register_script('sam-user-js', plugin_dir_url(__FILE__) . 'js/sam-user.js', array('jquery'));

    /*  // set speed setting in js
      $sam_options = array(
          'slider_speed' => get_option('sam_slider_speed') ? get_option('sam_slider_speed') : 8
      );
      wp_localize_script('sam-user-js', 'sam_options', $sam_options);*/

    wp_enqueue_script('sam-user-js');

    wp_enqueue_style('sam-user-css', plugin_dir_url(__FILE__) . 'css/sam-user.css');
}


// create custom post types
function sam_register()
{
    global $wp_rewrite;

    $stories_slug = get_option('sam_stories_slug') ? get_option('sam_stories_slug') : 'stories';
    $books_slug = get_option('sam_books_slug') ? get_option('sam_books_slug') : 'books';


    // Books
    $labels = array(
        'name' => _x('Books', 'post type general name'),
        'singular_name' => _x('Book', 'post type singular name'),
        'add_new' => _x('Add New Book', 'story item'),
        'add_new_item' => __('Add New Book'),
        'edit_item' => __('Edit Book'),
        'new_item' => __('New Book'),
        'view_item' => __('View Book'),
        'search_items' => __('Search Book'),
        'not_found' => __('Nothing found'),
        'not_found_in_trash' => __('Nothing found in Trash'),
        'parent_item_colon' => ''
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'has_archive' => true,
        'menu_icon' => '',
        'show_in_menu' => 'sam-options',
        'rewrite' => array('slug' => $books_slug),
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions')
    );

    register_post_type('sam_book', $args);
    $wp_rewrite->add_permastruct('sam_book', "$books_slug/%sam_book%", true, EP_NONE);

    // Stories
    $labels = array(
        'name' => _x('Stories', 'post type general name'),
        'singular_name' => _x('Story', 'post type singular name'),
        'add_new' => _x('Add New Story', 'story item'),
        'add_new_item' => __('Add New Story'),
        'edit_item' => __('Edit Story'),
        'new_item' => __('New Story'),
        'view_item' => __('View Story'),
        'search_items' => __('Search Story'),
        'not_found' => __('Nothing found'),
        'not_found_in_trash' => __('Nothing found in Trash'),
        'parent_item_colon' => ''
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'query_var' => true,
        'has_archive' => true,
        'menu_icon' => '',
        'show_in_menu' => 'sam-options',
        'rewrite' => array('slug' => $stories_slug),
        'capability_type' => 'post',
        'hierarchical' => false,
        'menu_position' => null,
        'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions')
    );

    register_post_type('sam_story', $args);
    $wp_rewrite->add_permastruct('sam_story', "$stories_slug/%sam_story%", true, EP_NONE);


}

// create admin menu item(s)
function sam_admin_menu()
{
    //remove_meta_box('pageparentdiv', 'bcs_story', 'normal');
    add_menu_page('Author Manager', 'Author Manager', 'edit_posts', 'sam-options', '', 'dashicons-book', 100);
}

// create meta boxes for post type(s)
function sam_meta_boxes()
{
    add_meta_box('sam-meta-story', 'Story Information', 'sam_meta_box', 'sam_story', 'normal', 'high');
    add_meta_box('sam-meta-book', 'Book Information', 'sam_meta_box', 'sam_book', 'normal', 'high');
}

// create publication date meta box
function sam_meta_box($post)
{

    $html = '';

    $title = get_the_title();


    $html .= '<div class="sam_title_meta sam_input">';
    $html .= 'Title (same as Post Title)<br><input readonly type="text" size="75" class="sam_title" name="sam_title" value="' . $title . '"/>&nbsp;';
    $html .= '</div>';


    $html .= '<div class="sam_reprint_meta sam_input">';
    $reprint = get_post_meta($post->ID, 'sam_reprint', true);
    $html .= '<div class="sam_reprint_meta sam_input">';
    $html .= 'Is this a reprint? &nbsp;<select class="sam_reprint" name="sam_reprint" />';
    $selected = ($reprint == 1) ? 'selected' : '';
    $html .= '<option value="1" ' . $selected . '>Yes</option>';
    $selected = ($reprint == 0) ? 'selected' : '';
    $html .= '<option value="0" ' . $selected . '>No</option>';
    $html .= '</select>';
    $html .= '</div>';


    $reprint_id = get_post_meta($post->ID, 'sam_reprint_id', true);
    if ($reprint_id == '') {
        $reprint_id = guess_original_publication($post);
    }

    $html .= '<div class="sam_reprint_id_meta sam_input">';

    $html .= 'Reprint of<br>';

    $my_query = new WP_Query(['post_type' => $post->post_type,
        'meta_query' => [
            [
                'key' => 'sam_reprint',
                'value' => '1',
                'compare' => '!='
            ]
        ], 'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC']);


    $original_post = $post;
    if ($my_query->have_posts()) {
        $html .= '<select name="reprint_id" class="sam-select-2">';

        $html .= "<option value='0'>(Select Original Publication Below)</option>";
        while ($my_query->have_posts()) {
            global $post;
            $my_query->the_post();
            $pub_name = get_post_meta($post->ID, 'sam_pub_name', true);
            $pub = get_post_meta($post->ID, 'sam_publisher', true);
            $reprint = get_post_meta($post->ID, 'sam_reprint', true);
            ($post->ID == $reprint_id) ? $selected = 'selected' : $selected = '';
            $html .= '<option value="' . $post->ID . '" ' . $selected . '>' . get_the_title() . " - $reprint $pub_name $pub" . '</option>';
        }
        $html .= '</select>';
        /* Restore original Post Data */
        $post = $original_post;
    } else {
        $html .= 'No stories found.';
    }
    $html .= '</div>';

    $byline = get_post_meta($post->ID, 'sam_byline', true);

    /*if(empty($byline)) {
        $current_user = wp_get_current_user();
        $byline = $current_user->display_name;
    }*/

    $html .= '<div class="sam_byline_meta sam_input">';
    $html .= 'Author Byline<br><input type="text" size="75" class="sam_byline" name="sam_byline" value="' . $byline . '"/>';
    $html .= '</div>';

    $pub = get_post_meta($post->ID, 'sam_pub_name', true);
    $html .= '<div class="sam_pub_name_meta sam_input">';
    $html .= 'Publication Name (if different from title)<br><input type="text" size="75" class="sam_pub_name" name="sam_pub_name" value="' . $pub . '"/>';
    $html .= '</div>';

    $pub = get_post_meta($post->ID, 'sam_publisher', true);
    $html .= '<div class="sam_publisher_meta sam_input">';
    $html .= 'Publisher<br><input type="text" size="75" class="sam_publisher" name="sam_publisher" value="' . $pub . '"/>';
    $html .= '</div>';

    $editors = get_post_meta($post->ID, 'sam_editors', true);
    $html .= '<div class="sam_sam_editors_meta sam_input">';
    $html .= 'Editor(s) (separate with commas)<br><input type="text" size="75" class="sam_editors" name="sam_editors" value="' . $editors . '"/>';
    $html .= '</div>';

    $url = get_post_meta($post->ID, 'sam_url', true);
    $html .= '<div class="sam_url_meta sam_input">';
    $html .= 'Link URL<br><input type="text" size="75" class="sam_url" name="sam_url" value="' . $url . '"/>';
    $html .= '</div>';

    $url_text = get_post_meta($post->ID, 'sam_url_text', true);
    $html .= '<div class="sam_url_text_meta sam_input">';
    $html .= 'Link URL Text<br><input type="text" size="75" class="sam_url_text" name="sam_url_text" value="' . $url_text . '" placeholder="More info"/>';
    $html .= '</div>';

    $podcast = get_post_meta($post->ID, 'sam_podcast_url', true);
    $html .= '<div class="sam_podcast_url_meta sam_input">';
    $html .= 'Podcast / Audiobook URL<br><input type="text" size="75" class="sam_podcast_url" name="sam_podcast_url" value="' . $podcast . '"/>';
    $html .= '</div>';

    $podcast_text = get_post_meta($post->ID, 'sam_podcast_url_text', true);
    $html .= '<div class="sam_podcast_url_text_meta sam_input">';
    $html .= 'Podcast / Audiobook URL Text<br><input type="text" size="75" class="sam_podcast_url_text" name="sam_podcast_url_text" value="' . $podcast_text . '" placeholder="Listen now" />';
    $html .= '</div>';


    // pub date
    $pub_date = get_post_meta($post->ID, 'sam_pub_date', true);

    if ($pub_date == PHP_INT_MAX) {
        $checked = 'checked';
        $disabled = 'disabled';
        $date = 'Forthcoming';
    } else {
        $checked = '';
        $disabled = '';
        if (empty($pub_date)) $pub_date = time();
        $date = date('F j, Y', $pub_date);
    }


    $html .= '<div class="sam_pub_date_meta sam_input">';
    $html .= 'Publication Date&nbsp; &nbsp; &nbsp;<label for="sam_pub_date_forthcoming" class="selectit"><input type="checkbox" ' . $checked . ' class="sam_pub_date_forthcoming" name="sam_pub_date_forthcoming" value="1"><em>Forthcoming</em>?</label><br><input type="text" autocomplete="off"  class="sam_pub_date" ' . $disabled . ' name="sam_pub_date" value="' . $date . '"/></p>';
    $html .= '</div>';

    $language = get_post_meta($post->ID, 'sam_language', true);
    $html .= '<div class="sam_language_meta sam_input">';
    $html .= 'Language<br><input type="text" size="75" class="sam_language" name="sam_language" value="' . $language . '"/>';
    $html .= '</div>';

    $translator = get_post_meta($post->ID, 'sam_translator', true);
    $html .= '<div class="sam_translator_meta sam_input">';
    $html .= 'Translator<br><input type="text" size="75" class="sam_translator" name="sam_translator" value="' . $translator . '"/>';
    $html .= '</div>';

    $isbn = get_post_meta($post->ID, 'sam_isbn', true);
    $isbn10 = isbn13_to_10($isbn);
    $html .= '<div class="sam_isbn_meta sam_input">';
    $html .= 'ISBN';
    if (!empty($isbn10) and $isbn10 != $isbn) {
        $html .= ' (ISBN-10: ' . $isbn10 . ')';
    }
    $html .= '<br><input type="text" size="75" class="sam_isbn" name="sam_isbn" value="' . $isbn . '"/>';
    $html .= '</div>';

    $accolades = get_post_meta($post->ID, 'sam_accolades', true);
    $html .= '<div class="sam_accolades_meta sam_input">';
    $html .= 'Accolades<br><input type="text" size="75" class="sam_accolades" name="sam_accolades" value="' . $accolades . '"/>';
    $html .= '</div>';

    $show_in_slider = get_post_meta($post->ID, 'sam_show_in_slider', true);
    $html .= '<div class="sam_show_in_slider_meta sam_input">';
    $html .= 'Show in slider &nbsp; <select class="sam_show_in_slider" name="sam_show_in_slider" />';
    $selected = ($show_in_slider == 1) ? 'selected' : '';
    $html .= '<option value="1" ' . $selected . '>Yes</option>';
    $selected = ($show_in_slider == 0) ? 'selected' : '';
    $html .= '<option value="0" ' . $selected . '>No</option>';
    $html .= '</select>';
    $html .= '</div>';

    $html .= '<div class="sam_pub_cover_image_meta sam_input">';
    $sam_cover_image = get_post_meta($post->ID, 'sam_cover_image', true);
    $html .= 'Cover image<br>';
    $html .= '<input type="text" size="75" value="' . $sam_cover_image . '" name="sam_cover_image" class="sam_cover_image"><br>';
    $html .= '<input type="button" class="button sam_cover_image_button" value="Browse" name="sam_cover_image_button">';
    $html .= '<p><img src="' . $sam_cover_image . '" class="sam_cover_image_preview"></p></div>';

    $html .= '<div class="sam_bookstore_links_meta sam_input">';
    $sam_bookstore_links = explode('|', get_post_meta($post->ID, 'sam_bookstore_links', true));
    $html .= 'Show Bookstore Links<br>';
    foreach (sam_get_bookstores() as $bookstore) {
        $html .= '<div class="sam-bookstore-icon">';
        $checked = (in_array($bookstore, $sam_bookstore_links)) ? 'checked' : '';
        $html .= '<input type="checkbox" name="sam_bookstore_links[]" value="' . $bookstore . '" ' . $checked . '>';
        $html .= '<label>' . ucwords(str_replace('_', ' ', $bookstore)) . '</label>';
        $html .= '</div>';
    }
    $html .= '</div>';

    $html .= '<div class="sam_bookstore_title_meta sam_input">';
    $custom_bookstore_title = get_post_meta($post->ID, 'sam_custom_bookstore_title', true);
    $html .= 'Custom Bookstore Title<br><input type="text" size="75" class="sam_custom_bookstore_title" name="sam_custom_bookstore_title" value="' . $custom_bookstore_title . '"/>';
    $html .= '</div>';

    $html .= '<div class="sam_bookstore_url_meta sam_input">';
    $custom_bookstore_url = get_post_meta($post->ID, 'sam_custom_bookstore_url', true);
    $html .= 'Custom Bookstore URL<br><input type="text" size="75" class="sam_custom_bookstore_url" name="sam_custom_bookstore_url" value="' . $custom_bookstore_url . '"/>';
    $html .= '</div>';

    $html .= '<input type="hidden" name="sam_meta_noncename" value="' . wp_create_nonce(__FILE__) . '" />';

    $html .= '<div class="sam_input">';
    $html .= 'Post ID: ' . $post->ID;
    $html .= '</div>';


    echo $html;
}

// try to guess what the original publication is and return ID
function guess_original_publication($reprint_post)
{

    global $wpdb;

    $query = "SELECT * from $wpdb->posts p LEFT JOIN  $wpdb->postmeta pm1 ON ( pm1.post_id = p.ID) where (p.post_type = '$reprint_post->post_type') and (p.post_status = 'publish') and  (pm1.meta_key = 'sam_pub_date') and (p.post_title LIKE '%" . esc_sql($wpdb->esc_like($reprint_post->post_title)) . "%') ORDER BY meta_value ASC LIMIT 1;";

    //echo $query; exit;

    $post = $wpdb->get_row($query);

    if (is_object($post)) return $post->ID;

    return 0;
}

function title_filter($where, &$wp_query)
{
    global $wpdb;
    if ($search_term = $wp_query->get('sam_search_title')) {
        $where .= ' AND ' . $wpdb->posts . '.';
    }
    return $where;
}

// save the meta fields
function sam_save($post_id)
{

    if (!isset($_REQUEST['sam_meta_noncename'])) return $post_id;

    // make sure data came from our meta box
    if (!wp_verify_nonce($_REQUEST['sam_meta_noncename'], __FILE__)) return $post_id;

    /* if(isset($_REQUEST['sam_title'])) {
        update_post_meta($post_id, 'sam_title', $_REQUEST['sam_title']);
    } */

    if (isset($_REQUEST['sam_byline'])) {
        $sam_byline = sanitize_text_field($_REQUEST['sam_byline']);
        update_post_meta($post_id, 'sam_byline', $sam_byline);
    }

    if (isset($_REQUEST['sam_url'])) {
        if (valid_url($_REQUEST['sam_url'])) {
            update_post_meta($post_id, 'sam_url', esc_url($_REQUEST['sam_url']));
        } else if (empty($_REQUEST['sam_url'])) {
            update_post_meta($post_id, 'sam_url', '');
        }
    } else {
        update_post_meta($post_id, 'sam_url', '');
    }

    if (isset($_REQUEST['sam_url_text'])) {
        $sam_url_text = sanitize_text_field($_REQUEST['sam_url_text']);
        update_post_meta($post_id, 'sam_url_text', $sam_url_text);
    }

    if (isset($_REQUEST['sam_podcast_url'])) {
        if (valid_url($_REQUEST['sam_podcast_url'])) {
            update_post_meta($post_id, 'sam_podcast_url', esc_url($_REQUEST['sam_podcast_url']));
        } else if (empty($_REQUEST['sam_podcast_url'])) {
            update_post_meta($post_id, 'sam_podcast_url', '');
        }
    }

    if (isset($_REQUEST['sam_podcast_url_text'])) {
        $sam_podcast_url_text = sanitize_text_field($_REQUEST['sam_podcast_url_text']);
        update_post_meta($post_id, 'sam_podcast_url_text', $sam_podcast_url_text);
    }

    if (isset($_REQUEST['sam_pub_name'])) {
        $sam_pub_name = sanitize_text_field($_REQUEST['sam_pub_name']);
        update_post_meta($post_id, 'sam_pub_name', $sam_pub_name);
    }

    if (isset($_REQUEST['sam_publisher'])) {
        $sam_publisher = sanitize_text_field($_REQUEST['sam_publisher']);
        update_post_meta($post_id, 'sam_publisher', $sam_publisher);
    }

    if (isset($_REQUEST['sam_editors'])) {
        $sam_editors = sanitize_text_field($_REQUEST['sam_editors']);
        update_post_meta($post_id, 'sam_editors', $sam_editors);
    }

    if (isset($_REQUEST['sam_reprint'])) {
        $sam_reprint = sanitize_text_field($_REQUEST['sam_reprint']);
        update_post_meta($post_id, 'sam_reprint', $sam_reprint);

    }

    if (isset($_REQUEST['sam_reprint_id'])) {
        $sam_reprint_id = sanitize_text_field($_REQUEST['sam_reprint_id']);
        update_post_meta($post_id, 'sam_reprint_id', $sam_reprint_id);
    }

    // reset reprint id

    if ($_REQUEST['sam_reprint'] == 0) {
        update_post_meta($post_id, 'sam_reprint_id', 0);
    }

    if (isset($_REQUEST['sam_isbn'])) {
        $sam_isbn = sanitize_text_field($_REQUEST['sam_isbn']);
        update_post_meta($post_id, 'sam_isbn', $sam_isbn);
    }

    if (isset($_REQUEST['sam_pub_date_forthcoming'])) {
        update_post_meta($post_id, 'sam_pub_date', PHP_INT_MAX);
    }

    if (isset($_REQUEST['sam_cover_image'])) {
        if (valid_url($_REQUEST['sam_cover_image'])) {
            update_post_meta($post_id, 'sam_cover_image', esc_url($_REQUEST['sam_cover_image']));
        }
    }

    if (isset($_REQUEST['sam_pub_date'])) {
        $data = strtotime($_REQUEST['sam_pub_date']);
        update_post_meta($post_id, 'sam_pub_date', $data);
    }

    if (isset($_REQUEST['sam_language'])) {
        $sam_language = sanitize_text_field($_REQUEST['sam_language']);
        update_post_meta($post_id, 'sam_language', $sam_language);
    }

    if (isset($_REQUEST['sam_translator'])) {
        $sam_translator = sanitize_text_field($_REQUEST['sam_translator']);
        update_post_meta($post_id, 'sam_translator', $sam_translator);
    }

    if (isset($_REQUEST['sam_accolades'])) {
        $sam_accolades = sanitize_text_field($_REQUEST['sam_accolades']);
        update_post_meta($post_id, 'sam_accolades', $sam_accolades);
    }

    if (isset($_REQUEST['sam_show_in_slider'])) {
        $sam_show_in_slider = ($_REQUEST['sam_show_in_slider'] == 1) ? '1' : '0';
        update_post_meta($post_id, 'sam_show_in_slider', $sam_show_in_slider);
    }

    if (isset($_REQUEST['sam_bookstore_links'])) {
        // sanitize
        $temp_links = [];
        foreach ($_REQUEST['sam_bookstore_links'] as $link) {
            if (in_array($link, sam_get_bookstores())) {
                array_push($temp_links, $link);
            }
        }
        $links = implode("|", $temp_links);
        update_post_meta($post_id, 'sam_bookstore_links', $links);
    } else {
        // empty? then zero meta val
        update_post_meta($post_id, 'sam_bookstore_links', '');
    }

    if (isset($_REQUEST['sam_custom_bookstore_title'])) {
        $sam_custom_bookstore_title = sanitize_text_field($_REQUEST['sam_custom_bookstore_title']);
        update_post_meta($post_id, 'sam_custom_bookstore_title', $sam_custom_bookstore_title);
    }


    if (isset($_REQUEST['sam_custom_bookstore_url'])) {
        if (valid_url($_REQUEST['sam_custom_bookstore_url'])) {
            update_post_meta($post_id, 'sam_custom_bookstore_url', esc_url($_REQUEST['sam_custom_bookstore_url']));
        } else if (empty($_REQUEST['sam_custom_bookstore_url'])) {
            update_post_meta($post_id, 'sam_custom_bookstore_url', '');
        }
    } else {
        update_post_meta($post_id, 'sam_custom_bookstore_url', '');
    }


}

function valid_url($url)
{
    return (filter_var($url, FILTER_VALIDATE_URL) !== false);
}

// add before story
function sam_content($content)
{
    global $post;

    if (is_post_type_archive(array('sam_story', 'sam_book')) and in_the_loop()) {
        return sam_do_archive($content);
    }

    if (is_singular(array('sam_story', 'sam_book')) and in_the_loop()) {
        return sam_do_single($content);
    }

    return $content;

}

function sam_do_archive($content)
{
    global $post;

    $html = '';

    $sam_cover_image = get_post_meta($post->ID, 'sam_cover_image', true);
    $sam_title = get_the_title();
    $sam_byline = get_post_meta($post->ID, 'sam_byline', true);
    $sam_pub_name = get_post_meta($post->ID, 'sam_pub_name', true);
    $sam_publisher = get_post_meta($post->ID, 'sam_publisher', true);
    $sam_pub_date = get_post_meta($post->ID, 'sam_pub_date', true);
    if ($sam_pub_date == PHP_INT_MAX) {
        $sam_pub_date = '<em>Forthcoming</em>';
    } else {
        $sam_pub_date = date('F j, Y', $sam_pub_date);
    }


    $html .= '<div class="sam-story-header">';
    if (!empty($sam_cover_image)) {
        $html .= '<div class="sam-cover-image archive"><a href="' . get_permalink() . '"><img src="' . esc_url($sam_cover_image) . '"></a></div>';
    }
    $html .= '<div class="sam-title sam-meta"><a href="' . get_permalink() . '">';

    if ($post->post_type == 'sam_book') {
        $html .= '<em>' . esc_html($sam_title) . '</em> ';
    } else {
        $html .= '&ldquo;' . esc_html($sam_title) . '.&rdquo; ';
    }
    $html .= '</a></div>';
    $html .= '<div class="sam-byline sam-meta">By ' . esc_html($sam_byline) . '</div>';
    $html .= '<div class="sam-pub-name sam-meta"><em>' . esc_html($sam_pub_name) . '</em></div>';
    $html .= '<div class="sam-publisher sam-meta">' . esc_html($sam_publisher) . '</div>';
    $html .= '<div class="sam-pub-date sam-meta">' . $sam_pub_date . '</div>';
    $html .= '<div class="sam-more-info sam-meta"><a href="' . get_permalink() . '">More information</a></div>';


    $html .= '</div>';

    return $html;
}

// change archive order based on settings

add_action('pre_get_posts', 'sam_change_archive_order');
function sam_change_archive_order($query)
{

    if (is_admin()) return;

    if (is_archive(array('sam_story', 'sam_book'))) {
        $query->set('order', get_option('sam_biblio_sort_dir'));
        $query->set('orderby', get_option('sam_biblio_sort_by'));
    }

}

function sam_do_single($content)
{
    global $post;

    $html = '';

    $sam_cover_image = get_post_meta($post->ID, 'sam_cover_image', true);
    $sam_title = get_the_title();
    $sam_reprint = get_post_meta($post->ID, 'sam_reprint', true);
    $sam_byline = get_post_meta($post->ID, 'sam_byline', true);
    $sam_pub_name = get_post_meta($post->ID, 'sam_pub_name', true);
    $sam_publisher = get_post_meta($post->ID, 'sam_publisher', true);
    $sam_editors = get_post_meta($post->ID, 'sam_editors', true);
    $sam_language = get_post_meta($post->ID, 'sam_language', true);
    $sam_translator = get_post_meta($post->ID, 'sam_translator', true);
    $sam_accolades = get_post_meta($post->ID, 'sam_accolades', true);
    $sam_pub_date = get_post_meta($post->ID, 'sam_pub_date', true);
    $sam_url = get_post_meta($post->ID, 'sam_url', true);
    $sam_url_text = get_post_meta($post->ID, 'sam_url_text', true);
    $sam_isbn = get_post_meta($post->ID, 'sam_isbn', true);
    $sam_podcast_url = get_post_meta($post->ID, 'sam_podcast_url', true);
    $sam_podcast_url_text = get_post_meta($post->ID, 'sam_podcast_url_text', true);
    $sam_bookstore_links = get_post_meta($post->ID, 'sam_bookstore_links', true);


    $html .= '<div class="sam-story-header">';

    if (!empty($sam_cover_image)) {
        $html .= '<div class="sam-cover-image"><a href="' . esc_url($sam_cover_image) . '"><img src="' . esc_url($sam_cover_image) . '"></a></div>';
    }

    if ($post->post_type == 'sam_book') {
        $html .= '<div class="sam-title sam-meta"><em>' . esc_html($sam_title) . '</em></div>';
    } else {
        $html .= '<div class="sam-title sam-meta">&ldquo;' . esc_html($sam_title) . '&rdquo;</div>';

    }


    if (!empty($sam_byline)) {
        $html .= '<div class="sam-byline sam-meta">By ' . esc_html($sam_byline) . '</div>';
    }

    if (!empty($sam_pub_name)) {
        $html .= '<div class="sam-pub-name sam-meta"><em>' . esc_html($sam_pub_name) . '</em></div>';
    }

    if (!empty($sam_publisher)) {
        $html .= '<div class="sam-publisher sam-meta">' . esc_html($sam_publisher) . '</div>';
    }

    if (!empty($sam_editors)) {
        if (count(explode(',', $sam_editors)) > 1) {
            $html .= '<div class="sam-editors sam-meta">Eds. ' . esc_html($sam_editors) . '</div>';
        } else {
            $html .= '<div class="sam-editors sam-meta">Ed. ' . esc_html($sam_editors) . '</div>';
        }
    }

    if (!empty($sam_pub_date)) {
        if ($sam_pub_date == PHP_INT_MAX) {
            $html .= '<div class="sam-pub-date sam-meta"><em>Forthcoming</em></div>';
        } else {
            $html .= '<div class="sam-pub-date sam-meta">' . date('F j, Y', $sam_pub_date) . '</div>';
        }
    }

    if (!empty($sam_reprint)) {
        $html .= '<div class="sam-reprint sam-meta"><em>Reprint</em></div>';
    }

    if (!empty($sam_language)) {
        $html .= '<div class="sam-language sam-meta">' . esc_html($sam_language) . ' Language</div>';
    }

    if (!empty($sam_translator)) {
        $html .= '<div class="sam-translator sam-meta">Translated by ' . esc_html($sam_translator) . '</div>';
    }

    if (!empty($sam_isbn)) {
        $html .= '<div class="sam-isbn sam-meta">ISBN ' . esc_html($sam_isbn) . '</div>';
    }

    if (!empty($sam_accolades)) {
        $html .= '<div class="sam-accolades sam-meta"><strong>' . esc_html($sam_accolades) . '</strong></div>';
    }


    if (empty($sam_url_text)) {
        $sam_url_text = "More info";
    }


    if (!empty($sam_url)) {
        $html .= '<div class="sam-url sam-meta"><a target="_blank" href="' . esc_url($sam_url) . '">' . $sam_url_text . '</a></div>';
    }

    if (empty($sam_podcast_url_text)) {
        $sam_podcast_url_text = "Listen now";
    }

    if (!empty($sam_podcast_url)) {
        $html .= '<div class="sam-podcast-url sam-meta"><a target="_blank" href="' . esc_url($sam_podcast_url) . '">' . $sam_podcast_url_text . '</a></div>';
    }

    if (!empty($sam_bookstore_links)) {
        $html .= get_bookstore_links($sam_bookstore_links, $post->ID, $sam_isbn, $sam_pub_name, $sam_title);
    }

    $html .= '</div>';

    $content = $html . $content;


    return $content;
}

function get_bookstore_links($bookstores, $post_id, $isbn, $pub_name, $title)
{
    $html = '<div class="sam-meta sam-bookstore-link">';
    $html .= 'Get a copy<br>';

    $links = [];

    $keyword = $isbn;
    if (empty($keyword)) {
        $keyword = $pub_name;
    }
    if (empty($keyword)) {
        $keyword = $title;
    }

    $isbn_13 = get_post_meta($post_id, 'sam_isbn', true);
    $isbn = isbn13_to_10($isbn_13);

    $amazon_tag = get_option('sam_amazon_affiliate_tag');

    $keyword = urlencode($keyword);

    foreach (explode('|', $bookstores) as $bookstore) {

        if ($bookstore == 'amazon') {
            // affiliate tag
            $tag = (!empty($amazon_tag)) ? "tag=$amazon_tag" : '';
            if (!empty($isbn) and !str_starts_with($isbn_13,'979-')) {
                $links[] = '<a class="sam-bookstore-link" target="_blank" href="https://www.amazon.com/dp/' . $isbn . '/?' . $tag . '">Amazon</a>';
            } else {
                $links[] = '<a class="sam-bookstore-link" target="_blank" href="https://www.amazon.com/s?field-keywords=' . $keyword . '&' . $tag . '">Amazon</a>';
            }
        }
        if ($bookstore == 'barnes_and_noble')
            $links[] = '<a class="sam-bookstore-link" target="_blank" href="https://www.barnesandnoble.com/s/' . $keyword . '">Barnes &amp; Noble</a>';

        if ($bookstore == 'google_play')
            $links[] = '<a class="sam-bookstore-link" target="_blank" href="https://play.google.com/store/search?q=' . $keyword . '&c=books">Google Play</a>';

        if ($bookstore == 'kobo')
            $links[] = '<a class="sam-bookstore-link" target="_blank" href="https://www.kobo.com/us/en/search?Query=' . $keyword . '">Kobo</a>';

        if ($bookstore == 'indiebound')
            $links[] = '<a class="sam-bookstore-link" target="_blank" href="https://www.indiebound.org/search/book?keys=' . $keyword . '">Indie Bound</a>';

        if ($bookstore == 'custom') {
            $custom_title = get_post_meta($post_id, 'sam_custom_bookstore_title', true);
            $custom_url = get_post_meta($post_id, 'sam_custom_bookstore_url', true);
            if (!empty($custom_title) and !empty($custom_url)) {
                $links[] = '<a class="sam-bookstore-link" target="_blank" href="' . $custom_url . '">' . $custom_title . '</a>';
            }
        }
    }


    $html .= implode(' | ', $links);
    $html .= '</div>';
    return $html;


}


// shortcode function
function sam_biblio($atts)
{

    $opts = shortcode_atts(array(
        'orderby' => get_option('sam_biblio_sort_by'),
        'order' => get_option('sam_biblio_sort_dir'),
        'sort_reprints' => get_option('sam_biblio_sort_reprints'),
        'date_headings' => get_option('sam_biblio_date_headings'),
        'type' => 'story,book'
    ), $atts);


    $args = sam_get_biblio_args($opts);

    $my_query = new WP_Query($args);

    if ($my_query->have_posts()) {
        return sam_do_biblio($my_query, $opts);
    }

}

function sam_get_biblio_args($opts, $reprint_id = 0)
{

    $post_type = array();
    if (empty($opts['type'])) {
        $post_type = array('sam_story', 'sam_book');
    } else {
        foreach (explode(',', $opts['type']) as $type) {
            if (trim($type) == 'story') array_push($post_type, 'sam_story');
            if (trim($type) == 'book') array_push($post_type, 'sam_book');
        }
    }

    // wp_query args
    $args = array('post_type' => $post_type, 'post_status' => 'publish', 'order' => esc_sql($opts['order']), 'posts_per_page' => -1);


    // sorting options
    switch ($opts['orderby']) {
        case 'title':
            $args = array_merge($args, array('orderby' => 'title'));
            break;
        case 'date':
            $args = array_merge($args, array('meta_key' => 'sam_pub_date', 'orderby' => 'meta_value_num'));
            break;
        default: // default by date
            $args = array_merge($args, array('meta_key' => 'sam_pub_date', 'orderby' => 'meta_value_num'));
            break;
    }

    if ($reprint_id != 0) {
        $args = array_merge($args, array('meta_query' => array('key' => 'sam_reprint_id', 'compare' => '=', 'value' => $reprint_id)));
    }

    return $args;
}

// display the biblio
function sam_do_biblio($my_query, $opts)
{

    $html = '';
    $html .= '<ul class="sam_biblio">';
    if ($opts['date_headings']) {
        $date_headings = [];
    }
    while ($my_query->have_posts()) {

        $my_query->the_post();

        $pub_date = get_post_meta($my_query->post->ID, 'sam_pub_date', true);
        $reprint = get_post_meta($my_query->post->ID, 'sam_reprint', true);
        $reprint_id = get_post_meta($my_query->post->ID, 'sam_reprint_id', true);

        if ($opts['date_headings']) {
            if ($pub_date == PHP_INT_MAX) {
                if (!$date_headings['forthcoming']) {
                    $html .= '<h3>Forthcoming</h3>';
                    $date_headings['forthcoming']++;
                }
            } else {
                $year = date('Y', $pub_date);
                if (!$date_headings[$year]) {
                    $html .= '<h3>' . $year . '</h3>';
                    $date_headings[$year]++;
                }
            }
        }

        if ($opts['sort_reprints'] and ($reprint == 1)) continue;
        $html .= sam_do_biblio_single($my_query->post);
        if ($opts['sort_reprints']) {
            $args = sam_get_biblio_args($opts, $my_query->post->ID);
            //var_dump($args);
            //exit;
            $reprint_query = new WP_Query($args);

            if ($reprint_query->have_posts()) {
                $html .= '<ul>';
                while ($reprint_query->have_posts()) {
                    $reprint_query->the_post();
                    $html .= sam_do_biblio_single($reprint_query->post);
                }
                $html .= '</ul>';

            }
        }


    }
    $html .= '</ul>';
    wp_reset_postdata();
    return $html;
}

function sam_do_biblio_single($post)
{

    $html = '';

    $title = get_the_title();
    // $byline = get_post_meta($my_query->post->ID, 'sam_byline', true);
    $pub_name = get_post_meta($post->ID, 'sam_pub_name', true);
    $publisher = get_post_meta($post->ID, 'sam_publisher', true);
    $editors = get_post_meta($post->ID, 'sam_editors', true);
    $language = get_post_meta($post->ID, 'sam_language', true);
    $translator = get_post_meta($post->ID, 'sam_translator', true);
    $accolades = get_post_meta($post->ID, 'sam_accolades', true);
    $pub_date = get_post_meta($post->ID, 'sam_pub_date', true);
    $isbn = get_post_meta($post->ID, 'sam_isbn', true);
    $reprint = get_post_meta($post->ID, 'sam_reprint', true);
    //$reprint_id = get_post_meta($post->ID, 'sam_reprint_id', true);


    $html .= '<li>';

    $html .= '<a href="' . get_permalink() . '">';
    if ($post->post_type == 'sam_book') {
        $html .= '<em>' . $title;
        if (!preg_match("/[.!?,;:]$/", $title)) $html .= '.';
        $html .= '</em> ';
    } else {
        $html .= '&ldquo;' . $title;
        if (!preg_match("/[.!?,;:]$/", $title)) $html .= '.';
        $html .= '&rdquo; ';
    }
    $html .= '</a>';

    if (!empty($pub_name)) {
        $html .= '<em>' . esc_html($pub_name) . '</em>. ';
    }

    if (!empty($editors)) {
        (count(explode(',', esc_html($editors))) > 1) ? $eds = 'Eds.' : $eds = 'Ed.';
        $html .= $eds . ' ' . $editors . '. ';
    }

    if (!empty($publisher)) {
        $html .= "$publisher. ";
    }

    if (!empty($language)) {
        $html .= esc_html($language) . ' language. ';
    }

    if (!empty($translator)) {
        $html .= 'Translated by ' . esc_html($translator) . '. ';
    }

    if ($pub_date == PHP_INT_MAX) {
        $html .= '<em>Forthcoming</em>. ';
    } else {
        $html .= date('F j, Y', $pub_date) . '. ';
    }

    if (!empty($reprint)) {
        $html .= 'Reprint. ';
    }

    if (!empty($isbn)) {
        $html .= 'ISBN ' . esc_html($isbn) . '. ';
    }

    if (!empty($accolades)) {
        $html .= '<strong>' . esc_html($accolades) . '</strong>. ';
    }

    $html .= '<a href="' . get_permalink() . '">More info</a>';

    $html .= '</li>';

    return $html;
}

function sam_posts_where($where, &$wp_query)
{
    global $wpdb;
    if ($sam_title = $wp_query->get('sam_title')) {
        $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql($wpdb->esc_like($sam_title)) . '%\'';
    }
    return $where;
}

function sam_story_columns($columns)
{

    foreach ($columns as $column => $val) {
        $new_columns[$column] = $val;
        if ($column === 'title') {
            $new_columns['sam_pub_name'] = __('Publisher / Venue');
            $new_columns['sam_reprint'] = __('Reprint');
            $new_columns['sam_pub_date'] = __('Publication Date');
            $new_columns['sam_show_in_slider'] = __('Show in slider');
        }
    }

    return $new_columns;

}

add_filter('manage_sam_story_posts_columns', 'sam_story_columns');
add_filter('manage_sam_book_posts_columns', 'sam_story_columns');

function sam_story_custom_columns($column)
{
    global $post;

    switch ($column) {
        case 'sam_pub_name':
            $pub_name = get_post_meta($post->ID, 'sam_pub_name', true);
            echo esc_html($pub_name);
            break;
        case 'sam_reprint':
            $reprint = get_post_meta($post->ID, 'sam_reprint', true);
            echo ($reprint == '1') ? '<em>Reprint</em>' : '';
            break;
        case 'sam_pub_date':
            $pub_date = get_post_meta($post->ID, 'sam_pub_date', true);
            if ($pub_date == PHP_INT_MAX) {
                echo '<em>Forthcoming</em>';
            } else {
                echo date('F j, Y', $pub_date);
            }
            break;
        case 'sam_show_in_slider' :
            echo (get_post_meta($post->ID, 'sam_show_in_slider', true) == 1) ? 'Yes' : 'No';
            break;

    }
}

add_action('manage_sam_story_posts_custom_column', 'sam_story_custom_columns');
add_action('manage_sam_book_posts_custom_column', 'sam_story_custom_columns');

// Make these columns sortable
function sam_sortable_columns($columns)
{
    $columns['sam_pub_name'] = 'sam_pub_name';
    $columns['sam_pub_date'] = 'sam_pub_date';
    $columns['sam_show_in_slider'] = 'sam_show_in_slider';
    return $columns;
}

add_filter("manage_edit-sam_story_sortable_columns", "sam_sortable_columns");
add_filter("manage_edit-sam_book_sortable_columns", "sam_sortable_columns");

add_action('pre_get_posts', 'sam_admin_sort_by_date_and_pub_name', 1);
function sam_admin_sort_by_date_and_pub_name($query)
{

    /* if( ! is_admin() )
         return;*/

    /**
     * We only want our code to run in the main WP query
     * AND if an orderby query variable is designated.
     */
    if ($query->is_main_query() && ($orderby = $query->get('orderby'))) {

        switch ($orderby) {
            case 'sam_pub_date':
                $query->set('meta_key', 'sam_pub_date');
                $query->set('orderby', 'meta_value_num');
                break;
            case 'sam_pub_name':
                $query->set('meta_key', 'sam_pub_name');
                $query->set('orderby', 'meta_value');
                break;
            case 'sam_show_in_slider':
                $query->set('meta_key', 'sam_show_in_slider');
                $query->set('orderby', 'meta_value');
                break;

        }

    }

}

// create custom plugin settings menu
add_action('admin_menu', 'sam_create_menu');

function sam_create_menu()
{
    //create new top-level menu
    add_submenu_page('sam-options', 'Sunray Author Manager Settings', 'Settings', 'manage_options', 'sam-options-settings', 'sam_settings_page');

    //call register settings function
    add_action('admin_init', 'sam_settings');
}

//register our settings
function sam_settings()
{

    register_setting('sam-settings-group', 'sam_slider_speed');
    register_setting('sam-settings-group', 'sam_slider_sort_by');
    register_setting('sam-settings-group', 'sam_slider_sort_dir');
    register_setting('sam-settings-group', 'sam_slider_height');
    register_setting('sam-settings-group', 'sam_biblio_sort_by');
    register_setting('sam-settings-group', 'sam_biblio_sort_dir');
    register_setting('sam-settings-group', 'sam_biblio_sort_reprints');
    register_setting('sam-settings-group', 'sam_biblio_date_headings');
    register_setting('sam-settings-group', 'sam_stories_slug');
    register_setting('sam-settings-group', 'sam_books_slug');
    register_setting('sam-settings-group', 'sam_amazon_affiliate_tag');
    //register_setting('sam-settings-group', 'sam_show_bookstore_links');
    //register_setting('sam-settings-group', 'sam_bookstore_links');


}

// settings page
function sam_settings_page()
{
    $slider_speed = get_option('sam_slider_speed') ? get_option('sam_slider_speed') : '8';
    $slider_sort_by = get_option('sam_slider_sort_by') ? get_option('sam_slider_sort_by') : 'date';
    $slider_sort_dir = get_option('sam_slider_sort_dir') ? get_option('sam_slider_sort_dir') : 'desc';
    $slider_height = get_option('sam_slider_height') ? get_option('sam_slider_height') : '250';

    $biblio_sort_by = get_option('sam_biblio_sort_by') ? get_option('sam_biblio_sort_by') : 'date';
    $biblio_sort_dir = get_option('sam_biblio_sort_dir') ? get_option('sam_biblio_sort_dir') : 'desc';
    $biblio_sort_reprints = (get_option('sam_biblio_sort_reprints') != 0) ? get_option('sam_biblio_sort_reprints') : '0';
    $biblio_date_headings = (get_option('sam_biblio_date_headings') != 0) ? get_option('sam_biblio_date_headings') : '0';

    $stories_slug = get_option('sam_stories_slug') ? get_option('sam_stories_slug') : 'stories';
    $books_slug = get_option('sam_books_slug') ? get_option('sam_books_slug') : 'books';

    $amazon_tag = get_option('sam_amazon_affiliate_tag');

    //$show_bookstore_links = get_option('sam_show_bookstore_links') ? get_option('sam_show_bookstore_links') : '0';

    ?>
    <div class="wrap">
        <h1>Sunray Author Manager Settings</h1>
        <p class="sam-check-out">Created by Matthew Kressel. I write science fiction and fantasy. <a target="_blank"
                                                                                                     href="http://www.matthewkressel.net/">Check
                out my books.</a></p>
        <p>Here you can change the default settings. <em>These settings will be overridden if shortcode options are
                provided.</em></p>
        <p><a href="https://wordpress.org/plugins/sunray-author-manager/">Like this plugin? Please rate it!</a></p>

        <form method="post" action="options.php">
            <?php settings_fields('sam-settings-group'); ?>
            <?php do_settings_sections('sam-settings-group'); ?>
            <h3>Slider Settings</h3>

            <table class="form-table sam-form-table">
                <tr valign="top">
                    <td colspan="3">
                        <div class="sam-highlight">To add a slider, add the shortcode <strong>[sam-slider]</strong> to your posts or within your WordPress theme. For all options, <a href="https://wordpress.org/plugins/sunray-author-manager/#what%20are%20the%20shortcodes%20and%20their%20options%3F" target="_blank">click here</a>.</div>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Speed (Seconds)</th>
                    <td><input type="text" name="sam_slider_speed"
                               value="<?php echo esc_attr($slider_speed); ?>"/></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sort By</th>
                    <td>
                        <select name="sam_slider_sort_by">
                            <option value="date">Date</option>
                            <option value="title" <?php echo ($slider_sort_by == 'title') ? 'selected' : ''; ?>>
                                Title
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sort Direction</th>
                    <td>
                        <select name="sam_slider_sort_dir">
                            <option value="desc">Descending</option>
                            <option value="asc" <?php echo ($slider_sort_dir == 'asc') ? 'selected' : ''; ?>>
                                Ascending
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Height (Pixels)</th>
                    <td><input type="text" name="sam_slider_height"
                               value="<?php echo esc_attr($slider_height); ?>"/> (For optimum results, maximum
                        height
                        should be less than 1024 pixels)
                    </td>
                </tr>
            </table>

            <h3>Bibliography Settings</h3>
            <table class="form-table sam-form-table">
                <tr valign="top">
                    <th scope="row">Sort By</th>
                    <td>
                        <select name="sam_biblio_sort_by">
                            <option value="date">Date</option>
                            <option value="title" <?php echo ($biblio_sort_by == 'title') ? 'selected' : ''; ?>>
                                Title
                            </option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Sort Direction</th>
                    <td>
                        <select name="sam_biblio_sort_dir">
                            <option value="desc">Descending</option>
                            <option value="asc" <?php echo ($biblio_sort_dir == 'asc') ? 'selected' : ''; ?>>
                                Ascending
                            </option>
                        </select>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Sort Reprints</th>
                    <td>
                        <select name="sam_biblio_sort_reprints">
                            <option value="0">No</option>
                            <option value="1" <?php echo ($biblio_sort_reprints == '1') ? 'selected' : ''; ?>>Yes
                            </option>
                        </select>
                        <a name="sort-reprints"></a>When set to Yes, reprints are indented under the original
                        publication (by title). When set to
                        No, all publications are listed chronologically.
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Date Headings</th>
                    <td>
                        <select name="sam_biblio_date_headings">
                            <option value="0">No</option>
                            <option value="1" <?php echo ($biblio_date_headings == '1') ? 'selected' : ''; ?>>Yes
                            </option>
                        </select>
                        When set to Yes, publications are separated by year and "Forthcoming".
                    </td>
                </tr>
            </table>

            <h3>URL Settings</h3>
            <table class="form-table sam-form-table">
                <tr valign="top">
                    <th scope="row">Stories slug (URL prefix)</th>
                    <td><?php echo get_site_url() . '/'; ?>
                        <input type="text" name="sam_stories_slug"
                               value="<?php echo esc_attr($stories_slug); ?>"/>
                        <?php echo '/my-story-name'; ?></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Books slug (URL prefix)</th>
                    <td><?php echo get_site_url() . '/'; ?>
                        <input type="text" name="sam_books_slug"
                               value="<?php echo esc_attr($books_slug); ?>"/>
                        <?php echo '/my-book-name'; ?></td>
                </tr>


            </table>

            <h3>Misc Settings</h3>
            <table class="form-table sam-form-table">
                <tr valign="top">
                    <th scope="row">Amazon Affiliate Tag</th>
                    <td><input type="text" name="sam_amazon_affiliate_tag"
                               value="<?php echo esc_attr($amazon_tag); ?>"/>
                        <br><em>E.g. "myamazontag-20"</em>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>

            <p>Plugin Version <?php echo get_option('sam_version'); ?></p>
            <p><em>This plugin makes use of the brilliant <a href="http://kenwheeler.github.io/slick/">Slick.js
                        jQuery
                        carousel</a>.</em></p>

        </form>
    </div>
<?php }


// enqueue css for users
//add_action('wp_enqueue_scripts', 'sam_enqueue' );

function sam_slider($atts)
{

    $opts = shortcode_atts(array(
        'orderby' => get_option('sam_slider_sort_by'),
        'order' => get_option('sam_slider_sort_dir'),
        'type' => 'story, book',
        'height' => get_option('sam_slider_height'),
        'speed' => get_option('sam_slider_speed'),
        'post_ids' => '',
    ), $atts);


    $height = $opts['height'];
    $speed = $opts['speed'];

    $post_type = array();
    if (empty($opts['type'])) {
        $post_type = array('sam_story', 'sam_book');
    } else {
        foreach (explode(',', $opts['type']) as $type) {
            if (trim($type) == 'story') array_push($post_type, 'sam_story');
            if (trim($type) == 'book') array_push($post_type, 'sam_book');
        }
    }

    $post_ids = [];
    // manual ids? sanitize
    if (!empty($opts['post_ids'])) {
        foreach (explode(',', trim($opts['post_ids'])) as $post_id) {
            if (is_numeric($post_id)) {
                array_push($post_ids, $post_id);
            }
        }
        if (!empty($post_ids)) {
            $args = array('post_type' => array('sam_story', 'sam_book'), 'post_status' => 'publish', 'order' => $opts['order'], 'post__in' => $post_ids, 'posts_per_page' => -1);
            $manual = true;
        }
    } else {
        // wp_query args
        $args = array('post_type' => $post_type, 'post_status' => 'publish', 'order' => $opts['order'], 'posts_per_page' => -1);
    }

    // sorting options
    switch ($opts['orderby']) {
        case 'title':
            $args = array_merge($args, array('orderby' => 'title'));
            break;
        case 'date':
            $args = array_merge($args, array('meta_key' => 'sam_pub_date', 'orderby' => 'meta_value_num'));
            break;
        default: // default by date
            $args = array_merge($args, array('meta_key' => 'sam_pub_date', 'orderby' => 'meta_value_num'));
            break;
    }

    $my_query = new WP_Query($args);

    if ($my_query->have_posts()) {
        return sam_do_slider($my_query, $height, $speed, count($post_ids));
    } else {
        return 'No posts found';
    }

}

// display the slider
function sam_do_slider($my_query, $slide_height, $slide_speed, $post_ids_specified)
{

    $html = '';


    // safe defaults
    if (empty($slide_height) or !is_numeric($slide_height)) {
        $slide_height = get_option('sam_slider_height');
        if (empty($slide_height)) $slide_height = 250;
    }

    if (empty($slide_speed) or !is_numeric($slide_speed)) {
        $slide_speed = get_option('sam_slider_speed');
        if (empty($slide_speed)) $slide_speed = 8;
    }

    // insert speed into html via js
    // $html .= '<script type="text/javascript"> sam_options = {"slider_speed":"'.$slide_speed.'"};</script>';

    /*// set speed setting in js
    $sam_options = array(
        'slider_speed' => $slide_speed,
    );
    wp_localize_script('sam-user-js', 'sam_options', $sam_options);*/


    $html .= '<div class="sam-slider-loading"></div>';
    $html .= '<div class="sam-slider" data-slide-speed="' . $slide_speed . '">';


    while ($my_query->have_posts()) {

        $my_query->the_post();

        // skip if not shown
        if ((get_post_meta($my_query->post->ID, 'sam_show_in_slider', true) != 1) and ($post_ids_specified == 0)) {
            continue;
        }


        // get large thumbnail from image
        $image_url = get_post_meta($my_query->post->ID, 'sam_cover_image', true);
        $image_id = attachment_url_to_postid($image_url);
        $image_src = wp_get_attachment_image_src($image_id, 'medium');

        // use thumbnail, or full size
        if (is_array($image_src)) {
            $sam_cover_image = $image_src[0];
        } else {
            $sam_cover_image = $image_url;
        }

        if (empty($sam_cover_image)) continue;

        $pub_name = get_post_meta($my_query->post->ID, 'sam_pub_name', true);

        $html .= '<a class="sam-slide-link" href="' . get_permalink() . '">';
        $html .= '<div class="sam-slide">'; // begin slide
        $html .= '<img class="sam-slide-image" style="height: ' . esc_attr($slide_height) . 'px !important;" src="' . esc_url($sam_cover_image) . '">';

        $html .= '<div class="sam-slide-overlay">'; // begin overlay
        $html .= '<div class="sam-center-text">';

        if ($my_query->post->post_type == 'sam_book') {
            $html .= '<em>' . get_the_title() . '</em><br>';
        } else {
            $html .= '&ldquo;' . get_the_title() . '&rdquo;<br>';
        }

        if (!empty($pub_name)) {
            $html .= '<em>' . esc_html($pub_name) . '</em>';
        }
        $html .= '<div class="sam-more-info">More</div>';
        $html .= '</div>';

        $html .= '</div>'; // end overlay*/
        $html .= '</div>'; // end slide
        $html .= '</a>';

    }
    $html .= '</div>';
    wp_reset_postdata();
    return $html;
}

function sam_update_db_check()
{
    global $sam_version;

    // set to zero if not found
    if (empty(get_site_option('sam_version'))) {
        update_option("sam_version", "0.0.0");
    }

    $site_version = explode('.', get_site_option('sam_version'));
    //$this_version = explode('.',$sam_version);

    // less than 1.0.0, add sam_show_in_slider to true for all
    if ($site_version[0] < 1) {
        $posts = get_posts(array('posts_per_page' => -1, 'post_type' => array('sam_story', 'sam_book')));
        foreach ($posts as $post) {
            update_post_meta($post->ID, 'sam_show_in_slider', 1);
        }
    }

    // update reprint ids
    if ($sam_version[0] == 1 and $sam_version[1] == 0 and $sam_version[2] < 6) {
        //if (true) {
        $posts = get_posts(array('posts_per_page' => -1, 'post_type' => array('sam_story', 'sam_book')));
        foreach ($posts as $post) {
            $reprint = get_post_meta($post->ID, 'sam_reprint', true);
            $reprint_id = get_post_meta($post->ID, 'sam_reprint_id', true);
            if ($reprint == 1 and $reprint_id == '') {
                //if ($reprint == 1) {
                $reprint_id = guess_original_publication($post);
                update_post_meta($post->ID, 'sam_reprint_id', $reprint_id);

            }
        }
    }


    // now make sure to set sam version
    update_option("sam_version", $sam_version);

}

function isbn13_to_10($isbn)
{
    $isbn = str_replace('-', '', $isbn);

    if (preg_match('/^\d{3}(\d{9})\d$/', $isbn, $m)) {
        $sequence = $m[1];
        $sum = 0;
        $mul = 10;
        for ($i = 0; $i < 9; $i++) {
            $sum = $sum + ($mul * (int)$sequence[$i]);
            $mul--;
        }
        $mod = 11 - ($sum % 11);
        if ($mod == 10) {
            $mod = "X";
        } else if ($mod == 11) {
            $mod = 0;
        }
        $isbn = $sequence . $mod;
    }
    return $isbn;
}


