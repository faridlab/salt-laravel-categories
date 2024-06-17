<?php

namespace SaltCategories\Traits;

use Illuminate\Support\Str;
use SaltCategories\Models\Categories;

trait Orderable
{
    /**
     * Boot function from Laravel.
     */
    public static function bootOrderable() {
        static::creating(function ($model) {
            $parent = null;
            if(!empty($model->parent_id) && !is_null($model->parent_id)) {
                $parent = Categories::find($model->parent_id);
            }
            $order = 0;
            if(!is_null($parent)) {
                $order = $parent->order + 1;
            }
            $model->order = $order;
        });

        static::updating(function ($model) {
            $parent = null;
            if(!empty($model->parent_id) && !is_null($model->parent_id)) {
                $parent = Categories::find($model->parent_id);
            }
            $order = 0;
            if(!is_null($parent)) {
                $order = $parent->order + 1;
            }
            $model->order = $order;
        });
    }
}
