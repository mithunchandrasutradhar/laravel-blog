@extends('admin.layouts.admin')

@section('title', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item active">Users</li>
@endsection

@section('page-title', 'Users')
@section('page-subtitle', 'Manage registered users')

@section('page-actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus me-1"></i>Add New User
    </a>
@endsection

@section('content')

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" name="search" class="form-control"
                           placeholder="Name or email..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <option value="admin"  {{ request('role') === 'admin'  ? 'selected' : '' }}>Admin</option>
                    <option value="editor" {{ request('role') === 'editor' ? 'selected' : '' }}>Editor</option>
                    <option value="author" {{ request('role') === 'author' ? 'selected' : '' }}>Author</option>
                    <option value="user"   {{ request('role') === 'user'   ? 'selected' : '' }}>User</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="banned"   {{ request('status') === 'banned'   ? 'selected' : '' }}>Banned</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0">All Users</h6>
        <span class="text-muted small">{{ $users->total() ?? 0 }} users</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="ps-3">ID</th>
                        <th width="50">Avatar</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Posts</th>
                        <th>Joined</th>
                        <th width="110">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users ?? [] as $user)
                    <tr>
                        <td class="ps-3 text-muted small">{{ $user->id }}</td>
                        <td>
                            @if($user->profile_image)
                                <img src="{{ asset('storage/' . $user->profile_image) }}"
                                     alt="{{ $user->name }}" class="rounded-circle"
                                     style="width:36px;height:36px;object-fit:cover;">
                            @else
                                <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold"
                                     style="width:36px;height:36px;font-size:.7rem;">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user->id) }}"
                               class="fw-semibold text-dark text-decoration-none">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td class="small text-muted">{{ $user->email }}</td>
                        <td>
                            @php $userRole = $user->roles->first()?->name ?? 'user'; $roleColors = ['admin'=>'danger','editor'=>'primary','author'=>'info','user'=>'secondary']; @endphp
                            <span class="badge bg-{{ $roleColors[$userRole] ?? 'secondary' }} bg-opacity-10
                                         text-{{ $roleColors[$userRole] ?? 'secondary' }}">
                                {{ ucfirst($userRole) }}
                            </span>
                        </td>
                        <td>
                            @if(($user->status ?? 'active') === 'active')
                                <span class="badge bg-success bg-opacity-10 text-success">Active</span>
                            @elseif($user->status === 'banned')
                                <span class="badge bg-danger bg-opacity-10 text-danger">Banned</span>
                            @else
                                <span class="badge bg-secondary bg-opacity-10 text-secondary">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-10 text-info">{{ $user->posts_count ?? 0 }}</span>
                        </td>
                        <td class="small text-muted text-nowrap">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.users.edit', $user->id) }}"
                                   class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            data-confirm-delete
                                            data-confirm-title="Delete user?"
                                            data-confirm-text="This will permanently delete the user and their data."
                                            title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-users fa-2x mb-2 d-block"></i>
                            No users found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(isset($users) && $users->hasPages())
    <div class="card-footer bg-transparent border-0 d-flex align-items-center justify-content-between py-3">
        <div class="small text-muted">
            Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
        </div>
        {{ $users->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>

@endsection

