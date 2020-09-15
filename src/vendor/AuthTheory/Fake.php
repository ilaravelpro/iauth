<?php

namespace iLaravel\iAuth\Vendor\AuthTheory;

use Illuminate\Http\Request;

class Fake extends Theory
{
    public function __construct($model = null)
    {
        parent::__construct($model ?: new $this->theoryModel);
    }

    public function register(Request $request, $model = null, array $parameters = [])
    {

    }

    public function rules(Request $request)
    {
    }
}
