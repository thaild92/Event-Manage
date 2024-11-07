<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use App\Models\Event;
use Illuminate\Http\Request;

/**
 * @OA\Schema(
 *     schema="Attendee",
 *     required={"id","user_id", "event_id"},
 *     @OA\Property(property="id", type="integer", format="int64", example=1),
 *     @OA\Property(property="user_id", type="integer", format="int64", example=1),
 *     @OA\Property(property="event_id", type="integer", format="int64", example=2)
 * )
 */
class AttendeeController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Attendee::class, "attendee");
    }
    /**
     * @OA\Get(
     *   tags={"Attendees"},
     *   path="/api/events/{event_id}/attendees",
     *   summary="Get a list of attendees for a specific event",
     *   description="Returns a paginated list of attendees for a specific event",
     *   @OA\Parameter(
     *     name="event_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer",example=1),
     *     description="The ID of the event"
     *   ),
     *   @OA\Parameter(
     *     name="page",
     *     in="query",
     *     required=false,
     *     @OA\Schema(type="integer",example=1),
     *     description="The page number for paginated results"
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *         type="object",
     *         @OA\Property(property="data", type="array", 
     *                 @OA\Items(ref="#/components/schemas/Attendee")
     *         ),
     *      )
     *   )
     * )
     */
    public function index(Event $event)
    {
        $attendees = Attendee::latest()->paginate(5);
        return $attendees;
    }

    /**
     * @OA\Post(
     *   tags={"Attendees"},
     *   path="/api/events/{event_id}/attendees",
     *   summary="Create a new Attendee",
     *   security={{"bearerAuth":{}}},
     *   description="Stores a new attendee in the database",
     *   @OA\Parameter(
     *     name="event_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer",example=2)
     *   ),
     *   @OA\Response(
     *     response=201,
     *     description="OK",
     *     @OA\JsonContent(ref="#/components/schemas/Attendee")
     *   ),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403,description="Forbidden"),
     *   @OA\Response(response=404, description="Not Found")
     * )
     */
    public function store(Event $event)
    {
        $attendee = $event->attendees()->create([
            'user_id' => request()->user()->id
        ]);

        return $attendee;
    }

    /**
     * @OA\Get(
     *   tags={"Attendees"},
     *   path="/api/events/{event_id}/attendees/{attendee_id}",
     *   summary="Get a specific attendee",
     *   @OA\Parameter(
     *     name="event_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="integer",example=2)
     *   )
     *   ,
     *   @OA\Parameter(
     *     name="attendee_id",
     *     in="path",
     *     required=true,
     *     @OA\Schema(type="string",example=200)
     *   ),
     *   @OA\Response(
     *     response=200,
     *      description="OK",
     *    @OA\JsonContent(ref="#/components/schemas/Attendee")
     *  ),
     *   @OA\Response(
     *     response=404, 
     *     description="Not Found",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string",example="Not Found")
     *     ) 
     * )
     * )
     */
    public function show(Event $event, Attendee $attendee)
    {
        return $attendee;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event, Attendee $attendee)
    {
        //
    }

    /**
     * @OA\Delete(
     *   tags={"Attendees"},
     *   path="/api/events/{event_id}/attendees/{attendee_id}",
     *   summary="Attendee destroy",
     *   security={{"bearerAuth":{}}},
     *   @OA\Parameter(
     *     name="event_id",
     *     in="path",
     *     required=true,
     *     description="ID of the event to delete",
     *     @OA\Schema(type="integer",example=2)
     *   ),
     *   @OA\Parameter(
     *     name="attendee_id",
     *     in="path",
     *     required=true,
     *     description="ID of the attendee to delete",
     *     @OA\Schema(type="integer",example=200)
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="OK",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(
     *         property="message",
     *         type="string",
     *         example="Attendee deleted successfully!",
     *       )
     *   )
     *   ),
     *   @OA\Response(
     *     response=401,
     *      description="Unauthenticated",
     *      @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="message", type="string", example="Unauthenticated")
     *      )
     *  ),
     *   @OA\Response(
     *     response=403,
     *     description="Forbidden",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string",example="Forbidden")
     *     )
     *  ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found",
     *     @OA\JsonContent(
     *       type="object",
     *       @OA\Property(property="message", type="string",example="Not Found")
     *     )
     *    )
     * )
     */
    public function destroy(Event $event, Attendee $attendee)
    {
        // $attendee->delete();

        return response()->json([
            "message" => "Attendee deleted successfully!"
        ]);
    }
}
