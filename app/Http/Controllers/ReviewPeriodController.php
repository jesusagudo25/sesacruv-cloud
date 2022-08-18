<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewPeriod;
use App\Models\Standby;
use App\Models\Student;
use App\Models\User;
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
            'start_date' => 'required|date|before:end_date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if(date('Y', strtotime($startDate)) != date('Y') && date('Y', strtotime($endDate)) != date('Y')) {
            return response()->json(['message' => 'El periodo debe ser del año actual'], 400);
        }

        $lastDateReviewPeriod = ReviewPeriod::orderBy('start_date', 'desc')->first();

        $newFormat = isset($lastDateReviewPeriod->start_date) ? $lastDateReviewPeriod->start_date : '1900-01-01';

        if(date('Y', strtotime($newFormat)) == date('Y', strtotime($startDate))){
            return response()->json(['message' => 'Ya existe un periodo para el año seleccionado'], 400);
        }
        
        $standbys = Standby::all();

        //Validación de fecha inicial
        if(date('w', strtotime($startDate)) == 0){
            $startDate = date('Y-m-d',strtotime($startDate. ' + 1 days'));
        }
        else if(date('w', strtotime($startDate)) == 6){
            $startDate = date('Y-m-d',strtotime($startDate. ' + 2 days'));
        }

        //Validación de fecha final
        if($startDate > $endDate){
            if(date('w', strtotime($endDate)) == 0){
                $endDate = date('Y-m-d',strtotime($endDate. ' + 1 days'));
            }
            else if(date('w', strtotime($endDate)) == 6){
                $endDate = date('Y-m-d',strtotime($endDate. ' + 2 days'));
            }
        }
        else{
            if(date('w', strtotime($endDate)) == 0){
                $endDate = date('Y-m-d',strtotime($endDate. ' - 2 days'));
            }
            else if(date('w', strtotime($endDate)) == 6){
                $endDate = date('Y-m-d',strtotime($endDate. ' - 1 days'));
            }
        }

        $validation = $startDate == $endDate ? true : false;

        if($validation){
            $endDate = date('Y-m-d',strtotime($endDate. ' + 1 days'));
        }

        ReviewPeriod::create([
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        //Recorrer la lista de espera

        $standbys->each(function ($standby, $key) use ($startDate, $endDate) {
            //Creación del estudiante************
            
            $student = Student::create([
                'id' => $standby->id,
                'name' => $standby->name,
                'identity_card' => $standby->identity_card,
                'phone_number' => $standby->phone_number,
            ]);

            //Creación de la revisión**************

            //Se obtiene la fecha mas reciente de un review
            $lastDateReview = Review::orderBy('date_review', 'desc')->first() ? Review::orderBy('date_review', 'desc')->first()->date_review : '2000-01-01';

            if(($lastDateReview >= $startDate) && ($lastDateReview <= $endDate)){

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

                if($userAvailable->count() > 0 ){
                    $user = $userAvailable->random()->id;
                    Review::create([
                        'user_id' => $user,
                        'student_id' => $student->id,
                        'date_request' => $standby->date_request,
                        'date_review' => $lastDateReview
                    ]);


                    $standby->delete();
                }
                elseif($userAvailable->count() == 0){
                    $newDateReview = date('Y-m-d', strtotime($lastDateReview. ' + 1 days'));
                    if(date('w', strtotime($newDateReview) == 7)){
                        $newDateReview = date('Y-m-d',strtotime($lastDateReview. ' + 1 days'));
                    }
                    else if(date('w', strtotime($newDateReview) == 6)){
                        $newDateReview = date('Y-m-d',strtotime($lastDateReview. ' + 2 days'));
                    }
                    
                    if($newDateReview <= $endDate){
                        $user = User::select('users.id')->get()->random()->id;
                        Review::create([
                            'user_id' => $user,
                            'student_id' => $student->id,
                            'date_request' => $standby->date_request,
                            'date_review' => $newDateReview
                        ]);

                        $standby->delete();
                    }
                }
            }
            else{
                $user = User::select('users.id')->get();

                Review::create([
                    'user_id' => $user->random()->id,
                    'student_id' => $student->id,
                    'date_request' => $standby->date_request,
                    'date_review' => $startDate
                ]);

                $standby->delete();
            }

        }); 

        //Que sucede con los standbys y los reviews al crear un nuevo periodo?
        return response()->json($standbys, 201);

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
