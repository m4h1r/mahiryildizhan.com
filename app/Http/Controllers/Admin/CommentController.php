<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(Request $request): View
    {
        $query = Comment::query()
            ->with(['post', 'user'])
            ->latest('id');

        if ($approval = $request->string('approval')->toString()) {
            if ($approval === 'approved') {
                $query->where('is_approved', true);
            }

            if ($approval === 'pending') {
                $query->where('is_approved', false);
            }
        }

        if ($search = trim((string) $request->string('q'))) {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('guest_name', 'like', "%{$search}%")
                    ->orWhere('guest_email', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        return view('admin.comments.index', [
            'title' => 'Comments',
            'heading' => 'Comments',
            'comments' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['approval', 'q']),
        ]);
    }

    public function approve(Comment $comment): RedirectResponse
    {
        $comment->update(['is_approved' => true]);

        return back()->with('success', 'Comment approved.');
    }

    public function destroy(Comment $comment): RedirectResponse
    {
        $comment->delete();

        return back()->with('success', 'Comment deleted.');
    }
}
