<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    protected $fillable = ['name', 'category', 'unit', 'stock'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($item) {
            if (empty($item->category)) {
                $item->category = self::detectCategory($item->name);
            }
        });
        
        static::updating(function ($item) {
            if (empty($item->category)) {
                $item->category = self::detectCategory($item->name);
            }
        });
    }

    public static function detectCategory($name)
    {
        $name = strtolower($name);
        
        // Alat Tulis Kantor
        $alatTulis = ['kertas', 'pulpen', 'pensil', 'spidol', 'penghapus', 'penggaris', 
                      'stapler', 'lem', 'gunting', 'cutter', 'amplop', 'map', 'folder',
                      'binder', 'clip', 'tinta', 'tipe-x', 'correction', 'highlighter',
                      'marker', 'ballpoint', 'notes', 'sticky', 'post-it'];
        
        foreach ($alatTulis as $keyword) {
            if (stripos($name, $keyword) !== false) {
                return 'Alat Tulis Kantor';
            }
        }
        
        // Elektronik
        $elektronik = ['laptop', 'komputer', 'monitor', 'keyboard', 'mouse', 'printer',
                       'scanner', 'proyektor', 'kabel', 'charger', 'adaptor', 'speaker',
                       'headset', 'webcam', 'harddisk', 'flashdisk', 'usb', 'hdmi'];
        
        foreach ($elektronik as $keyword) {
            if (stripos($name, $keyword) !== false) {
                return 'Elektronik';
            }
        }
        
        // Peralatan Kantor
        $peralatanKantor = ['meja', 'kursi', 'lemari', 'rak', 'papan', 'whiteboard',
                            'ac', 'kipas', 'lampu', 'kunci', 'gembok'];
        
        foreach ($peralatanKantor as $keyword) {
            if (stripos($name, $keyword) !== false) {
                return 'Peralatan Kantor';
            }
        }
        
        // Perlengkapan Kebersihan
        $kebersihan = ['sapu', 'pel', 'kain', 'lap', 'detergen', 'sabun', 'pembersih',
                       'tissue', 'tisu', 'sampah', 'kantong', 'plastik'];
        
        foreach ($kebersihan as $keyword) {
            if (stripos($name, $keyword) !== false) {
                return 'Perlengkapan Kebersihan';
            }
        }
        
        return 'UNCATEGORIZED';
    }

    public function requestItems()
    {
        return $this->hasMany(RequestItem::class);
    }
}
