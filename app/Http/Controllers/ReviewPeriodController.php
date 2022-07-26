<?php

namespace App\Http\Controllers;

use App\Models\ReviewPeriod;
use Illuminate\Http\Request;

class ReviewPeriodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ReviewPeriod::all();
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
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ]);
        $reviewPeriod = ReviewPeriod::create($request->all());
        return response()->json($reviewPeriod, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReviewPeriod  $reviewPeriod
     * @return \Illuminate\Http\Response
     */
    public function show(ReviewPeriod $reviewPeriod)
    {
        return ReviewPeriod::findOrFail($reviewPeriod->id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ReviewPeriod  $reviewPeriod
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ReviewPeriod $reviewPeriod)
    {
        $request->validate([
            'date_start' => 'required|date',
            'date_end' => 'required|date',
        ]);
        $reviewPeriod->update($request->all());
        return response()->json($reviewPeriod, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReviewPeriod  $reviewPeriod
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReviewPeriod $reviewPeriod)
    {
        $reviewPeriod->delete();
        return response()->json(null, 204);
    }
}
