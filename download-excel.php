<?php

/**
 * Plugin Name: Download data as excel
 * Description: Download data as excel
 * Version: 1.0
 * Author: Apple Rinquest
 * Author URI: https://applerinquest.com
 */

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

    // # set the destination file
    $fileLocation = 'output.xlsx';

    // # prepare the data set
    $data = array(
      array('year', 'month', 'amount'),
      array('2003', '1', '220'),
      array('2003', '2', '153.5'),
    );

    // # call the class and generate the excel file from the $data
    $writer = new XLSXWriter();
    $writer->writeSheet($data);
    $writer->writeToFile($fileLocation);


    // # prompt download popup
    header('Content-Description: File Transfer');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
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

// # initialize the class
new DOWNLOAD_EXCEL();