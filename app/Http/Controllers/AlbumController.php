<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
     public function readAll()
    {
        $albums = Album::all();
        return response()->json($albums);
    }

    public function readOne($id)
    {
        $albums = Album::find($id);
        if (!$albums) return response()->json(['message' => 'Not found'], 404);
        return response()->json($albums);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'album_name' => 'required|string|max:255',
            'release_date' => 'required|date|date_format:Y-m-d',
            'long' => 'required|integer|gt:0',
            'description' => 'required|string|max:255',
        ]);

        $album = Album::create($data);
        return response()->json($album, 201);
    }

    public function update(Request $request, $id)
    {
        $album = Album::find($id);
        if (!$album) return response()->json(['message' => 'Not found'], 404);

        $data = $request->validate([
            'album_name' => 'required|string|max:255',
            'release_date' => 'required|date|date_format:Y-m-d',
            'long' => 'required|integer|gt:0',
            'description' => 'required|string|max:255',
        ]);

        $album->update($data);
        return response()->json($album);
    }

    public function delete($id)
    {
        $album = Album::find($id);
        if (!$album) return response()->json(['message' => 'Not found'], 404);
        $album->delete();
        return response()->json(['message' => 'Soft deleted']);
    }
}


