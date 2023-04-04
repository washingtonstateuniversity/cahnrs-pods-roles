<?php
/**
 * Plugin Name: CAHNRS Pods Roles
 * Plugin URI: 
 * Description: This plugin changes some of the roles for the Pods plugin
 * Version: 1.1
 * Author: Washington State University | CAHNRS Communications
 * Author URI: http://cahnrs.wsu.edu/communications
 */

 /**
* Filter the capabilities that are required to access the Pods Admin area to include whatever caps we want.
* 
 * @param array $pods_admin_capabilities The capabilities that are required to access the Pods Admin area.
*
* @return array The capabilities that are required to access the Pods Admin area.
*/
function cahnrswsu_pods_admin_capabilities( $pods_admin_capabilities ) {
    $pods_admin_capabilities[] = 'edit_users';
    $pods_admin_capabilities[] = 'manage_options';
    
    return $pods_admin_capabilities;
}
add_filter( 'pods_admin_capabilities', 'cahnrswsu_pods_admin_capabilities' );

//Updates the revision date if a related pesticide has been updated 

function update_fact_sheet_revision_date($post_id) {
    // Check if the post being updated is a Pesticide
    if (get_post_type($post_id) == 'pesticide') {
        // Get related Fact Sheets
        $related_fact_sheets = get_posts(array(
            'post_type' => 'fact_sheet',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => 'related_pesticides', 
                    'value' => $post_id,
                    'compare' => 'IN'
                )
            )
        ));

        // Update the revision date of each related Fact Sheet
        foreach ($related_fact_sheets as $fact_sheet) {
            // Set the new revision date to the current date and time
            $new_revision_date = current_time('mysql');

            global $wpdb;
            $wpdb->query("UPDATE $wpdb->posts SET post_modified = '{$new_revision_date}', post_modified_gmt = '{$new_revision_date}'  WHERE ID = {$fact_sheet->ID}" );

        }
    }
}

// Hook the function to the 'save_post' action
add_action('save_post', 'update_fact_sheet_revision_date', 10, 1);

// Hook the function to the 'before_delete_post' action to handle Pesticide deletions
add_action('before_delete_post', 'update_fact_sheet_revision_date', 10, 1);

