<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user')->latest('id');

        if ($action = $request->string('action')->toString()) {
            $query->where('action', $action);
        }

        if ($modelType = $request->string('model_type')->toString()) {
            $query->where('model_type', $modelType);
        }

        return view('admin.activity-logs.index', [
            'title' => 'Activity Log',
            'heading' => 'Activity Log',
            'logs' => $query->paginate(50)->withQueryString(),
            'filters' => $request->only(['action', 'model_type']),
            'modelTypes' => ActivityLog::query()->select('model_type')->distinct()->orderBy('model_type')->pluck('model_type'),
        ]);
    }
}
