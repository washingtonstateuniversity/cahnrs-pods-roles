<?php
/**
 * Plugin Name: CAHNRS Pods Roles
 * Plugin URI: 
 * Description: This plugin changes some of the roles for the Pods plugin
 * Version: 1.0
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
