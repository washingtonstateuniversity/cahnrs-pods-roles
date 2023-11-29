<?php class DOWNLOAD_EXCEL {
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
    $filename = "$today Revised Factsheets.xlsx";
    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

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

        $rows[] = array($title, $modifiedDate, $postPermalink);
    }
} 

    $writer = new XLSXWriter();
    
    $widths = array(40,20,50);
    $styles1 = array( 'font'=>'Arial','font-size'=>12, 'color'=>'#ffffff', 'font-style'=>'bold', 'fill'=>'#A60F2D', 'widths'=>$widths);
    $col_options = array('widths'=>$widths);
    $writer->writeSheetHeader('Sheet1', $header, $styles1  );
    foreach($rows as $row)
      $writer->writeSheetRow('Sheet1', $row, $row_options = array('wrap_text'=>true));
    $writer->writeToStdOut();
    //$writer->writeToFile($filename);
    //echo $writer->writeToString();
    exit(0);

  }
}

// Initialize the class
new DOWNLOAD_EXCEL();