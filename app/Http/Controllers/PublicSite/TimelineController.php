<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\TimelineEvent;
use Illuminate\View\View;

class TimelineController extends Controller
{
    public function publicTimeline(): View
    {
        $events = TimelineEvent::query()
            ->where('is_public', true)
            ->orderBy('order')
            ->orderBy('start_date')
            ->get();

        return view('public.timeline.index', [
            'title' => 'Timeline | '.config('app.name'),
            'events' => $events,
        ]);
    }
}
