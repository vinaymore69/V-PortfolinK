<?php
// readCSV.php

// Load Composer's autoloader to include PhpSpreadsheet and other libraries.
require 'vendor/autoload.php';

// Import the necessary classes from PhpSpreadsheet.
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\IOFactory;

// Define the relative path to your CSV file.
$inputFileName = 'excel_data/data.csv';

try {
    // Create a CSV reader instance.
    $reader = new Csv();

    // Optional: Set CSV reader options (adjust if needed)
    $reader->setDelimiter(',');      // Set delimiter, comma is default.
    $reader->setEnclosure('"');      // Set enclosure, double quotes is default.
    $reader->setSheetIndex(0);       // CSV files have only one "sheet".

    // Load the CSV file into a Spreadsheet object.
    $spreadsheet = $reader->load($inputFileName);

    // Get the active sheet (the only sheet in a CSV file).
    $sheet = $spreadsheet->getActiveSheet();

    // Prepare an array to hold all the data.
    $data = [];

    // Loop through each row in the sheet.
    foreach ($sheet->getRowIterator() as $row) {
        $rowData = [];
        // Iterate over cells in the row.
        $cellIterator = $row->getCellIterator();
        // This makes sure even empty cells are included.
        $cellIterator->setIterateOnlyExistingCells(false);
        foreach ($cellIterator as $cell) {
            $rowData[] = $cell->getValue();
        }
        $data[] = $rowData;
    }

    // Display the data (for example, using print_r inside <pre> tags).
    echo "<pre>";
    print_r($data);
    echo "</pre>";
} catch (Exception $e) {
    // Output error message if something goes wrong.
    echo 'Error loading file: ' . $e->getMessage();
}
?>
