<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class MovieController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // For now, we'll just return a simple view.
        // Later, we will fetch the movie from the database.
        // $movie = Movie::findOrFail($id);
        return view('movies.show'); //, ['movie' => $movie]);
    }
}
