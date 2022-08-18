<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        //Primero debes validar el numero de recibo ingresado.
        $request->validate([
            'id' => 'required|numeric|min:3|unique:students|unique:standbies',
            'phone_number' => 'required|numeric|min:7|unique:students|unique:standbies',
        ]);

        $collectionExcel = Excel::toCollection(new StudentsImport, storage_path('app/public/31diario.xls'));

        //Recorremos la coleccion de excel para buscar el numero de recibo ingresado.
/*         $StudentRequest = collect($collectionExcel[0])->where('recibo', $request->receipt_number)
        ->where('servicio', 'PRE REVISION')
        ->first(); */


        $StudentRequest = collect($collectionExcel[0])->where('recibo', $request->id)
        ->first();
 
        if(!$StudentRequest){
            return response()->json([
                'message' => 'El recibo no existe en el archivo'
            ], 404);
        }

        //Determinar la fecha en que se realizara el review.
        //Obtener el ultimo periodo registrado.
        $lastDateReviewPeriod = ReviewPeriod::orderBy('start_date', 'desc')->first();

        //Se verifica si el ultimo periodo es del mismo año.
        if(date('Y', strtotime(isset($lastDateReviewPeriod->start_date) ? $lastDateReviewPeriod->start_date : '2000-01-01')) == date('Y')){

            $student = Student::create([
                'id' => $request->id,
                'name' => $StudentRequest['nombre_y_apellido'],
                'identity_card' => $StudentRequest['cedula'],
                'phone_number' => $request->phone_number,
            ]);
            
            //Se obtiene la fecha mas reciente de un review
            $lastDateReview = Review::orderBy('date_review', 'desc')->first() ? Review::orderBy('date_review', 'desc')->first()->date_review : '2000-01-01';


            if(($lastDateReview >= $lastDateReviewPeriod->start_date) && ($lastDateReview <= $lastDateReviewPeriod->end_date)){

                //Se obtienen los users que tienen 3 reviews en la ultima fecha de review.
                $userUnavailable= User::select('users.id')->withCount(['reviews' => function($query) use ($lastDateReview){
                    $query->where('date_review', $lastDateReview);
                }])
                    ->having('reviews_count', '=', config('constants.options.option_max'))
                    ->get()->map(function ($users) {
                        return collect($users)->only(['id']);
                      });
                
                //Se obtiene los analysts que aun no tienen 3 reviews en el día de hoy.
                $userAvailable = User::select('users.id')->whereNotIn('users.id',$userUnavailable)->get();

                if(date('Y-m-d') >= $lastDateReview && date('Y-m-d') < $lastDateReviewPeriod->end_date ){
                    
                    $newDateReview = date('Y-m-d', strtotime(date('Y-m-d'). ' + 1 days'));
                    if(date('w', strtotime($newDateReview) == 0)){
                        $newDateReview = date('Y-m-d',strtotime(date('Y-m-d'). ' + 1 days'));
                    }
                    else if(date('w', strtotime($newDateReview) == 6)){
                        $newDateReview = date('Y-m-d',strtotime(date('Y-m-d'). ' + 2 days'));
                    }

                    $user = $userAvailable->random()->id;
                    $review = Review::create([
                        'user_id' => $user,
                        'student_id' => $student->id,
                        //El dia de la solcitud, sera hoy.
                        'date_review' => $newDateReview
                    ]);

                    //Se debe mejorar el tema de que cada students y standby mantenga su llave clave, y que el recibo sea una columna.
                    return response()->json([
                        'review' => [ 'id' => $review->id, 'name' => $review->student->name,
                        'identity_card' => $review->student->identity_card,],

                    ], 201);

                }
                else if(date('Y-m-d') < $lastDateReview && $userAvailable->count() > 0 ){
                    $user = $userAvailable->random()->id;
                    $review = Review::create([
                        'user_id' => $user,
                        'student_id' => $student->id,
                        //El dia de la solcitud, sera hoy.
                        'date_review' => $lastDateReview
                    ]);

                    return response()->json([
                        'review' => [ 'id' => $review->id, 'name' => $review->student->name,
                        'identity_card' => $review->student->identity_card,],
                    ], 201);
                }
                elseif($userAvailable->count() == 0 && date('Y-m-d') < $lastDateReview){
                    $newDateReview = date('Y-m-d', strtotime($lastDateReview. ' + 1 days'));
                    if(date('w', strtotime($newDateReview) == 0)){
                        $newDateReview = date('Y-m-d',strtotime($lastDateReview. ' + 1 days'));
                    }
                    else if(date('w', strtotime($newDateReview) == 6)){
                        $newDateReview = date('Y-m-d',strtotime($lastDateReview. ' + 2 days'));
                    }
                    
                    if($newDateReview <= $lastDateReviewPeriod->end_date){
                        $user = User::select('users.id')->get()->random()->id;
                        $review = Review::create([
                            'user_id' => $user,
                            'student_id' => $student->id,
                            //El dia de la solcitud, sera hoy.
                            'date_review' => $newDateReview
                        ]);
                        return response()->json([
                            'review' => [ 'id' => $review->id, 'name' => $review->student->name,
                            'identity_card' => $review->student->identity_card,],
                        ], 201);
                    }
                }
            }
            else{
                $today = date('Y-m-d');
                $startDate = $lastDateReviewPeriod->start_date;
                $endDate = $lastDateReviewPeriod->end_date;
                $user = User::select('users.id')->get();
                if($today < $startDate){

                    if(date('w', strtotime($startDate)) == 0){
                        $startDate = date('Y-m-d',strtotime($startDate. ' + 1 days'));
                    }
                    else if(date('w', strtotime($startDate)) == 6){
                        $startDate = date('Y-m-d',strtotime($startDate. ' + 2 days'));
                    }
    
                    $review = Review::create([
                        'user_id' => $user->random()->id,
                        'student_id' => $student->id,
                        //El dia de la solcitud, sera hoy.
                        'date_review' => $startDate
                    ]);
    
                    return response()->json([
                        'review' => [ 'id' => $review->id, 'name' => $review->student->name,
                        'identity_card' => $review->student->identity_card,],
                    ], 201);
                }
                else if(($today >= $startDate) && ($today < $endDate)){
                    
                    $today = date('Y-m-d',strtotime($today. ' + 1 days'));

                    if(date('w', strtotime($today)) == 0){
                        $today = date('Y-m-d',strtotime($today. ' + 1 days'));
                    }
                    else if(date('w', strtotime($today)) == 6){
                        $today = date('Y-m-d',strtotime($today. ' + 2 days'));
                    }
    
                    $review = Review::create([
                        'user_id' => $user->random()->id,
                        'student_id' => $student->id,
                        //El dia de la solcitud, sera hoy.
                        'date_review' => $today
                    ]);
    
                    return response()->json([
                        'review' => [ 'id' => $review->id, 'name' => $review->student->name,
                        'identity_card' => $review->student->identity_card,],
                    ], 201);
                }
            }
        }

        $standby = Standby::create([
            'id' => $request->id,
            'name' => $StudentRequest['nombre_y_apellido'],
            'identity_card' => $StudentRequest['cedula'],
            'phone_number' => $request->phone_number
        ]);

        return response()->json([
            'standby' => $standby->id,
            'name' => $standby->name,
            'identity_card' => $standby->identity_card,
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
        $review = Review::where('id', $review->id)->with('user','student')->first();

        return response()->json([
            'review' => $review
        ], 200);
    }

    public function showUser(Review $review)
    {
        return $review->user;
    }

    public function showStudent(Review $review)
    {
        return $review->student;
    }

    public function showMessages(Review $review)
    {
        return $review->messages;
    }

    public function checkStandby($student_id)
    {
        $review = Review::where('student_id', $student_id)->first();

        if($review){
            return response()->json([
                'review' => $review->id
            ], 200);
        }
        else{
            return response()->json([
                'review' => null
            ], 200);
        }
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
        
        $review =  Review::findOrFail($review->id)->update([
            'status' => $request->status
        ]);

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
        return response()->json($review, 200);Review::select(['id', 'date_review AS start'])->where('user_id', 1)->where('date_review', '>=', "2022-08-01")->where('date_review', '<=', "2022-09-10")->with(['students' => function($query){ $query->select(['name AS title']); }])->get();
    }

    public function schedule($startStr, $endDate, User $user)
    {
        $reviews = Review::where('user_id', $user->id)->where('date_review', '>=', $startStr)->where('date_review', '<=', $endDate)->with(['student'])->get();

        return response()->json($reviews, 200);
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
