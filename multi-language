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


add_action("admin_init", "admin_init");
add_action("save_post", "save_lang");

/**
 * Hook that runs when admin_init is run
 */
function admin_init() {
    add_meta_box("lang-meta", "Language", "meta_options", "post", "normal", "high");
}

/**
 * Renders the Custom Fields inputs
 * @param $post
 */
function meta_options($post) {
    $custom = get_post_custom($post->ID);
    $lang = !empty($custom["lang"][0]) ? $custom["lang"][0] : 'en';

    // We'll use this nonce field later on when saving.
    wp_nonce_field('meta_box_nonce', 'meta_box_nonce');

    $languages = getAvailableLanguages();

    echo "<select id='lang' name='lang'>";
    foreach ($languages as $key => $name) {
        echo "<option value='".$key."'".selected($lang, $key, 0).">".$name."</option>";
    }
    echo "</select>";
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
function save_lang($postID) {
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
    update_post_meta($postID, "lang", $lang);
}
