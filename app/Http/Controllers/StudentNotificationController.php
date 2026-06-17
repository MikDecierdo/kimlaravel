<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StudentNotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $student = Auth::guard('student')->user();

        $notifications = $student->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(function ($notification) {
                $data = (array) ($notification->data ?? []);
                $url = route('student.candidate-applications');

                return [
                    'id' => $notification->id,
                    'message' => (string) ($data['message'] ?? 'You have a new update.'),
                    'url' => $url,
                    'is_read' => $notification->read_at !== null,
                    'created_at_human' => optional($notification->created_at)->diffForHumans(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'unread_count' => $student->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        $student = Auth::guard('student')->user();
        $student->unreadNotifications->markAsRead();

        return response()->json([
            'success' => true,
        ]);
    }
}
