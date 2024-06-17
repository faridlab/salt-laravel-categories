<?php

namespace SaltCategories\Controllers;

use OpenApi\Annotations as OA;
use Illuminate\Http\Request;

use SaltLaravel\Controllers\Controller;
use SaltLaravel\Controllers\Traits\ResourceIndexable;
use SaltLaravel\Controllers\Traits\ResourceStorable;
use SaltLaravel\Controllers\Traits\ResourceShowable;
use SaltLaravel\Controllers\Traits\ResourceUpdatable;
use SaltLaravel\Controllers\Traits\ResourcePatchable;
use SaltLaravel\Controllers\Traits\ResourceDestroyable;
use SaltLaravel\Controllers\Traits\ResourceTrashable;
use SaltLaravel\Controllers\Traits\ResourceTrashedable;
use SaltLaravel\Controllers\Traits\ResourceRestorable;
use SaltLaravel\Controllers\Traits\ResourceDeletable;
use SaltLaravel\Controllers\Traits\ResourceImportable;
use SaltLaravel\Controllers\Traits\ResourceExportable;
use SaltLaravel\Controllers\Traits\ResourceReportable;
use SaltCategories\Models\CategoryTree;
/**
 * @OA\Info(
 *      title="Categories Endpoint",
 *      version="1.0",
 *      @OA\Contact(
 *          name="Farid Hidayat",
 *          email="farid@startapp.id",
 *          url="https://startapp.id"
 *      )
 *  )
 */
class CategoriesResourcesController extends Controller
{
    protected $modelNamespace = 'SaltCategories';

    /**
     * @OA\Get(
     *      path="/api/v1/countries",
     *      @OA\ExternalDocumentation(
     *          description="More documentation here...",
     *          url="https://github.com/faridlab/laravel-search-query"
     *      ),
     *      @OA\Parameter(
     *          in="query",
     *          name="search",
     *          required=false
     *      ),
     *      @OA\Response(
     *          response="200",
     *          description="List of Country"
     *      ),
     *      @OA\Response(response="default", description="Welcome page")
     * )
     */
    use ResourceIndexable;

    use ResourceStorable;
    use ResourceShowable;
    use ResourceUpdatable;
    use ResourcePatchable;
    use ResourceDestroyable;
    use ResourceTrashable;
    use ResourceTrashedable;
    use ResourceRestorable;
    use ResourceDeletable;
    use ResourceImportable;
    use ResourceExportable;
    use ResourceReportable;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function tree(Request $request, CategoryTree $model) {

        if(is_null($model)) {
            $this->responder->set('message', "Model not found!");
            $this->responder->setStatus(404, 'Not found.');
            return $this->responder->response();
        }

        try {
            $this->checkPermissions('index', 'read');
        } catch (\Exception $e) {
            $this->responder->set('message', 'You do not have authorization.');
            $this->responder->setStatus(401, 'Unauthorized');
            return $this->responder->response();
        }

        try {

            $count = $model->count();
            $model = $model->filter();

            $format = $request->get('format', 'default');

            $limit = intval($request->get('limit', 25));
            if($limit > 100) {
                $limit = 100;
            }

            $p = intval($request->get('page', 1));
            $page = ($p > 0 ? $p - 1: $p);

            if($format == 'datatable') {
                $draw = $request['draw'];
            }

            $modelCount = clone $model;
            $meta = array(
                'recordsTotal' => $count,
                'recordsFiltered' => $modelCount->count()
            );

            $data = $model
                        ->offset(0)
                        ->limit(1000)
                        ->orderBy('order','DESC')
                        ->get();

            $groups = [];
            $categories = [];

            foreach ($data as $key => $value) {
                if(is_null($value['parent_id'])) {
                    $value['children'] = [];
                    if(isset($groups[$value['id']])) {
                        $value['children'] = $groups[$value['id']];
                    }
                    $categories[] = $value;
                    continue;
                }

                if(!isset($groups[$value['parent_id']])) $groups[$value['parent_id']] = [];
                $value['children'] = [];
                if(isset($groups[$value['id']])) {
                    $value['children'] = $groups[$value['id']];
                }
                $groups[$value['parent_id']][] = $value;
            }

            $categories = $this->generateSlugHirarchy($categories);

            $this->responder->set('message', 'Data retrieved.');
            $this->responder->set('meta', $meta);
            $this->responder->set('data', $categories);

            // $this->responder->set('draw', $draw);
            $this->responder->set('recordsFiltered', $meta['recordsFiltered']);
            $this->responder->set('recordsTotal', $meta['recordsTotal']);

            return $this->responder->response();
        } catch(\Exception $e) {
            $this->responder->set('message', $e->getMessage());
            $this->responder->setStatus(500, 'Internal server error.');
            return $this->responder->response();
        }
    }

    function generateSlugHirarchy(&$categories, $parent = null) {

        foreach ($categories as $key => &$value) {
            $children = $value['children'];
            if(!is_null($parent)) {
                $value['slug'] = $parent['slug'].'/'.$value['slug'];
            }
            if(!count($children)) continue;
            $this->generateSlugHirarchy($children, $value);
        }

        return $categories;
    }
}

