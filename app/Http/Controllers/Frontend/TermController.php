<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TermController extends FrontendController
{
    public function index()
    {
        return $this->view('frontend.terms');
    }
    public function privacy()
    {
        return $this->view('frontend.privacy');
    }
}
