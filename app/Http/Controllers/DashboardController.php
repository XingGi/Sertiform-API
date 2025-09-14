<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Form;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        $stats = [
            'active_forms_count' => Form::where('is_template', false)->where('is_active', true)->count(),
            'templates_count' => Form::where('is_template', true)->count(),
            'submissions_count' => Submission::count(),
            'categories_count' => Category::count(),
        ];

        return response()->json($stats);
    }
}