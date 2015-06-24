<?php
/**
 * Created by PhpStorm.
 * User: igor
 * Date: 14.04.2015
 * Time: 21:47
 */

namespace modules\blog\components;
use yii\base\Component;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;
//use yii\base\InvalidValueException;
use modules\blog\models\Category;

class CategoryTools extends Component {

    public function categoriesForDropDown() {
        $categories = Category::getCategories();

        $categoriesArray = [];
        $categoriesOptions = [];
        $parentStyle = ['style' => 'background-color:#eaeaea;font-weight:bold;'];

        foreach($categories as $category) {
            $parent_id = $category['parent_id'];

            $parentsArray = [];
            $parents = '';

            while (true) {
                if (!$parent_id) {
                    break;
                }

                $parentsArray[] = ' - ';
                $parent_id = $categories[$parent_id]['parent_id'];
            }

            if (count($parentsArray)) {
                krsort($parentsArray);
                $parents = implode('', $parentsArray);
            }

            $categoriesArray[$category['id']] = $parents.$category['name'];
            if ($category['parent_id'] === 0) {
                $categoriesOptions[$category['id']] = $parentStyle;
            }

        }

        return ['array' => $categoriesArray, 'options' => $categoriesOptions];
    }

    public function breadcrumbs($category_id, $crumb, $except=null) {
        $breadcrumbs = [];
        $categories = Category::getCategories();

        while ($category_id) {
            if ($category_id !== $except) {
                array_unshift($breadcrumbs, [
                    'label' => HtmlPurifier::process($categories[$category_id]['crumb']),
                    'url' => ['category/index', 'alias'=>$categories[$category_id]['alias']]
                ]);
            }
            $category_id = $categories[$category_id]['parent_id'];
        }

        if ($crumb) {
            $breadcrumbs[] = [
                'label' => $crumb
            ];
        }

        return $breadcrumbs;
    }

    public function subCategoryIds($category_id) {
        $ids = [];
        $categories = Category::getCategories();

        foreach($categories as $cat) {
            if ($cat['parent_id'] == $category_id) {
                $ids[] = $cat['id'];
            }
        }
        return $ids;
    }

    /**
     * @return array
     */
    public function categoryMenuItems() {
        $items = [];

        $categories = Category::getCategories();
        $parentIds = [];

        // set root items
        foreach ($categories as $cat) {
            if ($cat['parent_id']) {
                if (!isset($parentIds[$cat['parent_id']])) {
                    $parentIds[$cat['parent_id']] = [];
                }
                $parentIds[$cat['parent_id']][$cat['id']] = $cat['id'];
            }
            else {
                $items[$cat['id']] = [
                    'label' => $cat['crumb'],
                    'url' => Url::to(['category/index', 'alias'=>$cat['alias']])
                ];
            }
        }

        // walk roots
        foreach ($items as $item_id => $item) {
            if (isset($parentIds[$item_id])) {
                unset($items[$item_id]['url']);
                $items[$item_id]['items'] = [];

                foreach($parentIds[$item_id] as $child_id => $child) {
                    $items[$item_id]['items'][$child_id] = [
                        'label' => $categories[$child_id]['crumb'],
                        'url' => Url::to(['category/index', 'alias'=>$categories[$child_id]['alias']])
                    ];
                }
            }
        }

        return $items;
    }
}