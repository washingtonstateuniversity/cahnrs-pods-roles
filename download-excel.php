<?php 
class DOWNLOAD_EXCEL {
  function __construct(){
    add_action('init', array($this, 'print_excel'));
  }


  function print_excel(){

    // Check the URL in order to perform the downloading
    if (!isset($_GET['excel']) || !isset($_GET['download_excel'])) {
      return false;
    }


    // Check the XLSXWriter class is already loaded or not.
    if (!class_exists('XLSXWriter')) {
      include_once('inc/xlsxwriter.class.php');
    }

    // Get today's date
    $today = date('Y');
   
    // Set the name file
    $fileLocation = "$today Revised Factsheets.csv";

    // Get parameters for the query
    $post_args = array( 
        'post_type' => 'fact_sheet',
        'posts_per_page' => -1,
        'orderby' => 'modified',
        'date_query'    => array(
          'column'  => 'post_modified',
          'after'   => '-365 days'
        )
    );

    // Run WordPress Query
    $fact_sheet_query = new WP_Query($post_args);
    
    // Create headers for Excel file
    $header = array(
      'Name'=>'string',
      'Modified Date'=>'date',
      'Link'=>'string',
    );
    

    // Run the WP_Query and retrieve the facts sheets
    if($fact_sheet_query->have_posts()){
      while($fact_sheet_query->have_posts()){
          $fact_sheet_query->the_post();
      
          $title = get_the_title();
          $modifiedDate = get_the_modified_date('Y-m-d');
          
          $postPermalink = get_permalink();

          $data[] = array($title, $modifiedDate, $postPermalink);
      }
  } 

    // Call the class and generate the excel file from the $data
    $writer = new XLSXWriter();
    $widths = array(40,20,50);
    $col_options = array('widths'=>$widths);

    $writer->writeSheetHeader('Sheet1', $header, $col_options );
    foreach($data as $row)
	    $writer->writeSheetRow('Sheet1', $row  );
    $writer->writeToFile($fileLocation);

    // Prompt download popup
    header('Content-Description: File Transfer');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header('Access-Control-Allow-Methods: GET'); 
    header('Access-Control-Allow-Origin: *');
    header("Content-Disposition: attachment; filename=" . basename($fileLocation));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: public");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Length: ' . filesize($fileLocation));

    ob_clean();
    flush();

    readfile($fileLocation);
    unlink($fileLocation);
    exit;
  }
}

// Initialize the class
new DOWNLOAD_EXCEL();