{{--
    Recursive Comment Item Partial
    Variables:
      $comment - Comment model
      $depth   - nesting depth (default 0, max 3)
--}}
@php $depth = $depth ?? 0; @endphp

<div class="comment-item {{ $depth > 0 ? 'comment-nested' : '' }}" id="comment-{{ $comment->id }}" data-depth="{{ $depth }}">
    <div class="d-flex gap-3">
        {{-- Avatar --}}
        <div class="comment-avatar flex-shrink-0">
            @if($comment->user?->avatar)
                <img src="{{ asset($comment->user->avatar) }}"
                     alt="{{ $comment->user->name }}"
                     class="rounded-circle"
                     width="{{ $depth === 0 ? 44 : 36 }}"
                     height="{{ $depth === 0 ? 44 : 36 }}"
                     style="object-fit:cover;"
                     loading="lazy">
            @else
                <img src="https://www.gravatar.com/avatar/{{ md5(strtolower(trim($comment->commenter_email ?? 'unknown@example.com'))) }}?s={{ $depth === 0 ? 88 : 72 }}&d=mp"
                     alt="{{ $comment->commenter_name }}"
                     class="rounded-circle"
                     width="{{ $depth === 0 ? 44 : 36 }}"
                     height="{{ $depth === 0 ? 44 : 36 }}"
                     loading="lazy">
            @endif
        </div>

        {{-- Comment Body --}}
        <div class="comment-body flex-grow-1">
            <div class="comment-bubble bg-light rounded-3 p-3">
                {{-- Header --}}
                <div class="d-flex align-items-start justify-content-between gap-2 mb-1 flex-wrap">
                    <div>
                        <span class="fw-semibold text-dark">{{ $comment->commenter_name }}</span>
                        @if($comment->user?->hasRole('admin'))
                            <span class="badge bg-danger ms-1" style="font-size:.65rem;">Admin</span>
                        @elseif($comment->user?->hasRole('editor'))
                            <span class="badge bg-primary ms-1" style="font-size:.65rem;">Editor</span>
                        @endif
                        @if($comment->website)
                        <a href="{{ $comment->website }}" class="text-muted ms-1" target="_blank" rel="noopener noreferrer nofollow" style="font-size:.75rem;">
                            <i class="fas fa-globe"></i>
                        </a>
                        @endif
                    </div>
                    <time class="text-muted" style="font-size:.75rem;" datetime="{{ $comment->created_at->toIso8601String() }}" title="{{ $comment->created_at->format('M d, Y H:i') }}">
                        {{ $comment->created_at->diffForHumans() }}
                    </time>
                </div>

                {{-- Content --}}
                <div class="comment-content">
                    @if($comment->parent_id)
                    <p class="mb-1">
                        <a href="#comment-{{ $comment->parent_id }}" class="text-muted text-decoration-none" style="font-size:.8rem;">
                            <i class="fas fa-reply me-1"></i>
                            @if($comment->relationLoaded('parent') && $comment->parent)
                                <em>{{ $comment->parent->commenter_name }}</em>
                            @endif
                        </a>
                    </p>
                    @endif
                    <p class="mb-0 comment-text">{{ $comment->body }}</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="comment-actions d-flex align-items-center gap-3 mt-1 ms-1">
                @if($depth < 3)
                <button type="button"
                        class="btn btn-link text-muted p-0 btn-sm comment-reply-toggle"
                        data-comment-id="{{ $comment->id }}"
                        data-author="{{ $comment->commenter_name }}"
                        aria-label="Reply to {{ $comment->commenter_name }}">
                    <i class="fas fa-reply me-1"></i><span style="font-size:.8rem;">Reply</span>
                </button>
                @endif

                @if(auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->hasRole('admin')))
                <form action="{{ route('admin.comments.destroy', $comment->id) }}" method="POST" class="d-inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-link text-danger p-0 btn-sm" onclick="return confirm('Delete this comment?')" aria-label="Delete comment">
                        <i class="fas fa-trash me-1"></i><span style="font-size:.8rem;">Delete</span>
                    </button>
                </form>
                @endif
            </div>

            {{-- Inline Reply Form (hidden by default) --}}
            @if($depth < 3)
            <div class="comment-reply-form mt-3 d-none" id="reply-form-{{ $comment->id }}">
                <form action="{{ route('comments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="post_id" value="{{ $comment->post_id }}">
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    @guest
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4">
                            <input type="text" name="name" class="form-control form-control-sm" placeholder="Your name *" required>
                        </div>
                        <div class="col-sm-4">
                            <input type="email" name="email" class="form-control form-control-sm" placeholder="Email *" required>
                        </div>
                        <div class="col-sm-4">
                            <input type="url" name="website" class="form-control form-control-sm" placeholder="Website (optional)">
                        </div>
                    </div>
                    @endguest
                    <div class="d-flex gap-2">
                        <textarea name="body" class="form-control form-control-sm" rows="2" placeholder="Write a reply..." required></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary comment-reply-cancel" data-comment-id="{{ $comment->id }}">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary">Post Reply</button>
                    </div>
                </form>
            </div>
            @endif

            {{-- Nested replies --}}
            @if($comment->replies && $comment->replies->isNotEmpty())
            <div class="comment-replies mt-3">
                @foreach($comment->replies as $reply)
                    @include('partials.comment-item', ['comment' => $reply, 'depth' => $depth + 1])
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>
