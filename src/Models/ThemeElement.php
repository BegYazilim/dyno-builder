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
     * Retrieves an element for a specific element_key and page_id
     *
     * @param  string  $element_key  Element key
     * @param  string|null  $page_id  Page ID (optional)
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
     * Retrieves element data for a specific element_key and page_id
     * First tries to read from the file system, if the file doesn't exist, reads from the database
     *
     * @param  string  $element_key  Element key
     * @param  string|null  $page_id  Page ID (optional)
     */
    public static function getElementData(string $element_key, ?string $page_id = null): ?array
    {
        $element = self::getElement($element_key, $page_id);

        if (! $element) {
            return null;
        }

        // If the file path exists and the file is available, read from the file first
        // @phpstan-ignore-next-line
        if ($element->file_path && Storage::exists($element->file_path)) {
            try {
                $content = Storage::get($element->file_path);
                $fileData = json_decode($content, true);

                if ($fileData) {
                    return $fileData;
                }
            } catch (\Exception $e) {
                // If the file cannot be read or is invalid, log the error (optional)
                // Log::error('Theme element file could not be read: ' . $e->getMessage());
            }
        }

        // If the file doesn't exist or cannot be read, use the data from the database
        // @phpstan-ignore-next-line
        return $element->data;
    }

    /**
     * Adds or updates a new element
     * Writes the data first to the database, then to the file system
     *
     * @param  string  $element_key  Element key
     * @param  string  $element_type  Element type (header, footer, sidebar etc.)
     * @param  array|string  $data  Element data - can be an array or JSON string
     * @param  string|null  $page_id  Page ID (optional)
     * @param  bool  $store_in_file  Store data in file (for large data)
     */
    public static function saveElement(
        string $element_key,
        string $element_type,
        $data,
        ?string $page_id = null,
        bool $store_in_file = false
    ): ThemeElement {
        // Check the data type
        if (is_string($data)) {
            // If it's a JSON string, decode it
            try {
                $decoded = json_decode($data, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $arrayData = $decoded;
                } else {
                    // If it's not JSON, use it directly
                    $arrayData = $data;
                }
                // @phpstan-ignore-next-line
            } catch (\Exception $e) {
                // In case of error, use it directly
                $arrayData = $data;
            }
        } else {
            $arrayData = $data;
        }

        // Create conditions
        $conditions = ['element_key' => $element_key];

        if ($page_id !== null) {
            $conditions['page_id'] = $page_id;
        }

        // Check for existing element
        $existingElement = self::where($conditions)->first();

        // Delete old file (if it exists)
        // @phpstan-ignore-next-line
        if ($existingElement && $existingElement->file_path && Storage::exists($existingElement->file_path)) {
            Storage::delete($existingElement->file_path);
        }

        $filePath = null;
        $dbData = $arrayData; // Varsayılan olarak tüm veriyi veritabanında sakla

        // Step 1: First save to database
        $element = self::updateOrCreate(
            $conditions,
            [
                'element_type' => $element_type,
                'data' => $dbData,
                'file_path' => null, // Set file path to null initially
                'page_id' => $page_id,
            ]
        );

        // Step 2: If it should be stored in a file, write to file
        if ($store_in_file) {
            // Create directory
            $directory = 'theme_elements/'.$element_type;
            if (! Storage::exists($directory)) {
                Storage::makeDirectory($directory);
            }

            // Create unique filename
            $fileNameBase = $page_id ?
                md5($element_key.'_'.$page_id) :
                md5($element_key);

            $filePath = $directory.'/'.$fileNameBase.'.json';

            // Write data to file
            Storage::put($filePath, json_encode($arrayData));

            // Update file reference in database
            // @phpstan-ignore-next-line
            $element->file_path = $filePath;
            // @phpstan-ignore-next-line
            $element->data = ['_stored_in_file' => true]; // Only store reference in database
            $element->save();
        }

        return $element;
    }

    /**
     * Deletes a specific element
     *
     * @param  string  $element_key  Element key
     * @param  string|null  $page_id  Page ID (optional)
     */
    public static function deleteElement(string $element_key, ?string $page_id = null): bool
    {
        // Create query
        $query = self::where('element_key', $element_key);

        if ($page_id !== null) {
            $query->where('page_id', $page_id);
        }

        $elements = $query->get();

        if ($elements->isEmpty()) {
            return false;
        }

        foreach ($elements as $element) {
            // Delete file (if it exists)
            // @phpstan-ignore-next-line
            if ($element->file_path && Storage::exists($element->file_path)) {
                Storage::delete($element->file_path);
            }

            // Delete database record
            $element->delete();
        }

        return true;
    }

    /**
     * Retrieves all elements with a specific element type
     *
     * @param  string  $element_type  Element type
     * @param  string|null  $page_id  Page ID (optional)
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
     * Retrieves all elements with a specific page ID
     *
     * @param  string  $page_id  Page ID
     */
    public static function getElementsByPageId(string $page_id): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('page_id', $page_id)->get();
    }
}
