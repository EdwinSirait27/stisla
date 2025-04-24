<?php

namespace App\Helpers;

class CategoryHelper
{
    public static function renderOptions($categories, $prefix = '')
    {
        $html = '';
        foreach ($categories as $category) {
            $html .= '<option value="' . $category->id . '">' . $prefix . $category->category_name . '</option>';
            if ($category->children && $category->children->isNotEmpty()) {
                $html .= self::renderOptions($category->children, $prefix . '— ');
            }
        }
        return $html;
    }
    
}