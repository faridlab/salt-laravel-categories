<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories_json_path = base_path('database/seeders/categories.product.json');
        $content_json = file_get_contents($categories_json_path);
        $data = json_decode($content_json, true);

        $cats = [];
        $categories = $this->generateHirarchy($data, $cats);

        DB::table('categories')->insert($categories);
    }

    function generateHirarchy($data, &$categories, $parent = null) {
        $type = 'product';

        foreach ($data as $key => $value) {

            $name = $value['name'];
            if(!$name) continue;

            $uuid = Str::uuid()->toString();
            $slug = Str::slug($name, '-');
            $children = $value['children'];

            $parent_id = is_null($parent)? null: $parent['id'];
            $order = is_null($parent)? 0: $parent['order'] + 1;

            $category = array(
                'id' => $uuid,
                'parent_id' => $parent_id,
                'type' => $type,
                'type_other' => null,
                'name' => $name,
                'slug' => $slug,
                'order' => $order
            );

            $categories[] = $category;
            if(!count($children)) continue;
            $this->generateHirarchy($children, $categories, $category);
        }

        return $categories;
    }
}
