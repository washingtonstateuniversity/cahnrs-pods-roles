<?php
/**
 * Plugin Name: CAHNRS Pods Roles
 * Plugin URI: 
 * Description: This plugin changes some of the roles for the Pods plugin
 * Version: 1.3
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

        pods_cache_clear ( $key = true, $group = '' );
    }
}

// Hook the function to the 'save_post' action
add_action('save_post', 'update_fact_sheet_revision_date', 10, 1);

// Hook the function to the 'before_delete_post' action to handle Pesticide deletions
add_action('before_delete_post', 'update_fact_sheet_revision_date', 10, 1);

require_once 'download-excel.php';

add_action( 'admin_menu', 'create_custom_post_type_settings_page' );

function create_custom_post_type_settings_page() {
    add_submenu_page(
        'edit.php?post_type=fact_sheet',
        'Reports',
        'Reports',
        'manage_options',
        'custom_post_type_settings',
        'custom_post_type_settings_page'
      );
}

function custom_post_type_settings_page() {
    // Check if the user has permissions to access the settings page
    if ( ! current_user_can( 'manage_options' ) ) {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    // Output the settings page HTML
    echo '<div class="wrap">';
    ?>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
  <?php
  // Build the URL and query string
  $get_vars_excel = $_GET;
  $get_vars_excel['excel'] = true;
  $get_vars_excel['download_excel'] = true;
  $excel_url = site_url() . '?' . http_build_query($get_vars_excel);
  $today = strtotime(date('Y-m-d'));
  
  $args = array( 
    'post_type'      => 'fact_sheet',
    //'nopaging'      => true,
    'posts_per_page' => -1,
    'orderby'        => 'modified',
    'date_query'     => array(
            'column' => 'post_modified', 
            'after'  => date('Y-m-d H:i:s', strtotime('-1 year', $today)),
    ),
  );

    $fact_sheet_query = new WP_Query($args);
  ?> 
  <h1>Fact Sheet Report</h1>
  <p>Below are the most recent fact sheets that have been modified in the past 12 months. To download to an excel file, click on the "Download Report" button below.</p>
  <div class="row" style="margin-top: 30px;">
    <div class="col-sm-12">
        <div class="dropdown">
          <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: #A60F2D;">
            Download Report
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="<?php echo $excel_url ?>">Download as Excel</a>
          </div>
        </div>
    </div>
  </div>

  <table>
    <thead>
      <th>Post</th>
      <th>Revised Date</th>
    </thead>
  <?php 
      if($fact_sheet_query->have_posts()){
          while($fact_sheet_query->have_posts()){
              $fact_sheet_query->the_post();
              echo "<tr>";
              echo "<td style='padding: 0.3rem 1.5rem .3rem .3rem;border: 1px solid #b3b3b3;border-collapse: collapse;'><a href='" . get_the_permalink() . "'>". get_the_title(). "</a></td>";
              echo "<td style='padding: 0.3rem 1.5rem;border: 1px solid #b3b3b3;border-collapse: collapse;'>" . get_the_modified_date() . "</td>";
              echo "</tr>";
          }
      } ?>
  </table>

  <div class="row" style="margin-top: 30px;">
    <div class="col-sm-12">
        <div class="dropdown">
          <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: #A60F2D;">
            Download Report
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="<?php echo $excel_url ?>">Download as Excel</a>
          </div>
        </div>
    </div>
  </div> 
  <?php
    echo '</div>';
  }