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

class Categories extends Resources {

    use Uuids;
    use ObservableModel;
    use Sluggable;
    use Orderable;

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
        'data',
    ];

    protected $rules = array(
        'parent_id' => 'nullable|string',
        'type' => 'required|string',
        'type_other' => 'nullable|string',
        'name' => 'required|string',
        'slug' => 'nullable|string',
        'order' => 'nullable|integer',
        'data' => 'nullable|json',
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

    protected $searchable = array('parent_id', 'type', 'type_other', 'name', 'slug', 'order', 'data');
    protected $fillable = array('parent_id', 'type', 'type_other', 'name', 'slug', 'order', 'data');
    protected $casts = [];

    public function parent() {
        return $this->belongsTo('SaltCategories\Models\Categories', 'parent_id', 'id')->withTrashed();
    }

    public function children() {
        return $this->hasMany('SaltCategories\Models\Categories', 'parent_id', 'id')->withTrashed();
    }

}
