<?php


namespace App\Http\Controllers\Category;


use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\JSONAPIService;
use App\Http\Controllers\Controller;

class CategoriesTasksRelationshipController extends Controller
{

    private $service;
    public function __construct(JSONAPIService $service)
    {
        $this->service = $service;
    }

    public function index(Category $category)
    {
        return $this->service->fetchRelationship($category, 'tasks');
    }
}
