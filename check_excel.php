<?php
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'public/templates/template_import_barang_baru.xlsx';
if (!file_exists($file)) {
    echo "File not found\n";
    exit;
}

$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
echo "Title: " . $sheet->getTitle() . "\n";
echo "A1: " . $sheet->getCell('A1')->getValue() . "\n";
echo "B1: " . $sheet->getCell('B1')->getValue() . "\n";
echo "C1: " . $sheet->getCell('C1')->getValue() . "\n";
echo "D1: " . $sheet->getCell('D1')->getValue() . "\n";
echo "F1: " . $sheet->getCell('F1')->getValue() . "\n";
