<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = $request->input('q', '');
        $limit = $request->input('limit', 10);
        $skip = $request->input('skip', 0);
        $select = $request->input('select', '*');
        $sortBy = $request->input('sortBy', 'id');
        $order = $request->input('order', 'asc');


        $selectFields = $select !== '*' ? explode(',', $select) : ['*'];


        $posts = Post::where(function($queryBuilder) use ($query) {
                if ($query) {
                    $queryBuilder->where('title', 'LIKE', '%' . $query . '%')
                                ->orWhere('slug', 'LIKE', '%' . $query . '%')
                                ->orWhere('content', 'LIKE', '%' . $query . '%');
                }
            })
            ->select($selectFields)
            ->limit($limit)
            ->skip($skip)
            ->orderBy($sortBy, $order)
            ->get();

        return response()->json([
            'message' => 'OK',
            'data' => $posts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('post_images', 'public');
        }

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $post = Post::create([
            'user_id' => Auth::user()->id,
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'image_path' => $imagePath,
            'created_at' => now(),
            'last_update' => now(),
        ]);

        return response()->json([
            'message' => 'Post créé avec succès !',
            'data' => $post,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function showById($id)
    {
        $post = Post::find($id);

        if (!$post) {
            return response()->json([
                'message' => 'Post non trouvé',
                'data' => $posts,
            ], 404);
        }

        return response()->json([
            'message' => 'OK',
            'data' => $posts,
        ], 200);
    }

    public function showBySlug($slug)
    {
        $post = Post::where('slug', $slug)->first();

        if (!$post) {
            return response()->json([
                'message' => 'Post non trouvé',
                'data' => $posts,
            ], 404);
        }

        return response()->json([
            'message' => 'OK',
            'data' => $posts,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 400);
        }

        $post = Post::where('id', $id)->where('user_id', Auth::user()->id)->first();

        if (!$post) {
            return response()->json([
                'message' => 'Vous n\'avez pas le droit de modifier ce post',
            ], 403);
        }

        $imagePath = $post->image_path;
        if ($request->hasFile('image')) {
            if ($imagePath && Storage::exists('public/' . $imagePath)) {
                \Storage::delete('public/' . $imagePath);
            }

            $imagePath = $request->file('image')->store('post_images', 'public');
        }

        $slug = Str::slug($request->title);
        $originalSlug = $slug;
        $counter = 1;

        if ($request->title != $post->title) {
            while (Post::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }

        $post->update([
            'title' => $request->title,
            'slug' => $slug,
            'content' => $request->content,
            'image_path' => $imagePath,
            'last_update' => now()
        ]);

        return response()->json([
            'message' => 'Post mis à jour avec succès !',
            'data' => $post,
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $user = Auth::user();
        $post = Post::where('id', $id)->where('user_id', $user->id)->first();

        if (!$post) {
            return response()->json([
                'message' => 'Vous n\'avez pas le droit de supprimer ce post',
            ], 403);
        }
        $post->delete();

        return response()->json([
            'message' => 'Post supprimé avec succès !',
        ], 200);
    }
}
