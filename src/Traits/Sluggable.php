<?php

namespace SaltCategories\Traits;

use Illuminate\Support\Str;
use SaltCategories\Models\Categories;

trait Sluggable
{
    /**
     * Boot function from Laravel.
     */
    public static function bootSluggable() {
        static::creating(function ($model) {
            if(empty($model->slug) && is_null($model->slug)) {
                $model->slug = Str::slug($model->name, '-');
            }

            $count = Categories::where('slug', $model->slug)->count();
            if($count === 0) return;

            $model->slug = $model->slug .'-'. ($count + 1);
        });

        static::updating(function ($model) {
            $count = Categories::where('slug', $model->slug)->count();
            if($count === 0) return;

            $model->slug = $model->slug .'-'. ($count + 1);
        });
    }
}
