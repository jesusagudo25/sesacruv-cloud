<?php

namespace App\Http\Controllers;

use App\Models\Analyst;
use App\Models\Student;
use App\Models\Review;
use App\Models\ReviewPeriod;
use App\Models\Standby;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\StudentsImport;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Review::all();
    }

    public function indexProcessed()
    {
        return Review::where('status', true)->get();
    }

    public function indexUnprocessed()
    {
        return Review::where('status', false)->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //config('constants.options.option_max');
        //Primero debes validar el numero de recibo ingresado.
        $request->validate([
            'receipt_number' => 'required|numeric|min:3|unique:students|unique:standbies',
        ]);

        $collectionExcel = Excel::toCollection(new StudentsImport, storage_path('app/public/31diario.xls'));

        //Recorremos la coleccion de excel para buscar el numero de recibo ingresado.
/*         $StudentRequest = collect($collectionExcel[0])->where('recibo', $request->receipt_number)
        ->where('servicio', 'PRE REVISION')
        ->first(); */

        $StudentRequest = collect($collectionExcel[0])->where('recibo', $request->receipt_number)
        ->first();
 
        if(!$StudentRequest){
            return response()->json([
                'message' => 'El recibo no existe en el archivo'
            ], 404);
        }

        //Determinar la fecha en que se realizara el review.
        //Se debe considerar en el futuro utilizar timestamp para determinar de una mejor forma las fechas

        //Obtener el ultimo periodo registrado.
        $lastDateReviewPeriod = ReviewPeriod::orderBy('date_start', 'desc')->first();

        //Se verifica si el ultimo periodo es del mismo año.
        if(date('Y', strtotime(isset($lastDateReviewPeriod->date_start) ? $lastDateReviewPeriod->date_start : '2000-01-01')) == date('Y')){

            $student = Student::create([
                'name' => $StudentRequest['nombre_y_apellido'],
                'identity_card' => $StudentRequest['cedula'],
                'receipt_number' => $StudentRequest['recibo'],
            ]);
            
            //Se obtiene la fecha mas reciente de un review
            $lastDateReview = Review::orderBy('date_review', 'desc')->first() ? Review::orderBy('date_review', 'desc')->first()->date_review : '2000-01-01';


            if(($lastDateReview >= $lastDateReviewPeriod->date_start) && ($lastDateReview <= $lastDateReviewPeriod->date_end)){

                //Se obtienen los analysts que tienen 3 reviews en la ultima fecha de review.
                $analystUnavailable= Analyst::select('analysts.id')->withCount(['reviews' => function($query) use ($lastDateReview){
                    $query->where('date_review', $lastDateReview);
                }])
                    ->having('reviews_count', '=', 3)
                    ->get()->map(function ($analysts) {
                        return collect($analysts)->only(['id']);
                      });
                
                //Se obtiene los analysts que aun no tienen 3 reviews en el día de hoy.
                $analystAvailable = Analyst::select('analysts.id')->whereNotIn('analysts.id',$analystUnavailable)->get();
                //Se obtiene los analysts que aun no tienen 3 reviews en el día de hoy.

                if($analystAvailable->count() > 0 && date('Y-m-d') < $lastDateReviewPeriod->date_end ){
                    //Aqui puede suceder que haya cupos para la ultima fecha de revision, pero el día es hoy y ya ha acabado, entonces eso se debe validar
                    //Tambien si hay cupos para la ultima fecha de revision, pero el día ya ha pasado, entonces eso se debe validar

                    $analyst = $analystAvailable->random()->id;
                    $review = Review::create([
                        'analyst_id' => $analyst,
                        'student_id' => $student->id,
                        //El dia de la solcitud, sera hoy.
                        'date_review' => $lastDateReview
                    ]);

                    return response()->json([
                        'review' => ['id' => $review->id, 'student' => $review->student]
                    ], 201);

                }else{
                    $newDateReview = date('Y-m-d', strtotime($lastDateReview. ' + 1 days'));
                    if(date('w', strtotime($newDateReview) == 7)){
                        $newDateReview = date('Y-m-d',strtotime($lastDateReview. ' + 1 days'));
                    }
                    else if(date('w', strtotime($newDateReview) == 6)){
                        $newDateReview = date('Y-m-d',strtotime($lastDateReview. ' + 2 days'));
                    }
                    
                    //Se debe tomar en cuenta el dia de hoy, porque si es una solicitud de hace varios dias entonces el analista si la puede recibir, mientras que si es una solicitud que se hace el mismo dia entonces no...
                    if($newDateReview <= $lastDateReviewPeriod->date_end){
                        $analyst = Analyst::select('analysts.id')->get()->random()->id;
                        $review = Review::create([
                            'analyst_id' => $analyst,
                            'student_id' => $student->id,
                            //El dia de la solcitud, sera hoy.
                            'date_review' => $newDateReview
                        ]);
                        return response()->json([
                            'review' => ['id' => $review->id, 'student' => $review->student]
                        ], 201);
                    }
                }
            }
            else{
                $today = date('Y-m-d');
                $startDate = $lastDateReviewPeriod->date_start;
                $endDate = $lastDateReviewPeriod->date_end;
                $analyst = Analyst::select('analysts.id')->get();
                if($today < $startDate){

                    if(date('w', strtotime($startDate)) == 7){
                        $startDate = date('Y-m-d',strtotime($startDate. ' + 1 days'));
                    }
                    else if(date('w', strtotime($startDate)) == 6){
                        $startDate = date('Y-m-d',strtotime($startDate. ' + 2 days'));
                    }
    
                    $review = Review::create([
                        'analyst_id' => $analyst->random()->id,
                        'student_id' => $student->id,
                        //El dia de la solcitud, sera hoy.
                        'date_review' => $startDate
                    ]);
    
                    return response()->json([
                        'review' => ['id' => $review->id, 'student' => $review->student]
                    ], 201);
                }
                else if(($today >= $startDate) && ($today < $endDate)){
                    
                    $today = date('Y-m-d',strtotime($today. ' + 1 days'));

                    if(date('w', strtotime($today)) == 7){
                        $today = date('Y-m-d',strtotime($today. ' + 1 days'));
                    }
                    else if(date('w', strtotime($today)) == 6){
                        $today = date('Y-m-d',strtotime($today. ' + 2 days'));
                    }
    
                    $review = Review::create([
                        'analyst_id' => $analyst->random()->id,
                        'student_id' => $student->id,
                        //El dia de la solcitud, sera hoy.
                        'date_review' => $today
                    ]);
    
                    return response()->json([
                        'review' => ['id' => $review->id, 'student' => $review->student]
                    ], 201);
                }
            }
        }

        $standby = Standby::create([
            'name' => $StudentRequest['nombre_y_apellido'],
            'identity_card' => $StudentRequest['cedula'],
            'receipt_number' => $StudentRequest['recibo'],
        ]);

        return response()->json([
            'standby' => $standby->id,
        ], 201);
        

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function show(Review $review)
    {
        $review = Review::findOrFail($review->id);

        return response()->json([
            'review' => $review,
            'student' => $review->student,
            'analyst' => $review->analyst,
        ], 200);
    }

    public function showAnalyst(Review $review)
    {
        return $review->analyst;
    }

    public function showStudent(Review $review)
    {
        return $review->student;
    }

    public function showMessages(Review $review)
    {
        return $review->messages;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Review $review)
    {
        $request->validate([
            'status' => 'required|boolean'
        ]);
        $review->update($request->all());
        return response()->json($review, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function setCalification(Request $request, Review $review)
    {
        $request->validate([
            'calification' => 'required|integer'
        ]);
        $review->update($request->all());
        return response()->json($review, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        $review->delete();
        return response()->json(null, 204);
    }
}
