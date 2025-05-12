<?php

namespace SaltCategories\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Schema;

use SaltLaravel\Models\Resources;
use SaltLaravel\Traits\ObservableModel;
use SaltLaravel\Traits\Uuids;
use SaltCategories\Traits\Sluggable;
use SaltCategories\Traits\Orderable;
use SaltFile\Traits\Fileable;
use Illuminate\Support\Facades\Cache;

class Categories extends Resources {

    use Uuids;
    use ObservableModel;
    use Sluggable;
    use Orderable;

    use Fileable;
    protected $fileableFields = ['thumbnail', 'image'];
    protected $fileableCascade = true;
    protected $fileableDirs = [
        'thumbnail' => 'categories/thumbnail',
        'image' => 'categories/image',
    ];

    protected $filters = [
        'default',
        'search',
        'fields',
        'limit',
        'page',
        'relationship',
        'withtrashed',
        'orderby',
        // Fields table categories
        'id',
        'parent_id',
        'type',
        'type_other',
        'name',
        'slug',
        'order',
        'choice',
        'choice_order',
        'data',
    ];

    protected $rules = array(
        'parent_id' => 'nullable|string',
        'type' => 'required|string',
        'type_other' => 'nullable|string',
        'name' => 'required|string',
        'slug' => 'nullable|string',
        'order' => 'nullable|integer',
        // 'choice' => 'nullable|in:true,false,"true","false"',
        'choice' => 'nullable|boolean',
        'choice_order' => 'nullable|integer',
        'data' => 'nullable|json',
        'thumbnail' => 'nullable|image',
        'image' => 'nullable|image',
    );

    protected $auths = array (
        // 'index',
        'store',
        // 'show',
        'update',
        'patch',
        'destroy',
        'trash',
        'trashed',
        'restore',
        'delete',
        'import',
        'export',
        'report'
    );

    protected $forms = array();
    protected $structures = array();

    protected $searchable = array('parent_id', 'type', 'type_other', 'name', 'slug', 'order', 'choice', 'data', 'thumbnail', 'image', 'choice_order');
    protected $fillable = array('parent_id', 'type', 'type_other', 'name', 'slug', 'order', 'choice', 'data', 'thumbnail', 'image', 'choice_order');
    protected $casts = [];

    public function save(array $options = [])
    {
        $this->updated_at = now();
        return parent::save($options);
    }

    public function setChoiceAttribute($value)
    {
        if (gettype($value) == 'boolean') {
            $this->attributes['choice'] = $value;
            return;
        }
        $this->attributes['choice'] = ($value=='true');
    }

    public function getHierarchy($id, $reverse = false) {
        $order = $reverse ? 'DESC' : 'ASC';
        $cacheKey = 'category_hierarchy_' . $id;
        $cacheDuration = now()->addHours(24);
        return Cache::remember($cacheKey, $cacheDuration, function () use ($id, $order) {
            $hierarchy = DB::select("
                WITH RECURSIVE category_hierarchy AS (
                    SELECT
                        id,
                        name,
                        slug,
                        parent_id,
                        CAST(name AS VARCHAR) AS path
                    FROM categories
                    WHERE id = :id AND deleted_at IS NULL AND id != parent_id

                    UNION ALL

                    SELECT
                        c.id,
                        c.name,
                        c.slug,
                        c.parent_id,
                        CAST(ch.path || ' > ' || c.name AS VARCHAR)
                    FROM categories c
                    JOIN category_hierarchy ch ON c.parent_id = ch.id
                    WHERE c.deleted_at IS NULL AND c.id != c.parent_id
                )
                SELECT id, name, slug, path
                FROM category_hierarchy
                ORDER BY LENGTH(path) {$order};
            ", ['id' => $id]);

            return $hierarchy;
        });
    }

    function getCategoryHierarchyWithCache($categoryId)
    {
        $cacheKey = 'category_hierarchy_' . $categoryId;
        $cacheDuration = now()->addHours(24); // Cache selama 24 jam

        return Cache::remember($cacheKey, $cacheDuration, function () use ($categoryId) {
            $hierarchy = collect();
            $currentCategory = $this->find($categoryId);

            while ($currentCategory) {
                $hierarchy->prepend($currentCategory);
                $currentCategory = $currentCategory->parent;
            }

            return $hierarchy->toArray();
        });
    }

    public function parent() {
        return $this->belongsTo('SaltCategories\Models\Categories', 'parent_id', 'id')->withTrashed();
    }

    public function children() {
        return $this->hasMany('SaltCategories\Models\Categories', 'parent_id', 'id')->withTrashed();
    }

    public function image() {
        return $this->hasOne('SaltFile\Models\Files', 'foreign_id', 'id')
                    ->where('foreign_table', 'categories')
                    ->where('directory', 'categories/image')
                    ->withoutTrashed();
    }

    public function thumbnail() {
        return $this->hasOne('SaltFile\Models\Files', 'foreign_id', 'id')
                    ->where('foreign_table', 'categories')
                    ->where('directory', 'categories/thumbnail')
                    ->withoutTrashed();
    }
}
