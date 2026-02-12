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
            $stock = $this->findValue($row, ['stok_awal', 'stok awal', 'stok', 'stock', 'qty', 'quantity', 'stock barang', 'stock_barang']);

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

            // If there are errors, record them and skip
            if (!empty($rowErrors)) {
                $this->errors[] = "Baris {$rowNumber}: " . implode(', ', $rowErrors);
                continue;
            }

            // Auto-detect category using existing model method
            $category = Item::detectCategory($name);

            // Check if item already exists (case-insensitive)
            $existingItem = Item::whereRaw('LOWER(name) = ?', [strtolower(trim($name))])->first();

            $this->importData[] = [
                'name' => trim($name),
                'unit' => $unit,
                'stock' => (int) $cleanStock,
                'category' => $category,
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
        
        // Convert to string first
        $value = (string) $value;
        
        // Remove spaces, commas, dots used as thousand separators
        $value = str_replace([' ', ','], '', trim($value));
        
        // Check if it's numeric after cleaning
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
