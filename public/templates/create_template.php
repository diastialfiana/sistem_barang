<?php

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// DB Connection to fetch real branches
$branches = ['Gudang Pusat'];
try {
    $env = file_get_contents(__DIR__ . '/../../.env');
    preg_match('/DB_DATABASE=(.*)/', $env, $dbMatch);
    preg_match('/DB_USERNAME=(.*)/', $env, $userMatch);
    preg_match('/DB_PASSWORD=(.*)/', $env, $passMatch);
    preg_match('/DB_HOST=(.*)/', $env, $hostMatch);
    
    $database = trim($dbMatch[1] ?? 'sistem_barang');
    $username = trim($userMatch[1] ?? 'root');
    $password = trim($passMatch[1] ?? '');
    $host = trim($hostMatch[1] ?? '127.0.0.1');
    
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $stmt = $pdo->query("SELECT name FROM branches ORDER BY name ASC");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $branches[] = $row['name'];
    }
} catch (Exception $e) {
    // Fallback if DB connection fails
    $branches = ['Gudang Pusat', 'Cabang Jakarta', 'Cabang Surabaya', 'Cabang Bandung'];
}

// Set column widths
$sheet->getColumnDimension('A')->setWidth(30);
$sheet->getColumnDimension('B')->setWidth(20);
$sheet->getColumnDimension('C')->setWidth(15);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(25); // For Branch Reference Table

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
$sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
$sheet->getStyle('F1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(25);

// Set headers
$sheet->setCellValue('A1', 'Nama Barang');
$sheet->setCellValue('B1', 'Cabang');
$sheet->setCellValue('C1', 'Satuan');
$sheet->setCellValue('D1', 'Stok Awal');

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
$sheet->setCellValue('B2', 'Gudang Pusat');
$sheet->setCellValue('C2', 'Box');
$sheet->setCellValue('D2', '50');

$sheet->setCellValue('A3', 'Kertas HVS A4');
$sheet->setCellValue('B3', ($branches[1] ?? 'Cabang Jakarta'));
$sheet->setCellValue('C3', 'Rim');
$sheet->setCellValue('D3', '100');

$sheet->setCellValue('A4', 'Mouse Logitech');
$sheet->setCellValue('B4', ($branches[2] ?? 'Cabang Surabaya'));
$sheet->setCellValue('C4', 'Pcs');
$sheet->setCellValue('D4', '25');

// Apply example styling
$sheet->getStyle('A2:D4')->applyFromArray($exampleStyle);

// --- New Sheet for Branch Reference ---
$branchSheet = $spreadsheet->createSheet();
$branchSheet->setTitle('Data Cabang');
$branchSheet->getColumnDimension('A')->setWidth(30);

$branchSheet->setCellValue('A1', 'Daftar Cabang Terdaftar');
$branchSheet->getStyle('A1')->applyFromArray($headerStyle);

$row = 2;
foreach ($branches as $branchName) {
    $branchSheet->setCellValue('A' . $row, $branchName);
    $row++;
}
$branchSheet->getStyle('A2:A' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// --- Instruction sheet ---
$instructionSheet = $spreadsheet->createSheet();
$instructionSheet->setTitle('Instruksi');
$instructionSheet->setCellValue('A1', 'INSTRUKSI PENGGUNAAN TEMPLATE IMPORT BARANG');
$instructionSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$instructionSheet->getColumnDimension('A')->setWidth(80);

$instructionSheet->setCellValue('A3', '1. Gunakan sheet "Template Import" untuk mengisi data barang');
$instructionSheet->setCellValue('A4', '2. Kolom yang harus diisi:');
$instructionSheet->setCellValue('A5', '   - Nama Barang: Nama barang (wajib diisi)');
$instructionSheet->setCellValue('A6', '   - Cabang: Nama cabang (Lihat referensi di sheet "Data Cabang")');
$instructionSheet->setCellValue('A7', '   - Satuan: Pilih salah satu (Pcs, Box, Rim, Pack, Unit)');
$instructionSheet->setCellValue('A8', '   - Stok Awal: Jumlah stok awal (angka, minimal 0)');
$instructionSheet->setCellValue('A10', '3. Kategori akan otomatis terdeteksi berdasarkan nama barang');
$instructionSheet->setCellValue('A11', '4. Hapus baris contoh sebelum mengupload file');
$instructionSheet->setCellValue('A12', '5. Simpan file dan upload ke sistem');

// Set active sheet back to Template Import
$spreadsheet->setActiveSheetIndex(0);
$spreadsheet->getActiveSheet()->setTitle('Template Import');

// Save file
$writer = new Xlsx($spreadsheet);
$outputPath = __DIR__ . '/template_import_barang_baru.xlsx';
$writer->save($outputPath);

echo "Template Excel berhasil dibuat: {$outputPath}\n";
