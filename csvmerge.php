<?php

/**
 * @file
 *  CSV Merge is a tool to merge multiple csv's based on a specified field
 * serving as the main key.
 */

define('KEY_ID', 'id');
define('FOLDER', '');

// CSV Files. First will be used as base.
$sources = [
  FOLDER . 'main.csv',
  FOLDER . 'obit.csv',
];
$output_file_name = FOLDER . 'output.csv';
$header = [];
$data = [];

// Get the base file.
$csvsource = getCSV(array_shift($sources));
$data = $csvsource['data'];
$header = $csvsource['header'];

// Merge other files.
foreach ($sources as $source) {
  $csvsource = getCSV($source);
  foreach ($data as $key => &$value) {
    if (isset($csvsource['data'][$key])) {
      unset($csvsource['data'][$key][KEY_ID]);
      $value = array_merge($value, $csvsource['data'][$key]);
    }
  }

  if (($header_dup_id_pos = array_search(KEY_ID, $csvsource['header'])) !== false) {
    unset($csvsource['header'][$header_dup_id_pos]);
  }
  $header = array_merge($header, $csvsource['header']);
}

array_unshift($data, $header);
putCSV($output_file_name, $data);

print 'Merge complete.' . "\n";
exit;

/**
 * Get content of csv file.
 *
 * @param $file_name
 * @return array
 */
function getCSV($file_name) {
  $data = [];
  $header = [];
  if ($handle = fopen($file_name, "r")) {
    $header = fgetcsv($handle);
    while (($fragment = fgetcsv($handle)) !== FALSE) {
      $joined = array_combine($header, $fragment);
      $data[$joined[KEY_ID]] = $joined;
    }
    fclose($handle);
  }

  return ['header' => $header, 'data' => $data];
}

/**
 * Write the csv file.
 *
 * @param $output_file_name
 * @param $data
 */
function putCSV($output_file_name, $data) {
  if ($handle = fopen($output_file_name, "w")) {
    foreach ($data as $line) {
      fputcsv($handle, $line);
    }
    fclose($handle);
  }
}
