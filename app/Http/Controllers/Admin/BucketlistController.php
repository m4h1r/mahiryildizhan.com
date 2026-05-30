<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseItem;
use App\Models\TodoItem;
use Illuminate\View\View;

class BucketlistController extends Controller
{
    public function index(): View
    {
        $purchaseItems = PurchaseItem::where('is_bucketlist', true)
            ->orderBy('is_completed')
            ->orderBy('title')
            ->get();

        $todoItems = TodoItem::where('is_bucketlist', true)
            ->orderBy('is_completed')
            ->orderBy('due_date')
            ->orderBy('title')
            ->get();

        $total = $purchaseItems->count() + $todoItems->count();
        $completed = $purchaseItems->where('is_completed', true)->count()
            + $todoItems->where('is_completed', true)->count();
        $percentage = $total > 0 ? min(100, (int) (($completed / $total) * 100)) : 0;

        return view('admin.bucketlist.index', compact(
            'purchaseItems',
            'todoItems',
            'total',
            'completed',
            'percentage',
        ));
    }
}
