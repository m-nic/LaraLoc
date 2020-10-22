<?php

namespace mNic\LaraLoc\Concerns;

use Illuminate\Database\Eloquent\Model;
use mNic\LaraLoc\Models\Translation;

trait Translatable
{
    private $currentLocale = null;
    private $shouldTranslateModel = false;

    private $translatedMap = [];
    private $originalData = [];
    private $defferedTranslationsCreation = [];

    abstract public function getTranslatableFieldNames();

    public static function bootTranslatable()
    {
        static::retrieved(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model | Translatable $model */
            $model->initInstance();
        });

        static::created(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model | Translatable $model */
            $model->saveAllTranslations();
        });

        static::saved(function ($model) {
            /** @var \Illuminate\Database\Eloquent\Model | Translatable $model */
            $model->saveAllTranslations();
        });
    }

    public function initInstance()
    {
        $defaultLocale = laraloc()->getFallBackLocale();
        $this->currentLocale = laraloc()->getModelLocale();
        $this->shouldTranslateModel = $this->currentLocale !== $defaultLocale;

        /** @var Model\ $this */
        $this->with[] = 'modelTranslations';
        $this->addHidden(['modelTranslations']);

        $this->storeOriginalModelData();
        $this->createTranslationMap();

        if ($this->shouldTranslateModel) {
            $this->setAttributesFromTranslation();
        }
    }

    public function modelTranslations()
    {
        /** @var \Illuminate\Database\Eloquent\Model $this */
        return $this->morphMany(Translation::class, 'translatable');
    }

    public function setAttribute($key, $value)
    {
        $value = $this->handleTranslationUpdates($key, $value);
        return parent::setAttribute($key, $value);
    }

    private function handleTranslationUpdates($key, $value)
    {
        if (
            $this->shouldTranslateModel &&
            $this->isTranslatableField($key) &&
            $this->valueIsDifferentThanOriginal($key, $value)
        ) {
            if ($this->exists) {
                $this->upsertTranslationFor($key, $value);
            } else {
                $this->registerTranslation($key, $value);
            }

            $value = $this->restoreOriginalValue($key);
        }

        return $value;
    }

    private function upsertTranslationFor($key, $value)
    {
        /** @var \Illuminate\Database\Eloquent\Model | Translatable $this */

        /** @var Model $translatedModel */
        $translatedModel = $this->modelTranslations
            ->where('column_name', $key)
            ->where('locale', $this->currentLocale)
            ->first();

        if ($translatedModel) {
            $this->defferedTranslationsCreation[] = function () use ($translatedModel, $value) {
                $translatedModel->value = $value;
                $translatedModel->save();
            };
        } else {
            $this->registerTranslation($key, $value);
        }
    }

    private function registerTranslation($key, $value)
    {
        $this->defferedTranslationsCreation[] = function () use ($key, $value) {
            $this->modelTranslations()->save(
                new Translation([
                    'column_name' => $key,
                    'locale'      => $this->currentLocale,
                    'value'       => $value,
                ])
            );
        };
    }

    private function restoreOriginalValue($key)
    {
        return $this->originalData[$key] ?? null;
    }

    public function saveAllTranslations()
    {
        foreach ($this->defferedTranslationsCreation as $deferredTranslation) {
            call_user_func($deferredTranslation);
        }

        $this->defferedTranslationsCreation = [];
    }


    private function createTranslationMap()
    {
        /** @var \Illuminate\Database\Eloquent\Model | Translatable $this */
        $this->translatedMap = $this->modelTranslations
            ->where('locale', $this->currentLocale)
            ->mapWithKeys(function ($translation) {
                return [
                    $translation->column_name => $translation->value,
                ];
            })
            ->toArray();
    }

    private function setAttributesFromTranslation()
    {
        foreach ($this->attributes as $key => $value) {
            /** @var \Illuminate\Database\Eloquent\Model | Translatable $this */
            if (in_array($key, $this->getTranslatableFieldNames())) {
                $this->attributes[$key] = $this->translatedMap[$key] ?? parent::getAttributeFromArray($key);
            }
        }
    }

    private function storeOriginalModelData()
    {
        if ($this->exists) {
            $this->originalData = $this->attributes;
        }
    }


    private function valueIsDifferentThanOriginal($key, $value)
    {
        if ($this->exists) {
            return $this->originalData[$key] !== $value;
        }

        return true;
    }

    private function isTranslatableField($key)
    {
        return in_array($key, $this->getTranslatableFieldNames());
    }
}
