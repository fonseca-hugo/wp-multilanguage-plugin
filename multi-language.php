<?php
/**
 * @package Multi_Language
 * @version 0.1
 */
/*
Plugin Name: Multi-Language
Plugin URI: https://github.com/fonseca-hugo/wp-multilanguage-plugin
Description: This is a multi-language plugin (WIP)
Author: Hugo Fonseca
Version: 0.1
Author URI: http://hugofonseca.co.uk/
*/

// Add language field to posts/pages
add_action("admin_init", "admin_init");
add_action("save_post", "save_meta_fields");

// Add language filter to posts/pages
add_action("restrict_manage_posts", "af_admin_posts_filter");
add_filter("parse_query", "af_posts_filter");

/**
 * Hook that runs when admin_init is run
 */
function admin_init() {
    $postTypes = getValidPostTypes();
    foreach ($postTypes as $type) {
        add_meta_box("lang-meta", "Properties", "meta_options", $type, "normal", "high");
    }
}

/**
 * Renders the Custom Fields inputs
 * @param $post
 */
function meta_options($post) {
    $custom = get_post_custom($post->ID);
    $lang = !empty($custom["lang"][0]) ? $custom["lang"][0] : 'en';
    $metaTitle = !empty($custom["meta-title"][0]) ? $custom["meta-title"][0] : '';
    $metaDescription = !empty($custom["meta-desc"][0]) ? $custom["meta-desc"][0] : '';

    // We'll use this nonce field later on when saving.
    wp_nonce_field('meta_box_nonce', 'meta_box_nonce');

    $languages = getAvailableLanguages();

    echo "<div id='namediv'>";

    echo "<div>";
    echo "<label for='lang'>Language: </label>";
    echo "<select id='lang' name='lang'>";
    foreach ($languages as $key => $name) {
        echo "<option value='".$key."'".selected($lang, $key, 0).">".$name."</option>";
    }
    echo "</select>";

    echo "</div>";
    echo "<div>";
    echo "<label for='meta_title'>Meta Title:</label>";
    echo "<input type='text' name='meta_title' value='".$metaTitle."' />";
    echo "</div>";
    echo "<div>";
    echo "<label for='meta_desc'>Meta Description:</label>";
    echo "<input type='text' name='meta_desc' value='".$metaDescription."' />";
    echo "</div>";

    echo "</div>";
}

/**
 * Returns the available languages
 * //todo get this from an admin interface instead of hardcoding
 * @return array
 */
function getAvailableLanguages() {
    $languages = [
        'en' => 'English',
        'fr' => 'French',
        'es' => 'Spanish',
    ];

    return $languages;
}

/**
 * Hook to update the meta options with the language field
 * @param int $postID
 */
function save_meta_fields($postID) {
    // Bail if we're doing an auto save
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // if our nonce isn't there, or we can't verify it, bail
    if (!isset($_POST['meta_box_nonce']) || !wp_verify_nonce($_POST['meta_box_nonce'], 'meta_box_nonce')) {
        return;
    }

    // if our current user can't edit this post, bail
    if (!current_user_can('edit_post')) {
        return;
    }
    $lang = !empty($_POST["lang"]) ? esc_attr($_POST["lang"]) : 'en';
    $metaTitle = !empty($_POST["meta_title"]) ? esc_attr($_POST["meta_title"]) : '';
    $metaDesc = !empty($_POST["meta_desc"]) ? esc_attr($_POST["meta_desc"]) : '';

    update_post_meta($postID, "lang", $lang);
    update_post_meta($postID, "meta-title", $metaTitle);
    update_post_meta($postID, "meta-desc", $metaDesc);
}

/**
 * Add the Language Filter Dropdown
 *
 * @return void
 */
function af_admin_posts_filter() {
    $type = !empty($_GET['post_type']) ? $_GET['post_type'] : 'post';
    $lang = !empty($_GET['lang_filter']) ? $_GET['lang_filter'] : '';

    //only add filter to post type you want
    if (isValidPostType($type)) {
        $languages = getAvailableLanguages();

        echo "<select id='langFilter' name='lang_filter'>";
        echo "<option value=''>All Languages</option>";
        foreach ($languages as $key => $name) {
            echo "<option value='".$key."'".selected($lang, $key, 0).">".$name."</option>";
        }
        echo "</select>";
    }
}

/**
 * Filter by post meta
 *
 * @param  (wp_query object) $query
 *
 * @return Void
 */
function af_posts_filter($query) {
    global $pagenow;
    $type = !empty($_GET['post_type']) ? $_GET['post_type'] : 'post';
    $langFilter = !empty($_GET['lang_filter']) ? $_GET['lang_filter'] : '';

    if (isValidPostType($type) && is_admin() && $pagenow == 'edit.php' && !empty($_GET['lang_filter'])) {
        $query->query_vars['meta_key'] = 'lang';
        $query->query_vars['meta_value'] = $langFilter;
    }
}

function isValidPostType($type) {
    $result = false;
    $postTypes = getValidPostTypes();

    if (array_search($type, $postTypes) !== false) {
        $result = true;
    }

    return $result;
}

function getValidPostTypes() {
    return [
        'post',
        'page',
    ];
}
