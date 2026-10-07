<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\TranslationService;
use Symfony\Component\HttpFoundation\Response;

class TranslateResponse
{
    protected $translationService;

    // Campos que deben ser traducidos por modelo
    protected $translationFields = [
        'service_categories' => ['name', 'description'],
        'service_requests' => ['description', 'notes'],
        'notifications' => ['message'],
        'reviews' => ['comment'],
        'messages' => ['content'],
        'client_registrations' => ['message'],
        'roles' => ['display_name', 'description'],
        'permissions' => ['display_name', 'description'],
    ];

    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Obtener idioma de la petición (header o parámetro)
        $targetLang = $request->header('Accept-Language', $request->get('lang', 'es'));
        
        // Normalizar idioma (solo 'es' o 'en')
        $targetLang = $this->normalizeLanguage($targetLang);

        // Si es español, no traducir
        if ($targetLang === 'es') {
            return $response;
        }

        // Solo procesar respuestas JSON
        if ($response->headers->get('Content-Type') === 'application/json') {
            $content = json_decode($response->getContent(), true);

            if ($content) {
                $translatedContent = $this->translateContent($content, $targetLang);
                $response->setContent(json_encode($translatedContent));
            }
        }

        return $response;
    }

    protected function translateContent($content, $targetLang)
    {
        // Si es un array de datos paginados
        if (isset($content['data']) && is_array($content['data'])) {
            $content['data'] = array_map(function ($item) use ($targetLang) {
                return $this->translateItem($item, $targetLang);
            }, $content['data']);
        } 
        // Si es un array simple
        elseif (is_array($content) && !isset($content['message'])) {
            $content = array_map(function ($item) use ($targetLang) {
                return is_array($item) ? $this->translateItem($item, $targetLang) : $item;
            }, $content);
        }
        // Si es un objeto único
        else {
            $content = $this->translateItem($content, $targetLang);
        }

        return $content;
    }

    protected function translateItem($item, $targetLang)
    {
        if (!is_array($item)) {
            return $item;
        }

        // Detectar tipo de modelo por campos disponibles
        $modelType = $this->detectModelType($item);

        if ($modelType && isset($this->translationFields[$modelType])) {
            $fieldsToTranslate = $this->translationFields[$modelType];

            foreach ($fieldsToTranslate as $field) {
                if (isset($item[$field]) && is_string($item[$field]) && !empty($item[$field])) {
                    $item[$field] = $this->translationService->translate($item[$field], $targetLang, 'es');
                }
            }
        }

        // Traducir relaciones anidadas
        foreach ($item as $key => $value) {
            if (is_array($value)) {
                $item[$key] = $this->translateItem($value, $targetLang);
            }
        }

        return $item;
    }

    protected function detectModelType($item)
    {
        // Detectar por campos únicos
        if (isset($item['service_category_id'])) {
            return 'service_requests';
        }
        if (isset($item['icon']) && isset($item['is_active'])) {
            return 'service_categories';
        }
        if (isset($item['document_type']) && isset($item['document_number'])) {
            return 'client_registrations';
        }
        if (isset($item['rating']) && isset($item['comment'])) {
            return 'reviews';
        }
        if (isset($item['sender_id']) && isset($item['content'])) {
            return 'messages';
        }
        if (isset($item['is_read']) && isset($item['message'])) {
            return 'notifications';
        }

        return null;
    }

    protected function normalizeLanguage($lang)
    {
        $lang = strtolower(substr($lang, 0, 2));
        return in_array($lang, ['en', 'es']) ? $lang : 'es';
    }
}