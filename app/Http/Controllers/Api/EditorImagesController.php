<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileUploadHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EditorImagesController extends Controller
{
    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = FileUploadHelper::upload(
            file: $request->file('image'),
            folder: 'editor/images',

        );

        $url = asset('storage/' . $path);

        return response()->json([
            'url' => $url,
        ]);
    }
}
