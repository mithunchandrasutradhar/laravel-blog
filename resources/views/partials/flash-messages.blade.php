@if(session()->hasAny(['success', 'error', 'warning', 'info']))
<div class="flash-messages-container container mt-3" id="flashMessages">
    @foreach(['success', 'error', 'warning', 'info'] as $type)
        @if(session($type))
        <div class="alert alert-{{ $type === 'error' ? 'danger' : $type }} alert-dismissible fade show d-flex align-items-start gap-2 shadow-sm border-0" role="alert">
            <i class="fas fa-{{ $type === 'success' ? 'check-circle' : ($type === 'error' ? 'times-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle')) }} mt-1 flex-shrink-0"></i>
            <div class="flex-grow-1">{{ session($type) }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
    @endforeach

    {{-- Validation errors summary --}}
    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-start gap-2 shadow-sm border-0" role="alert">
        <i class="fas fa-exclamation-circle mt-1 flex-shrink-0"></i>
        <div class="flex-grow-1">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-1 ps-3">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
</div>
@endif
