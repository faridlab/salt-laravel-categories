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
            $model->generateUniqueSlug();
        });

        static::updating(function ($model) {
            // Only regenerate slug if name has changed
            if ($model->isDirty('name')) {
                $model->generateUniqueSlug();
            }
        });
    }

    /**
     * Generate a unique slug for the model
     */
    protected function generateUniqueSlug()
    {
        // If slug is provided and not empty, use it as base
        $baseSlug = !empty($this->slug) ? $this->slug : Str::slug($this->name, '-');

        // Get the parent category if exists
        $parentSlug = '';
        if (!empty($this->parent_id)) {
            $parent = Categories::find($this->parent_id);
            if ($parent) {
                $parentSlug = $parent->slug . '/';
            }
        }

        $slug = $parentSlug . $baseSlug;
        $originalSlug = $slug;
        $count = 1;

        // Keep checking until we find a unique slug
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        $this->slug = $slug;
    }

    /**
     * Check if a slug exists in the database
     */
    protected function slugExists($slug)
    {
        $query = Categories::where('slug', $slug);

        // When updating, exclude the current record
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }
}
