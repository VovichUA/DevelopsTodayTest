<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Post;

use App\Http\Controllers\API\v1\BaseController;
use App\Models\Post;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class VoteController extends BaseController
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'vote' => 'boolean'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors()->toArray());
        }

        $post = Post::query()->find($request->post_id);
        if ($post === null) {
            return $this->sendError('Post not found');
        }

        try {
            $vote = new Vote();
            $vote->vote = $request->vote;
            $vote->post()->associate($post);
            $vote->user()->associate(auth()->user());
            $vote->save();
            return $this->sendResponse($vote->toArray(),'Vote created successfully.');
        } catch (Throwable $e) {
            return $this->sendError('Some problems with send vote', [$e->getMessage()]);
        }
    }
}
