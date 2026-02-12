<?php

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set column widths
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(15);

// Header row styling
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 12,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1E40AF'],
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => '000000'],
        ],
    ],
];

// Apply header style
$sheet->getStyle('A1:C1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(25);

// Set headers
$sheet->setCellValue('A1', 'Nama Barang');
$sheet->setCellValue('B1', 'Satuan');
$sheet->setCellValue('C1', 'Stok Awal');

// Example data styling
$exampleStyle = [
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'F3F4F6'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D1D5DB'],
        ],
    ],
];

// Add example rows
$sheet->setCellValue('A2', 'Pulpen Snowman');
$sheet->setCellValue('B2', 'Box');
$sheet->setCellValue('C2', '50');

$sheet->setCellValue('A3', 'Kertas HVS A4');
$sheet->setCellValue('B3', 'Rim');
$sheet->setCellValue('C3', '100');

$sheet->setCellValue('A4', 'Mouse Logitech');
$sheet->setCellValue('B4', 'Pcs');
$sheet->setCellValue('C4', '25');

// Apply example styling
$sheet->getStyle('A2:C4')->applyFromArray($exampleStyle);

// Add instruction sheet
$instructionSheet = $spreadsheet->createSheet();
$instructionSheet->setTitle('Instruksi');
$instructionSheet->setCellValue('A1', 'INSTRUKSI PENGGUNAAN TEMPLATE IMPORT BARANG');
$instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$instructionSheet->getColumnDimension('A')->setWidth(80);

$instructionSheet->setCellValue('A3', '1. Gunakan sheet "Template Import" untuk mengisi data barang');
$instructionSheet->setCellValue('A4', '2. Kolom yang harus diisi:');
$instructionSheet->setCellValue('A5', '   - Nama Barang: Nama barang (wajib diisi)');
$instructionSheet->setCellValue('A6', '   - Satuan: Pilih salah satu (Pcs, Box, Rim, Pack, Unit)');
$instructionSheet->setCellValue('A7', '   - Stok Awal: Jumlah stok awal (angka, minimal 0)');
$instructionSheet->setCellValue('A9', '3. Kategori akan otomatis terdeteksi berdasarkan nama barang');
$instructionSheet->setCellValue('A10', '4. Hapus baris contoh sebelum mengupload file');
$instructionSheet->setCellValue('A11', '5. Simpan file dan upload ke sistem');

// Set active sheet back to Template Import
$spreadsheet->setActiveSheetIndex(0);
$spreadsheet->getActiveSheet()->setTitle('Template Import');

// Save file
$writer = new Xlsx($spreadsheet);
$outputPath = __DIR__ . '/template_import_barang.xlsx';
$writer->save($outputPath);

echo "Template Excel berhasil dibuat: {$outputPath}\n";
