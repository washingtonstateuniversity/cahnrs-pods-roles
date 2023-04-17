<?php 
class DOWNLOAD_EXCEL
{
  function __construct()
  {
    // I use the download feature on the frontend so I use the init action hook.
    // If you use the download feature on the backend, you will use the admin_init action hook.
    add_action('init', array($this, 'print_excel'));
  }


  function print_excel()
  {

    // # check the URL in order to perform the downloading
    if (!isset($_GET['excel']) || !isset($_GET['download_excel'])) {
      return false;
    }


    // # check the XLSXWriter class is already loaded or not. If it is not loaded yet, we will load it.
    if (!class_exists('XLSXWriter')) {
      include_once('inc/xlsxwriter.class.php');
    }

    $today = date('Y');
   
    // # set the destination file
    $fileLocation = "$today Revised Factsheets.xlsx";

    
    $post_args = array( 
        'post_type' => 'fact_sheet',
        'posts_per_page' => -1,
        'orderby' => 'modified',
        'date_query'    => array(
          'column'  => 'post_modified',
          'after'   => '-365 days'
        )
    );

    $fact_sheet_query = new WP_Query($post_args);
    
    $header = array(
      'Name'=>'string',
      'Modified Date'=>'date',
      'Link'=>'string',
    );
    
    
    
    if($fact_sheet_query->have_posts()){
      while($fact_sheet_query->have_posts()){
          $fact_sheet_query->the_post();

          
          $title = get_the_title();
          $modifiedDate = get_the_modified_date('Y-m-d');
          
          $postPermalink = get_permalink();

          $data[] = array("$title", $modifiedDate, "$postPermalink");
      }
  } 

    // # call the class and generate the excel file from the $data
    $writer = new XLSXWriter();
    $widths = array(40,20,50);
    $col_options = array('widths'=>$widths);

    $writer->writeSheetHeader('Sheet1', $header, $col_options );
    foreach($data as $row)
	    $writer->writeSheetRow('Sheet1', $row  );
    $writer->writeToFile($fileLocation);

    // Clear any previous output (otherwise the generated file will be corrupted)
    ob_end_clean();

    // # prompt download popup
    header('Content-Description: File Transfer');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=" . basename($fileLocation));
    header("Content-Transfer-Encoding: binary");
    header("Expires: 0");
    header("Pragma: public");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header('Content-Length: ' . filesize($fileLocation));

    // ob_clean();
    // flush();

    readfile($fileLocation);
    unlink($fileLocation);
    exit;
  }
}

// # initialize the class
new DOWNLOAD_EXCEL();
?>