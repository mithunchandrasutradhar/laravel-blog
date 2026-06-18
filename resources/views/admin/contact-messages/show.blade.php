@extends('admin.layouts.admin')

@section('title', 'Contact Message — ' . $contactMessage->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('admin.contact-messages.index') }}" class="text-decoration-none">Contact Messages</a>
    </li>
    <li class="breadcrumb-item active">View Message</li>
@endsection

@section('page-title', 'Contact Message')
@section('page-subtitle', 'Submitted ' . $contactMessage->created_at->diffForHumans())

@section('content')

<div class="row g-4">

    {{-- Main message --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex align-items-center justify-content-between py-3">
                <h6 class="fw-bold mb-0">
                    <i class="fas fa-envelope-open-text text-primary me-2"></i>{{ $contactMessage->subject }}
                </h6>
                <span class="badge {{ $contactMessage->is_read ? 'bg-secondary' : 'bg-danger' }} bg-opacity-10 {{ $contactMessage->is_read ? 'text-secondary' : 'text-danger' }}">
                    {{ $contactMessage->is_read ? 'Read' : 'Unread' }}
                </span>
            </div>
            <div class="card-body">
                <div class="p-3 bg-light rounded mb-4" style="white-space:pre-wrap;line-height:1.8;font-size:.95rem;">{{ $contactMessage->message }}</div>

                <div class="d-flex align-items-center gap-2 mt-3">
                    <a href="mailto:{{ $contactMessage->email }}?subject=Re: {{ urlencode($contactMessage->subject) }}"
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-reply me-1"></i>Reply via Email
                    </a>
                    <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                    @if(auth()->user()->hasPermissionTo('contact_messages.delete'))
                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}"
                          class="ms-auto" onsubmit="return confirm('Delete this message permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar info --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h6 class="fw-bold mb-0"><i class="fas fa-user text-muted me-2"></i>Sender Info</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Name</dt>
                    <dd class="col-7 fw-semibold mb-2">{{ $contactMessage->name }}</dd>

                    <dt class="col-5 text-muted fw-normal">Email</dt>
                    <dd class="col-7 mb-2">
                        <a href="mailto:{{ $contactMessage->email }}" class="text-decoration-none">
                            {{ $contactMessage->email }}
                        </a>
                    </dd>

                    <dt class="col-5 text-muted fw-normal">Received</dt>
                    <dd class="col-7 mb-2">{{ $contactMessage->created_at->format('M j, Y') }}<br>
                        <span class="text-muted">{{ $contactMessage->created_at->format('g:i A') }}</span>
                    </dd>

                    @if($contactMessage->ip_address)
                    <dt class="col-5 text-muted fw-normal">IP Address</dt>
                    <dd class="col-7 mb-2">{{ $contactMessage->ip_address }}</dd>
                    @endif

                    @if($contactMessage->read_at)
                    <dt class="col-5 text-muted fw-normal">Read at</dt>
                    <dd class="col-7 mb-0">{{ $contactMessage->read_at->format('M j, Y g:i A') }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

</div>

@endsection
