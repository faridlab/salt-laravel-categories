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
        'choice' => 'nullable|in:1,0,true,false',
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
