<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Post;

use App\Http\Controllers\API\v1\BaseController;
use App\Models\Post;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $posts = Post::query()->paginate();

        return $this->sendResponse($posts->items(), 'Posts retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'body' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->toArray());
        }
        $link = Str::slug($request->title,'_');
        try {
            $post = new Post();
            $post->title = $request->title;
            $post->body = $request->body;
            $post->link = $link;
            $post->user()->associate(auth()->user());
            $post->save();
            return $this->sendResponse($post->toArray(), 'Post created successfully.');
        } catch (Throwable $e) {
            return $this->sendError('Post not create',[$e->getMessage()],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $post = Post::query()->find($id);
        if (is_null($post)) {
            return $this->sendError('Post not found.');
        }
        return $this->sendResponse($post->toArray(), 'Post retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'body' => 'required'
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->toArray());
        }

        $post = Post::query()->find($request->route('id'));
        if ($post === null) {
            return $this->sendError('Post not found');
        }

        $post->title = $request->title;
        $post->body = $request->body;
        $post->link = Str::slug($request->title,'_');
        $post->saveOrFail();

        return $this->sendResponse($post->toArray(), 'Post updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function destroy(Request $request): JsonResponse
    {
        Post::query()->find($request->route('id'))?->delete();

        return $this->sendResponse([], 'Post deleted successfully.');
    }
}
