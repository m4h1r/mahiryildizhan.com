<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Adage;
use App\Models\Person;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function search(Request $request): View
    {
        $query = trim((string) $request->string('q'));

        $posts = collect();
        $adages = collect();
        $people = collect();

        if ($query !== '') {
            $posts = Post::query()
                ->publiclyVisible()
                ->where(function ($builder) use ($query): void {
                    $builder
                        ->where('title', 'like', "%{$query}%")
                        ->orWhere('excerpt', 'like', "%{$query}%")
                        ->orWhere('body', 'like', "%{$query}%");
                })
                ->latest('published_at')
                ->limit(8)
                ->get();

            $adages = Adage::query()
                ->where(function ($builder) use ($query): void {
                    $builder
                        ->where('owner', 'like', "%{$query}%")
                        ->orWhere('adage', 'like', "%{$query}%")
                        ->orWhere('keywords', 'like', "%{$query}%");
                })
                ->latest('id')
                ->limit(8)
                ->get();

            $people = Person::query()
                ->where(function ($builder) use ($query): void {
                    $builder
                        ->where('name', 'like', "%{$query}%")
                        ->orWhere('surname', 'like', "%{$query}%")
                        ->orWhere('second_surname', 'like', "%{$query}%")
                        ->orWhere('notes', 'like', "%{$query}%");
                })
                ->latest('id')
                ->limit(8)
                ->get();
        }

        return view('public.search.index', [
            'title' => 'Search | '.config('app.name'),
            'query' => $query,
            'posts' => $posts,
            'adages' => $adages,
            'people' => $people,
        ]);
    }
}
