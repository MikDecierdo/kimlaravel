<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventComment;
use App\Models\Staff;
use App\Traits\DeptVariantHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EventController extends Controller
{
    use DeptVariantHelper;

    public function index(Request $request)
    {
        $user = Auth::guard('student')->user();
        
        // Students see only their department's events
        if ($user->isStudent()) {
            $visibleDepts = $this->visibleEventDepts($user->department);
            $events = Event::whereIn('department', $visibleDepts)
                ->withCount(['likes', 'comments'])
                ->with([
                    'staff',
                    'likes',
                    'comments' => function ($q) { $q->latest()->with('user'); },
                ])
                ->latest()
                ->get();

            $this->hydrateLegacyPosterStaff($events);
        } else {
            // Admin sees all events
            $events = Event::withCount(['likes', 'comments'])
                ->with('comments')
                ->latest()
                ->get();
        }

        return view('student.events', compact('events'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'department' => 'required|string',
            'event_date' => 'required|date',
            'description' => 'required|string'
        ]);

        $event = Event::create([
            ...$validated,
            'user_id' => Auth::guard('student')->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event posted successfully!',
            'event' => $event
        ]);
    }

    public function destroy(Event $event)
    {
        // Admin can always delete.
        if (Auth::guard('admin')->check()) {
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully!'
            ]);
        }

        // Event creator (student) can delete own event.
        $student = Auth::guard('student')->user();
        if (!$student || $event->user_id !== $student->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $event->delete();

        return response()->json([
            'success' => true,
            'message' => 'Event deleted successfully!'
        ]);
    }

    public function toggleLike(Event $event, Request $request)
    {
        $user = Auth::guard('student')->user();
        if (!$this->canAccessEvent($event, $user->department ?? '')) {
            return response()->json([
                'success' => false,
                'message' => 'You can only react to events visible to your department.'
            ], 403);
        }

        $reactionType = $request->input('reaction_type', 'like'); // like, haha, love, etc.
        
        // Check if user already reacted to the event
        $existingReaction = $event->likes()->where('user_id', $user->id)->first();
        
        if ($existingReaction) {
            // If same reaction, remove it (toggle off)
            if ($existingReaction->reaction_type === $reactionType) {
                $existingReaction->delete();
                return response()->json([
                    'success' => true,
                    'reacted' => false,
                    'reaction_type' => null,
                    'reactions' => $this->getReactionsSummary($event),
                    'message' => 'Reaction removed!'
                ]);
            } else {
                // Update to new reaction type
                $existingReaction->update(['reaction_type' => $reactionType]);
                return response()->json([
                    'success' => true,
                    'reacted' => true,
                    'reaction_type' => $reactionType,
                    'reactions' => $this->getReactionsSummary($event),
                    'message' => 'Reaction updated!'
                ]);
            }
        } else {
            // Add new reaction
            $event->likes()->create([
                'user_id' => $user->id,
                'reaction_type' => $reactionType
            ]);
            return response()->json([
                'success' => true,
                'reacted' => true,
                'reaction_type' => $reactionType,
                'reactions' => $this->getReactionsSummary($event),
                'message' => 'Reaction added!'
            ]);
        }
    }

    public function addComment(Event $event, Request $request)
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:1000'
        ]);

        $user = Auth::guard('student')->user();
        if (!$this->canAccessEvent($event, $user->department ?? '')) {
            return response()->json([
                'success' => false,
                'message' => 'You can only comment on events visible to your department.'
            ], 403);
        }
        
        $comment = $event->comments()->create([
            'user_id' => $user->id,
            'comment' => $validated['comment']
        ]);

        $comment->load('user');

        return response()->json([
            'success' => true,
            'comment' => $comment,
            'message' => 'Comment added!'
        ]);
    }

    public function getComments(Event $event)
    {
        $user = Auth::guard('student')->user();
        if (!$this->canAccessEvent($event, $user->department ?? '')) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view comments for events visible to your department.'
            ], 403);
        }

        $comments = $event->comments()->with('user')->latest()->get();
        
        return response()->json([
            'success' => true,
            'comments' => $comments
        ]);
    }

    private function getReactionsSummary($event)
    {
        $reactions = $event->likes()
            ->selectRaw('reaction_type, COUNT(*) as count')
            ->groupBy('reaction_type')
            ->get()
            ->pluck('count', 'reaction_type');

        return [
            'total' => $event->likes()->count(),
            'like' => $reactions->get('like', 0),
            'haha' => $reactions->get('haha', 0),
            'love' => $reactions->get('love', 0),
        ];
    }

    private function visibleEventDepts(string $department): array
    {
        $depts = $this->getDeptVariants($department);
        if (!in_array('CSG', $depts, true)) {
            $depts[] = 'CSG';
        }

        return array_values(array_unique($depts));
    }

    private function canAccessEvent(Event $event, string $department): bool
    {
        return in_array($event->department, $this->visibleEventDepts($department), true);
    }

    private function hydrateLegacyPosterStaff($events): void
    {
        $legacyPosterIds = $events
            ->filter(fn (Event $event) => !$event->staff && !empty($event->user_id))
            ->pluck('user_id')
            ->unique()
            ->values();

        if ($legacyPosterIds->isEmpty()) {
            return;
        }

        $legacyPosterMap = Staff::whereIn('id', $legacyPosterIds)->get()->keyBy('id');

        $events->each(function (Event $event) use ($legacyPosterMap) {
            if (!$event->staff && !empty($event->user_id) && $legacyPosterMap->has($event->user_id)) {
                $event->setRelation('staff', $legacyPosterMap->get($event->user_id));
            }
        });
    }
}
