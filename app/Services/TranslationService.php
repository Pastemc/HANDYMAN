<?php

namespace App\Services;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TranslationService
{
    protected $translator;
    protected $cacheTime = 86400; // 24 horas

    public function __construct()
    {
        $this->translator = new GoogleTranslate();
    }

    /**
     * Traducir texto de un idioma a otro
     */
    public function translate($text, $targetLang = 'en', $sourceLang = 'es')
    {
        if (empty($text)) {
            return $text;
        }

        // Si el idioma destino es el mismo que el origen, retornar el texto original
        if ($targetLang === $sourceLang) {
            return $text;
        }

        // Crear clave de caché única
        $cacheKey = "translation_{$sourceLang}_{$targetLang}_" . md5($text);

        // Intentar obtener de caché
        return Cache::remember($cacheKey, $this->cacheTime, function () use ($text, $targetLang, $sourceLang) {
            try {
                $this->translator->setSource($sourceLang);
                $this->translator->setTarget($targetLang);
                return $this->translator->translate($text);
            } catch (\Exception $e) {
                Log::error('Error en traducción: ' . $e->getMessage());
                return $text; // Retornar texto original si falla
            }
        });
    }

    /**
     * Traducir un array de datos
     */
    public function translateArray(array $data, $targetLang = 'en', $sourceLang = 'es', array $fieldsToTranslate = [])
    {
        $translated = $data;

        foreach ($fieldsToTranslate as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $translated[$field] = $this->translate($data[$field], $targetLang, $sourceLang);
            }
        }

        return $translated;
    }

    /**
     * Traducir una colección de modelos
     */
    public function translateCollection($collection, $targetLang = 'en', $sourceLang = 'es', array $fieldsToTranslate = [])
    {
        return $collection->map(function ($item) use ($targetLang, $sourceLang, $fieldsToTranslate) {
            $itemArray = $item->toArray();
            $translated = $this->translateArray($itemArray, $targetLang, $sourceLang, $fieldsToTranslate);
            
            // Mantener el objeto original pero con campos traducidos
            foreach ($fieldsToTranslate as $field) {
                if (isset($translated[$field])) {
                    $item->$field = $translated[$field];
                }
            }
            
            return $item;
        });
    }

    /**
     * Limpiar caché de traducciones
     */
    public function clearCache()
    {
        Cache::flush();
    }
}