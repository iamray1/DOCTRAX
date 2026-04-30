<?php

namespace App\Support;

use App\Models\Document;
use App\Models\User;

class SubmissionNotifications
{
    public static function forUser(?User $user, int $limit = 20): array
    {
        if (!$user || !$user->exists) {
            return [
                'submissionNotificationCount' => 0,
                'submissionNotificationDocs' => collect(),
                'submissionNotificationLimit' => $limit,
            ];
        }

        $base = Document::query()
            ->with(['currentOffice:id,name', 'submittedToOffice:id,name'])
            ->where('user_id', $user->id)
            ->whereIn('status', ['for_pickup', 'returned']);

        return [
            'submissionNotificationCount' => (clone $base)->count(),
            'submissionNotificationDocs' => (clone $base)
                ->orderByDesc('last_action_at')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit($limit)
                ->get(),
            'submissionNotificationLimit' => $limit,
        ];
    }
}
