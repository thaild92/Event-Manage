<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\TRaits\CanLoadRelationships;
use Illuminate\Support\Facades\Cache;

/**
 * @OA\Schema(
 *     schema="Event",
 *     required={"id", "name", "description", "start_time", "end_time", "user_id"},
 *     @OA\Property(property="id", type="integer", format="int64",example=1),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="description", type="string"),
 *     @OA\Property(property="start_time", type="string", format="date-time"),
 *     @OA\Property(property="end_time", type="string", format="date-time"),
 *     @OA\Property(property="user_id", type="integer", format="int64",example=1),
 * )
 */
class EventController extends Controller
{
    use CanLoadRelationships;
    private   $relations = ['user', 'attendees', 'attendees.user'];

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->middleware('throttle:3,1')->only(['update']);
        $this->authorizeResource(Event::class, 'event');
    }



    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="List all events",
     *     description="Show all events in latest order",
     *     operationId="getAllEvent",
     *     tags={"Events"},
     *     @OA\Parameter(
     *       name="include",
     *       in="query",
     *       required=false,
     *       @OA\Schema(type="string",example="user,attendees,attendees.user")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *           type="object",
     *           @OA\Property(
     *             property="data",
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Event"),
     *           )     
     *        )
     *     ),
     *      @OA\Response(
     *      response=400,
     *      description="Validate Error",
     *      @OA\JsonContent(
     *          @OA\Property(property="message", type="string", example="The given data was invalid."),
     *      )
     *      )
     * )
     */

    public function index()
    {
        $query = $this->loadRelationShips(Event::query());

        $cacheKey = 'events:' . request()->query('include');
        $events = Cache::remember($cacheKey, 60, fn() => $query->latest()->paginate());

        // return view('welcome', ['events' => $events]);
        return EventResource::collection($events);
    }

    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Create a new event",
     *     description="Stores a new event in the database",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "start_time", "end_time"},
     *             @OA\Property(property="name", type="string", maxLength=255, example="Sample Event"),
     *             @OA\Property(property="description", type="string", example="This is a sample event description."),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-12-01 10:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-12-01 12:00:00"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Event created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="Sample Event"),
     *             @OA\Property(property="description", type="string", example="This is a sample event description."),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2023-12-01 10:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2023-12-01 12:00:00"),
     *             @OA\Property(property="user_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object",
     *                 @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name field is required."))
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {

        $event =  Event::create([
            ...$request->validate([
                "name" => "required|string|max:255",
                "description" => "nullable|string",
                "start_time" => 'required|date',
                "end_time" => 'required|date|after:start_time',
            ]),
            'user_id' => request()->user()->id
        ]);

        return $event;
    }

    /**
     * @OA\Get(
     *     path="/api/events/{id}",
     *     summary="Get a specific event",
     *     operationId="getEvent",
     *     tags={"Events"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1),
     *         description="ID of the event to retrieve"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event retrieved successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found"
     *     )
     * )
     */
    public function show(Event $event)
    {
        $event = $this->loadRelationShips($event);
        return new EventResource($event);
    }

    /**
     * @OA\Put(
     *     path="/api/events/{id}",
     *     summary="Update an existing event",
     *     operationId="updateEvent",
     *     tags={"Events"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="ID of the event to update"
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Updated Event Name"),
     *             @OA\Property(property="description", type="string", example="Updated description for the event."),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2024-11-20 18:00:00"),
     *             @OA\Property(property="end_time", type="string", format="date-time", example="2024-11-20 21:00:00"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Event updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Event")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Event not found"
     *     ),
     *    @OA\Response(
     *         response=403,
     *         description="Forbidden - User does not have permission to update this event"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - User is not authenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     )
     * )
     */
    public function update(Request $request, Event $event)
    {
        $event->update(
            [
                ...$request->validate([
                    "name" => "sometimes|string|max:255",
                    "description" => "nullable|string",
                    "start_time" => 'sometimes|date',
                    "end_time" => [
                        'sometimes',
                        'date',
                        function ($attribute, $value, $fail) use ($request, $event) {
                            $startTime = $request->input('start_time') ?? $event->start_time;
                            if ($startTime && $value < $startTime) {
                                $fail("The end time must be after the start time.");
                            }
                        }
                    ],
                ])
            ]
        );

        return $event;
    }


    /**
     * @OA\Delete(
     *   tags={"Events"},
     *   path="/api/events/{id}",
     *   summary="Event destroy",
     *   @OA\Parameter(
     *       name="id",
     *       in="path",
     *       required=true,
     *       description="ID of the event to delete",
     *       @OA\Schema(type="integer")
     *   ),
     *   @OA\Response(
     *     response=200,
     *     description="Event deleted successfully",
     *     @OA\JsonContent(ref="#/components/schemas/Event") 
     *   ),
     *   @OA\Response(
     *     response=404,
     *     description="Not Found"
     *   )
     * )
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return response()->json([
            "message" => "Event deleted successfully!"
        ]);
    }
}
