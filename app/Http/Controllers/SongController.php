<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    
    public function readAll()
    {
        $songs = Song::with('album')->get();
        return response()->json($songs);
    }

    public function readOne($id)
    {
        $song = Song::with('album')->find($id);
        if (!$song) return response()->json(['message' => 'Not found'], 404);
        return response()->json($song);
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|gt:0',
            'lyrics' => 'nullable|string|max:255',
            'album_id' => 'required|exists:albums,id',
        ]);

        $song = Song::create($data);
        return response()->json($song->load('albums'), 201);
    }

    public function update(Request $request, $id)
    {
        $song = Song::find($id);
        if (!$song) return response()->json(['message' => 'Not found'], 404);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'duration' => 'required|integer|gt:0',
            'lyrics' => 'nullable|string|max:255',
            'album_id' => 'required|exists:albums,id',
        ]);

        $song->update($data);
        return response()->json($song->load('album'));
    }

    public function delete($id)
    {
        $song = Song::find($id);
        if (!$song) return response()->json(['message' => 'Not found'], 404);
        $song->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
