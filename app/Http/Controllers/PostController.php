<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PostDetailResource;

class PostController extends Controller
{
    //
    public function index(){

        $posts = Post::all();
        // return response()->json($posts);
        return PostDetailResource::collection($posts->loadMissing(['writer:id,username','comments:id,post_id,user_id,comments_content']));
    }

    public function show($id){


        // dd(Auth::user()->id);
        $post = Post::with('writer:id,username')->findOrFail($id);
        // return response()->json($posts);
        return new PostDetailResource($post->loadMissing(['writer:id,username','comments:id,post_id,user_id,comments_content']));

    }

    public function store(Request $request){
        $validated = $request->validate([
            'title' => 'required|max:255',
            'news_content' => 'required'
        ]);

        //upload imagae if exist
    //    $image = '';
       $image = null;
        if ($request->file) {

            Validator::validate($validated, [
                'photo' => [
                    // 'required',
                    File::image()
                        ->min(1024)
                        ->max(12 * 1024)
                        ->dimensions(Rule::dimensions()->maxWidth(1000)->maxHeight(500)),
                ],
            ]);


            $fileName = $this->generateRandomString();
            $extension = $request->file->extension();
            $image = $fileName. '.' .$extension;
            // dd($image);

            Storage::putFileAs('image', $request->file, $image);
        }

        $request['image'] = $image;
        $request['author'] = Auth::user()->id;
        $post = Post::create($request->all());
        return new PostDetailResource($post->loadMissing('writer:id,username'));

    }

    public function update(Request $request, $id){

        $validated = $request->validate([
            'title' => 'required|max:255',
            'news_content' => 'required',
        ]);


        // belum diterapkan sampai update by form-body fix


        // update image
        $image = null;
        if ($request->file) {

            Validator::validate($validated, [
                'photo' => [
                    // 'required',
                    File::image()
                        ->min(1024)
                        ->max(12 * 1024)
                        ->dimensions(Rule::dimensions()->maxWidth(1000)->maxHeight(500)),
                ],
            ]);


            $fileName = $this->generateRandomString();
            $extension = $request->file->extension();
            $image = $fileName. '.' .$extension;

            Storage::putFileAs('image', $request->file, $image);
        }

        $post = Post::findOrFail($id);
        $request['image'] = $image;
        $post->update($request->all());
        return new PostDetailResource($post->loadMissing('writer:id,username'));
    }

    public function destroy($id){
        $post = Post::findOrFail($id);
        $post->delete();

        // return new PostDetailResource($post->loadMissing('writer:id,username'));
        return('data terhapus');
    }
    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
