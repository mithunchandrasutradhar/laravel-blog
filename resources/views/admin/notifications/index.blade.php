@extends('admin.layouts.admin')

@section('title', 'Notifications')

@section('breadcrumb')
    <li class="breadcrumb-item active">Notifications</li>
@endsection

@section('page-title', 'Notifications')
@section('page-subtitle', 'Your recent activity and alerts')

@section('page-actions')
    @if(auth()->user()->unreadNotifications()->count())
    <form method="POST" action="{{ route('admin.notifications.mark-all-read') }}">
        @csrf
        <button type="submit" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-check-double me-1"></i>Mark all read
        </button>
    </form>
    @endif
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        @forelse($notifications as $notif)
        @php
            $d    = $notif->data;
            $type = $d['type'] ?? '';
            $icon = match($type) {
                'new_comment'             => ['icon' => 'fa-comment-dots', 'color' => 'text-primary',   'bg' => '#eff6ff'],
                'contact_form_submission' => ['icon' => 'fa-envelope',     'color' => 'text-info',      'bg' => '#f0fdfa'],
                default                   => ['icon' => 'fa-bell',          'color' => 'text-secondary', 'bg' => '#f9fafb'],
            };
            $text = match($type) {
                'new_comment'             => ($d['commenter_name'] ?? 'Someone') . ' commented on "' . ($d['post_title'] ?? 'a post') . '"',
                'contact_form_submission' => 'New message from ' . ($d['from_name'] ?? 'someone') . ': ' . ($d['subject'] ?? ''),
                default                   => $d['message'] ?? 'Notification',
            };
            $detail = match($type) {
                'new_comment'             => $d['excerpt'] ?? '',
                'contact_form_submission' => $d['excerpt'] ?? '',
                default                   => '',
            };
            $url = match($type) {
                'new_comment'             => route('admin.comments.index'),
                'contact_form_submission' => isset($d['message_id']) ? route('admin.contact-messages.show', $d['message_id']) : '#',
                default                   => '#',
            };
        @endphp
        <div class="d-flex align-items-start gap-3 px-4 py-3 border-bottom {{ $notif->read_at ? '' : 'bg-light' }}" style="{{ $notif->read_at ? '' : 'border-left:3px solid #0d6efd!important;' }}">
            <div class="flex-shrink-0 rounded-circle d-flex align-items-center justify-content-center mt-1"
                 style="width:38px;height:38px;background:{{ $icon['bg'] }};">
                <i class="fas {{ $icon['icon'] }} {{ $icon['color'] }}"></i>
            </div>
            <div class="flex-grow-1">
                <div class="d-flex align-items-start justify-content-between gap-2">
                    <div>
                        <div class="small fw-{{ $notif->read_at ? 'normal' : 'semibold' }}">{{ $text }}</div>
                        @if($detail)
                        <div class="text-muted mt-1" style="font-size:.8rem;">"{{ $detail }}"</div>
                        @endif
                        <div class="text-muted mt-1" style="font-size:.75rem;">{{ $notif->created_at->diffForHumans() }}</div>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-shrink-0">
                        @if(!$notif->read_at)
                        <span class="badge bg-primary" style="font-size:.65rem;">New</span>
                        @endif
                        <a href="{{ $url }}" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size:.75rem;">View</a>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-5 text-muted">
            <i class="fas fa-bell-slash d-block mb-3 fs-2 opacity-50"></i>
            <div class="fw-semibold">No notifications yet</div>
            <div class="small mt-1">You'll see new comments and messages here.</div>
        </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

@endsection
