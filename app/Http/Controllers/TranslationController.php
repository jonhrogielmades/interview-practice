<?php

namespace App\Http\Controllers;

use App\Services\AITranslationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TranslationController extends Controller
{
    /**
     * Handle the incoming request to translate an array of texts.
     *
     * @param Request $request
     * @param AITranslationService $translationService
     * @return JsonResponse
     */
    public function translate(Request $request, AITranslationService $translationService): JsonResponse
    {
        $validated = $request->validate([
            'texts' => ['required', 'array', 'max:200'],
            'texts.*' => ['required', 'string', 'max:2000'],
            'targetLanguage' => ['required', 'string', 'max:50'],
        ]);

        $textsToTranslate = $validated['texts'];
        $targetLanguage = $validated['targetLanguage'];
        
        // If the target language is English (assuming EN is base), we could just return it.
        // But the frontend usually won't even send EN requests.

        $translatedTexts = [];
        $textsNeedingApi = [];
        $indicesNeedingApi = [];

        // Check Cache first
        foreach ($textsToTranslate as $index => $text) {
            $cacheKey = 'translation_' . md5($targetLanguage . '_' . $text);
            $cachedTranslation = Cache::get($cacheKey);

            if ($cachedTranslation !== null) {
                $translatedTexts[$index] = $cachedTranslation;
            } else {
                $textsNeedingApi[] = $text;
                $indicesNeedingApi[] = $index;
            }
        }

        // Call AI Service for missing translations
        if (!empty($textsNeedingApi)) {
            $apiResults = $translationService->translateBatch($textsNeedingApi, $targetLanguage);

            if (!empty($apiResults)) {
                foreach ($apiResults as $i => $translatedText) {
                    $originalIndex = $indicesNeedingApi[$i];
                    $translatedTexts[$originalIndex] = $translatedText;
                    
                    // Cache the new translation forever
                    $cacheKey = 'translation_' . md5($targetLanguage . '_' . $textsNeedingApi[$i]);
                    Cache::forever($cacheKey, $translatedText);
                }
            } else {
                // If API failed entirely, populate with original texts so the frontend doesn't crash
                // but DO NOT cache these so we can try again later
                foreach ($textsNeedingApi as $i => $text) {
                    $originalIndex = $indicesNeedingApi[$i];
                    $translatedTexts[$originalIndex] = $text;
                }
            }
        }

        // Sort the array back by keys just to be safe
        ksort($translatedTexts);

        return response()->json([
            'translations' => array_values($translatedTexts)
        ]);
    }
}
