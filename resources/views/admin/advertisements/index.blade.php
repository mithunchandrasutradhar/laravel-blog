@extends('admin.layouts.admin')

@section('title', 'Advertisements')

@section('breadcrumb')
    <li class="breadcrumb-item active">Advertisements</li>
@endsection

@section('page-title', 'Advertisements')
@section('page-subtitle', 'Manage ad placements and campaigns')

@section('page-actions')
    <a href="{{ route('admin.advertisements.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add Advertisement
    </a>
@endsection

@section('content')

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0">All Advertisements</h6>
        <span class="text-muted small">{{ $advertisements->total() ?? count($advertisements ?? []) }} total</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="60" class="ps-3">ID</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Position</th>
                        <th width="90">Status</th>
                        <th width="110">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advertisements ?? [] as $ad)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $ad->id }}</td>
                        <td class="fw-semibold">{{ $ad->name }}</td>
                        <td>
                            @if($ad->type === 'adsense')
                                <span class="badge bg-warning bg-opacity-10 text-warning">
                                    <i class="fab fa-google me-1"></i>AdSense
                                </span>
                            @else
                                <span class="badge bg-info bg-opacity-10 text-info">
                                    <i class="fas fa-image me-1"></i>Banner
                                </span>
                            @endif
                        </td>
                        <td>
                            @php
                                $positionLabels = [
                                    'header'     => ['label' => 'Header',     'icon' => 'fa-arrow-up'],
                                    'sidebar'    => ['label' => 'Sidebar',    'icon' => 'fa-columns'],
                                    'in-article' => ['label' => 'In-Article', 'icon' => 'fa-newspaper'],
                                    'footer'     => ['label' => 'Footer',     'icon' => 'fa-arrow-down'],
                                ];
                                $pos = $positionLabels[$ad->position] ?? ['label' => ucfirst($ad->position), 'icon' => 'fa-ad'];
                            @endphp
                            <span class="small text-muted">
                                <i class="fas {{ $pos['icon'] }} me-1"></i>{{ $pos['label'] }}
                            </span>
                        </td>
                        <td>
                            {{-- Toggle active status --}}
                            <form method="POST" action="{{ route('admin.advertisements.toggle', $ad->id) }}" class="d-inline">
                                @csrf
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox"
                                           {{ $ad->is_active ? 'checked' : '' }}
                                           onchange="this.closest('form').submit()"
                                           title="{{ $ad->is_active ? 'Active — click to deactivate' : 'Inactive — click to activate' }}">
                                </div>
                            </form>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.advertisements.edit', $ad->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('admin.advertisements.destroy', $ad->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-confirm-delete
                                            data-confirm-title="Delete advertisement?"
                                            data-confirm-text="This will permanently remove the ad placement."
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-ad fa-2x mb-2 d-block"></i>
                            No advertisements found.
                            <a href="{{ route('admin.advertisements.create') }}">Create one?</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($advertisements) && method_exists($advertisements, 'hasPages') && $advertisements->hasPages())
    <div class="card-footer bg-transparent border-0 py-3">
        {{ $advertisements->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection
