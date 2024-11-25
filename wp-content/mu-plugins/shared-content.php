<?php
/**
 * Plugin Name: Shared Content Manager
 * Description: Allows content to be shared across multiple sites in a multisite network.
 * Version: 1.0
 * Author: Your Name
 */

// Function to share a post across sites
function share_post_across_sites($post_id) {
    global $wpdb;
    $post = get_post($post_id);
    if (!$post) {
        return false;
    }

    // Get all site IDs in the network
    $site_ids = get_sites(['fields' => 'ids']);

    foreach ($site_ids as $site_id) {
        switch_to_blog($site_id);

        // Check if post already exists
        $existing_post = get_page_by_title($post->post_title, OBJECT, $post->post_type);
        if (!$existing_post) {
            // Insert post into the site
            $new_post_id = wp_insert_post([
                'post_title'   => $post->post_title,
                'post_content' => $post->post_content,
                'post_status'  => $post->post_status,
                'post_type'    => $post->post_type,
            ]);

            // Copy post metadata
            $meta_data = get_post_meta($post_id);
            foreach ($meta_data as $meta_key => $meta_value) {
                update_post_meta($new_post_id, $meta_key, $meta_value[0]);
            }
        }

        restore_current_blog();
    }

    return true;
}

// Hook to share post when it's published
add_action('publish_post', 'share_post_across_sites');
