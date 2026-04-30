@php
    $submissionNotificationDocs = collect($submissionNotificationDocs ?? []);
    $submissionNotificationCount = (int) ($submissionNotificationCount ?? $submissionNotificationDocs->count());
    $submissionNotificationLimit = (int) ($submissionNotificationLimit ?? $submissionNotificationDocs->count());
@endphp

<div class="submission-notification-wrap" id="submissionNotification">
    <button type="button" class="submission-notif-btn" id="submissionNotificationToggle" aria-label="Pickup and return document notifications" aria-expanded="false" aria-controls="submissionNotificationPanel">
        <i class="fas fa-bell"></i>
        @if($submissionNotificationCount > 0)
            <span class="submission-notif-badge">{{ \App\Support\UiNumber::compact($submissionNotificationCount) }}</span>
        @endif
    </button>
    <div class="submission-notif-panel" id="submissionNotificationPanel" role="dialog" aria-labelledby="submissionNotificationTitle">
        <div class="submission-notif-head">
            <div class="submission-notif-title" id="submissionNotificationTitle">Pickup / Return</div>
            @if($submissionNotificationCount > 0)
                <span class="submission-notif-count">{{ \App\Support\UiNumber::compact($submissionNotificationCount) }} total</span>
            @endif
        </div>

        @if($submissionNotificationDocs->isNotEmpty())
            <div class="submission-notif-list">
                @foreach($submissionNotificationDocs as $nDoc)
                    @php
                        $notifLookup = $nDoc->reference_number ?: ($nDoc->tracking_number ?: '');
                        $notifStatus = preg_replace('/[^a-z0-9_-]/', '', (string) $nDoc->status);
                        $notifOfficeName = $nDoc->status === 'submitted'
                            ? ($nDoc->submittedToOffice?->name ? 'To: ' . $nDoc->submittedToOffice->name : 'Awaiting routing')
                            : ($nDoc->currentOffice?->name
                                ? 'At: ' . $nDoc->currentOffice->name
                                : ($nDoc->submittedToOffice?->name ? 'To: ' . $nDoc->submittedToOffice->name : 'In transit'));
                        $notifUrl = $notifLookup
                            ? url('/my-documents') . '?' . http_build_query(['search' => $notifLookup])
                            : url('/my-documents');
                        $notifIcon = match ($nDoc->status) {
                            'completed' => 'fa-check-circle',
                            'for_pickup' => 'fa-box-open',
                            'returned' => 'fa-undo-alt',
                            'cancelled' => 'fa-ban',
                            'archived' => 'fa-archive',
                            'received', 'in_review', 'on_hold' => 'fa-spinner',
                            default => 'fa-paper-plane',
                        };
                    @endphp
                    <a class="submission-notif-item" href="{{ $notifUrl }}">
                        <span class="submission-notif-icon {{ $notifStatus }}">
                            <i class="fas {{ $notifIcon }}"></i>
                        </span>
                        <span class="submission-notif-body">
                            <span class="submission-notif-top">
                                <span class="submission-notif-ref" title="{{ $notifLookup ?: 'N/A' }}">{{ $notifLookup ?: 'N/A' }}</span>
                                <span class="submission-notif-status {{ $notifStatus }}">{{ $nDoc->statusLabel() }}</span>
                            </span>
                            <span class="submission-notif-subject" title="{{ $nDoc->subject }}">{{ $nDoc->subject }}</span>
                            <span class="submission-notif-meta">
                                <span title="{{ $notifOfficeName }}">{{ $notifOfficeName }}</span>
                                <span>{{ optional($nDoc->last_action_at ?? $nDoc->updated_at ?? $nDoc->created_at)->format('M d, Y') }}</span>
                            </span>
                            <span class="submission-notif-view">View document</span>
                        </span>
                    </a>
                @endforeach
            </div>
            @if($submissionNotificationCount > $submissionNotificationDocs->count())
                <div class="submission-notif-note">Showing latest {{ min($submissionNotificationLimit, $submissionNotificationDocs->count()) }} notices</div>
            @endif
            <a href="/my-documents" class="submission-notif-footer">View my documents</a>
        @else
            <div class="submission-notif-empty">
                <i class="fas fa-bell-slash"></i>
                No pickup or return notices
            </div>
        @endif
    </div>
</div>
