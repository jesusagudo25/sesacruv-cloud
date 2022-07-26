<?php

namespace App\Http\Controllers;

use App\Models\Analyst;
use Illuminate\Http\Request;

class AnalystController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Analyst::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:analyst',
            'password' => 'required|string|min:6|confirmed',
        ]);
        $analyst = Analyst::create($request->all());
        return response()->json($analyst, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Analyst  $analyst
     * @return \Illuminate\Http\Response
     */
    public function show(Analyst $analyst)
    {
        return Analyst::findOrFail($analyst->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Analyst  $analyst
     * @return \Illuminate\Http\Response
     */
    public function showReviews(Analyst $analyst)
    {
        return $analyst->reviews;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Analyst  $analyst
     * @return \Illuminate\Http\Response
     */
    public function showMessages(Analyst $analyst)
    {
        return $analyst->messages;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Analyst  $analyst
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Analyst $analyst)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:analyst,email,'.$analyst->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);
        $analyst->update($request->all());
        return response()->json($analyst, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Analyst  $analyst
     * @return \Illuminate\Http\Response
     */
    public function destroy(Analyst $analyst)
    {
        $analyst->delete();
        return response()->json(null, 204);
    }
}
