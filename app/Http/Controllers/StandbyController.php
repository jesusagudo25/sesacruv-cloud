<?php

namespace App\Http\Controllers;

use App\Models\Standby;
use Illuminate\Http\Request;

class StandbyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Standby::all();
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
            'last_name' => 'required|string|max:255',
            'identity_card' => 'required|string|max:255|unique:students|unique:standbies',
            'receipt_number' => 'required|string|max:255|unique:students|unique:standbies',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Standby  $standby
     * @return \Illuminate\Http\Response
     */
    public function show(Standby $standby)
    {
        $standby = Standby::findOrFail($standby->id);

        return response()->json([
            'standby' => $standby,
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Standby  $standby
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Standby $standby)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'identity_card' => 'required|string|max:255|unique:students|unique:standbies',
            'receipt_number' => 'required|string|max:255|unique:students|unique:standbies',
        ]);

        $standby->update($request->all());

        return response()->json([
            'standby' => $standby,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Standby  $standby
     * @return \Illuminate\Http\Response
     */
    public function destroy(Standby $standby)
    {
        $standby->delete();

        return response()->json([
            'message' => 'Standby deleted',
        ],200);
    }
}
