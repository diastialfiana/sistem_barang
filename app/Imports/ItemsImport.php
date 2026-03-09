<?php

namespace App\Imports;

use App\Models\Item;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class ItemsImport implements ToCollection, WithHeadingRow
{
    protected $importData = [];
    protected $validUnits = ['Pcs', 'Box', 'Rim', 'Pack', 'Unit'];
    protected $errors = [];

    public function collection(Collection $rows)
    {
        $rowNumber = 1; // Start from 1 (after header)
        
        foreach ($rows as $row) {
            $rowNumber++;
            
            // Flexible mapping - try different possible column names
            $name = $this->findValue($row, ['nama_barang', 'nama barang', 'nama', 'barang', 'name', 'item']);
            $unit = $this->findValue($row, ['satuan', 'unit', 'uom']);
            $price = $this->findValue($row, ['harga_barang', 'harga barang', 'harga', 'price', 'rate']);
            $stock = $this->findValue($row, ['stok_awal', 'stok awal', 'stok', 'stock', 'qty', 'quantity', 'stock barang', 'stock_barang']);
            $yearlyStock = $this->findValue($row, ['stok_tahun', 'stok tahun', 'stock_per_tahun', 'stock per tahun', 'yearly_stock', 'yearly stock']);
            $branchName = $this->findValue($row, ['cabang', 'branch', 'lokasi', 'location', 'area']);

            // Skip completely empty rows
            if (empty($name) && empty($unit) && ($stock === null || $stock === '')) {
                continue;
            }

            // Validate row
            $rowErrors = [];
            if (empty($name)) {
                $rowErrors[] = "Nama barang kosong";
            }
            if (empty($unit)) {
                $rowErrors[] = "Satuan kosong";
            } elseif (!in_array($unit, $this->validUnits)) {
                $rowErrors[] = "Satuan tidak valid (gunakan: " . implode(', ', $this->validUnits) . ")";
            }
            
            // Clean and validate stock
            $cleanStock = $this->cleanNumericValue($stock);
            if ($cleanStock === null || $cleanStock < 0) {
                $rowErrors[] = "Stok harus berupa angka minimal 0 (saat ini: '" . $stock . "')";
            }

            // Clean and validate price & yearly stock
            $cleanPrice = $this->cleanNumericValue($price) ?? 0;
            $cleanYearlyStock = $this->cleanNumericValue($yearlyStock) ?? 0;

            // If there are errors, record them and skip
            if (!empty($rowErrors)) {
                $this->errors[] = "Baris {$rowNumber}: " . implode(', ', $rowErrors);
                continue;
            }

            // Auto-detect category using existing model method
            $category = Item::detectCategory($name);

            // Resolve Branch
            $branchId = null;
            if ($branchName && strtolower(trim($branchName)) !== 'gudang pusat') {
                $branch = \App\Models\Branch::whereRaw('LOWER(name) = ?', [strtolower(trim($branchName))])->first();
                if ($branch) {
                    $branchId = $branch->id;
                } else {
                    $rowErrors[] = "Cabang '" . $branchName . "' tidak ditemukan";
                }
            }

            // Check if item already exists (case-insensitive) - update unique check to include branch_id
            $existingItem = Item::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])
                ->where('branch_id', $branchId)
                ->first();

            $this->importData[] = [
                'name' => trim($name),
                'unit' => $unit,
                'price' => (float) $cleanPrice,
                'stock' => (int) $cleanStock,
                'yearly_stock' => (int) $cleanYearlyStock,
                'category' => $category,
                'branch_id' => $branchId,
                'branch_name' => $branchName ?: 'Gudang Pusat',
                'is_duplicate' => !is_null($existingItem),
                'existing_item' => $existingItem ? [
                    'id' => $existingItem->id,
                    'stock' => $existingItem->stock,
                    'unit' => $existingItem->unit,
                ] : null,
            ];
        }
    }

    /**
     * Clean numeric value from Excel (remove formatting, convert to number)
     */
    private function cleanNumericValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        $value = (string) $value;
        $value = trim($value);
        
        // Handle common ID format: 10.000,00 or 10.000
        if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
            // Both dot and comma: dot is thousand, comma is decimal
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            // Only comma: check if it's thousand (e.g. 10,000) or decimal (e.g. 10,5)
            if (preg_match('/,\d{3}($|[^0-9])/', $value)) {
                $value = str_replace(',', '', $value); 
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif (strpos($value, '.') !== false) {
            // Only dot: check if it's thousand (e.g. 10.000) or decimal (e.g. 10.5)
            if (preg_match('/\.\d{3}($|[^0-9])/', $value)) {
                $value = str_replace('.', '', $value); 
            }
        }
        
        $value = str_replace([' ', 'Rp', '.', 'rp'], '', $value); // Final clean of currency prefix and leftover dots if any
        
        // Wait, I should not remove ALL dots if one is actually a decimal.
        // Let's refine the final clean.
        
        // Redoing the logic more simply:
        $value = (string) $value;
        $value = preg_replace('/[^\d,.]/', '', $value); // Keep only digits, comma, and dot
        
        // If there are both, assume dot is thousand, comma is decimal (ID standard)
        if (strpos($value, '.') !== false && strpos($value, ',') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } elseif (strpos($value, ',') !== false) {
            // If only comma, check if it's decimal or thousand
            if (preg_match('/,\d{3}$/', $value)) {
                $value = str_replace(',', '', $value);
            } else {
                $value = str_replace(',', '.', $value);
            }
        } elseif (strpos($value, '.') !== false) {
            // If only dot, check if it's thousand
            if (preg_match('/\.\d{3}$/', $value)) {
                $value = str_replace('.', '', $value);
            }
        }

        if (is_numeric($value)) {
            return (float) $value;
        }
        
        return null;
    }

    /**
     * Find value from row using multiple possible column names
     */
    private function findValue($row, $possibleKeys)
    {
        foreach ($possibleKeys as $key) {
            // Try exact match
            if (isset($row[$key]) && !empty($row[$key])) {
                return $row[$key];
            }
            
            // Try with spaces replaced by underscores
            $keyWithUnderscore = str_replace(' ', '_', $key);
            if (isset($row[$keyWithUnderscore]) && !empty($row[$keyWithUnderscore])) {
                return $row[$keyWithUnderscore];
            }
        }
        
        return null;
    }

    public function getImportData()
    {
        return $this->importData;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !empty($this->errors);
    }
}
