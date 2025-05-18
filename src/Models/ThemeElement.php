<?php

namespace BegYazilim\DynoBuilder\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ThemeElement extends Model
{
    protected $fillable = [
        'element_key',
        'element_type',
        'page_id',
        'data',
        'file_path',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Belirli bir element_key ve page_id için eleman getirir
     *
     * @param  string  $element_key  Element anahtarı
     * @param  string|null  $page_id  Sayfa ID (opsiyonel)
     */
    public static function getElement(string $element_key, ?string $page_id = null): ?ThemeElement
    {
        $query = self::where('element_key', $element_key);

        if ($page_id !== null) {
            $query->where('page_id', $page_id);
        }

        return $query->first();
    }

    /**
     * Belirli bir element_key ve page_id için eleman verisini getirir
     * Öncelikle dosya sisteminden okumaya çalışır, dosya yoksa veritabanından okur
     *
     * @param  string  $element_key  Element anahtarı
     * @param  string|null  $page_id  Sayfa ID (opsiyonel)
     */
    public static function getElementData(string $element_key, ?string $page_id = null): ?array
    {
        $element = self::getElement($element_key, $page_id);

        if (! $element) {
            return null;
        }

        // Eğer dosya yolu varsa ve dosya mevcutsa, öncelikle dosyadan oku
        // @phpstan-ignore-next-line
        if ($element->file_path && Storage::exists($element->file_path)) {
            try {
                $content = Storage::get($element->file_path);
                $fileData = json_decode($content, true);

                if ($fileData) {
                    return $fileData;
                }
            } catch (\Exception $e) {
                // Dosya okunamazsa veya geçersizse, hata kaydet (opsiyonel)
                // Log::error('Theme element file could not be read: ' . $e->getMessage());
            }
        }

        // Dosya yoksa veya okunamazsa veritabanındaki veriyi kullan
        // @phpstan-ignore-next-line
        return $element->data;
    }

    /**
     * Yeni bir element ekler veya günceller
     * Veriyi önce veritabanına, sonra dosya sistemine yazar
     *
     * @param  string  $element_key  Element anahtarı
     * @param  string  $element_type  Element tipi (header, footer, sidebar vb.)
     * @param  array|string  $data  Element verisi - array veya JSON string olabilir
     * @param  string|null  $page_id  Sayfa ID (opsiyonel)
     * @param  bool  $store_in_file  Veriyi dosyada sakla (büyük veriler için)
     */
    public static function saveElement(
        string $element_key,
        string $element_type,
        $data,
        ?string $page_id = null,
        bool $store_in_file = false
    ): ThemeElement {
        // Veri tipini kontrol et
        if (is_string($data)) {
            // Eğer JSON string ise, decode et
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $arrayData = $decoded;
                } else {
                    // JSON değilse, direkt olarak kullan
                    $arrayData = $data;
                }
                // @phpstan-ignore-next-line
            } catch (\Exception $e) {
                // Hata durumunda direkt olarak kullan
                $arrayData = $data;
            }
        } else {
            $arrayData = $data;
        }

        // Koşulları oluştur
        $conditions = ['element_key' => $element_key];

        if ($page_id !== null) {
            $conditions['page_id'] = $page_id;
        }

        // Mevcut elementi kontrol et
        $existingElement = self::where($conditions)->first();

        // Eski dosyayı sil (eğer varsa)
        // @phpstan-ignore-next-line
        if ($existingElement && $existingElement->file_path && Storage::exists($existingElement->file_path)) {
            Storage::delete($existingElement->file_path);
        }

        $filePath = null;
        $dbData = $arrayData; // Varsayılan olarak tüm veriyi veritabanında sakla

        // 1. Adım: Önce veritabanına kaydet
        $element = self::updateOrCreate(
            $conditions,
            [
                'element_type' => $element_type,
                'data' => $dbData,
                'file_path' => null, // Dosya yolunu başlangıçta null olarak ayarla
                'page_id' => $page_id,
            ]
        );

        // 2. Adım: Eğer dosyada saklanacaksa, dosyaya yaz
        if ($store_in_file) {
            // Dizini oluştur
            $directory = 'theme_elements/'.$element_type;
            if (! Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Benzersiz dosya adı oluştur
            $fileNameBase = $page_id ?
                md5($element_key.'_'.$page_id) :
                md5($element_key);

            $filePath = $directory.'/'.$fileNameBase.'.json';

            // Veriyi dosyaya yaz
            Storage::put($filePath, json_encode($arrayData));

            // Veritabanında dosya referansını güncelle
            // @phpstan-ignore-next-line
            $element->file_path = $filePath;
            // @phpstan-ignore-next-line
            $element->data = ['_stored_in_file' => true]; // Veritabanında sadece referans sakla
            $element->save();
        }

        return $element;
    }

    /**
     * Belirli bir elementi siler
     *
     * @param  string  $element_key  Element anahtarı
     * @param  string|null  $page_id  Sayfa ID (opsiyonel)
     */
    public static function deleteElement(string $element_key, ?string $page_id = null): bool
    {
        // Sorguyu oluştur
        $query = self::where('element_key', $element_key);

        if ($page_id !== null) {
            $query->where('page_id', $page_id);
        }

        $elements = $query->get();

        if ($elements->isEmpty()) {
            return false;
        }

        foreach ($elements as $element) {
            // Dosyayı sil (eğer varsa)
            // @phpstan-ignore-next-line
            if ($element->file_path && Storage::exists($element->file_path)) {
                Storage::delete($element->file_path);
            }

            // Veritabanı kaydını sil
            $element->delete();
        }

        return true;
    }

    /**
     * Belirli bir element tipine sahip tüm elementleri getirir
     *
     * @param  string  $element_type  Element tipi
     * @param  string|null  $page_id  Sayfa ID (opsiyonel)
     */
    public static function getElementsByType(string $element_type, ?string $page_id = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('element_type', $element_type);

        if ($page_id !== null) {
            $query->where('page_id', $page_id);
        }

        return $query->get();
    }

    /**
     * Belirli bir sayfa ID'sine sahip tüm elementleri getirir
     *
     * @param  string  $page_id  Sayfa ID
     */
    public static function getElementsByPageId(string $page_id): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('page_id', $page_id)->get();
    }
}
