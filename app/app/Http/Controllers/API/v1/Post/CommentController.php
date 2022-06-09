<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Post;

use App\Http\Controllers\API\v1\BaseController;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CommentController extends BaseController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'comment' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->toArray());
        }

        $post = Post::query()->find($request->post_id);
        if ($post === null) {
            return $this->sendError('Post not found');
        }

        try {
            $comment = new Comment();
            $comment->comment = $request->comment;
            $comment->post()->associate($post);
            $comment->user()->associate(auth()->user());
            $comment->save();

            return $this->sendResponse($comment->toArray(),'Comment created successfully.');
        } catch (Throwable $e) {
            return $this->sendError('Some problems with create comment', [$e->getMessage()]);
        }
    }
}
