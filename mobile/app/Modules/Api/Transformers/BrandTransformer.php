<?php

namespace App\Modules\Api\Transformers;

class BrandTransformer
{
    public function transform(array $map)
    {
        return [
           'id' => $map['article_id'],
           'title' => $map['title'],
        ];
    }
}
