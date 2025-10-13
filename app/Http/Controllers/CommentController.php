<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a new comment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'parent_id' => 'nullable|exists:comments,id',
            'content' => 'required|string|max:5000',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'commentable_type' => $validated['commentable_type'],
            'commentable_id' => $validated['commentable_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'content' => $validated['content'],
        ]);

        $comment->load(['user', 'replies']);

        return redirect()->back()->with('success', '评论发布成功！');
    }

    /**
     * Update a comment
     */
    public function update(Request $request, Comment $comment)
    {
        // Check if user owns the comment
        if ($comment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $comment->update($validated);

        return redirect()->back()->with('success', '评论已更新！');
    }

    /**
     * Delete a comment
     */
    public function destroy(Comment $comment)
    {
        // Check if user owns the comment or is admin
        if ($comment->user_id !== Auth::id() && !Auth::user()->is_admin) {
            abort(403, 'Unauthorized');
        }

        $comment->delete();

        return redirect()->back()->with('success', '评论已删除！');
    }
}
