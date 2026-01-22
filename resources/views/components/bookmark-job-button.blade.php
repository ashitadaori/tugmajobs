@props(['job', 'size' => 'sm', 'showText' => false])

@php
    $isBookmarked = auth()->check() && auth()->user()->role === 'jobseeker' ? $job->isBookmarkedByUser(auth()->id()) : false;
    $buttonClass = $isBookmarked ? 'btn-success' : 'btn-outline-primary';
    $icon = $isBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark';
    $text = $isBookmarked ? 'Bookmarked ✓' : 'Bookmark';
@endphp

@auth
    @if(auth()->user()->role === 'jobseeker')
        <button type="button"
                class="btn {{ $buttonClass }} btn-{{ $size }} bookmark-job-btn {{ $attributes->get('class') }}"
                data-job-id="{{ $job->id }}"
                data-bookmarked="{{ $isBookmarked ? 'true' : 'false' }}"
                aria-label="{{ $text }}">
            <i class="{{ $icon }}{{ $showText ? ' me-1' : '' }}"></i>
            @if($showText)
                <span class="bookmark-text">{{ $text }}</span>
            @endif
        </button>
    @endif
@endauth

@guest
    <button type="button"
            class="btn btn-outline-secondary btn-{{ $size }} {{ $attributes->get('class') }}"
            onclick="showLoginPrompt()"
            aria-label="Login to bookmark jobs">
        <i class="far fa-bookmark{{ $showText ? ' me-1' : '' }}"></i>
        @if($showText)
            <span>Bookmark</span>
        @endif
    </button>
@endguest

@push('styles')
<style>
.bookmark-job-toast {
    transition: all 0.3s ease-in-out !important;
    transform: translateX(0) !important;
}

.bookmark-job-toast.removing {
    opacity: 0 !important;
    transform: translateX(100%) !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bookmark job functionality with debounce
    let isProcessing = false;

    document.querySelectorAll('.bookmark-job-btn').forEach(button => {
        button.addEventListener('click', function() {
            // Prevent multiple rapid clicks
            if (isProcessing || this.disabled) {
                console.log('Click ignored - already processing');
                return;
            }

            const jobId = this.getAttribute('data-job-id');
            const isBookmarked = this.getAttribute('data-bookmarked') === 'true';

            // If job is already bookmarked, ask for confirmation to remove
            if (isBookmarked) {
                if (!confirm('Remove this job from your bookmarks?')) {
                    return; // User cancelled
                }
            }

            // Set processing flag
            isProcessing = true;

            // Show loading state
            const originalContent = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>' + ({{ $showText ? 'true' : 'false' }} ? '<span>Processing...</span>' : '');
            this.disabled = true;

            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found');
                showBookmarkMessage('Security token not found. Please refresh the page.', 'error');
                this.innerHTML = originalContent;
                this.disabled = false;
                return;
            }

            // Make AJAX request
            fetch('{{ route("account.bookmarked-jobs.toggle") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    job_id: jobId
                })
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    // Update button state
                    const newIsBookmarked = data.is_bookmarked;
                    const newButtonClass = newIsBookmarked ? 'btn-success' : 'btn-outline-primary';
                    const newIcon = newIsBookmarked ? 'fas fa-bookmark' : 'far fa-bookmark';
                    const newText = newIsBookmarked ? 'Bookmarked ✓' : 'Bookmark';

                    // Update classes - remove old classes first
                    this.classList.remove('btn-success', 'btn-outline-primary', 'btn-danger', 'btn-outline-danger');
                    if (newIsBookmarked) {
                        this.classList.add('btn-success');
                    } else {
                        this.classList.add('btn-outline-primary');
                    }

                    // Update content
                    this.innerHTML = `<i class="${newIcon} me-1"></i>` + ({{ $showText ? 'true' : 'false' }} ? `<span class="bookmark-text">${newText}</span>` : '');

                    // Update data attribute
                    this.setAttribute('data-bookmarked', newIsBookmarked ? 'true' : 'false');
                    this.setAttribute('aria-label', newText);

                    // Show success message with action
                    const actionMessage = data.action === 'bookmarked' ?
                        '✅ Job bookmarked successfully!' :
                        '🗑️ Job removed from your bookmarks!';
                    showBookmarkMessage(actionMessage, 'success');

                    // Update bookmarked count if element exists
                    const bookmarkedCountElement = document.querySelector('.bookmarked-jobs-count');
                    if (bookmarkedCountElement) {
                        bookmarkedCountElement.textContent = data.bookmarked_count;
                    }

                    console.log(`Job ${data.action}. New state: ${newIsBookmarked ? 'BOOKMARKED' : 'NOT BOOKMARKED'}`);
                } else {
                    showBookmarkMessage(data.message, 'error');
                    this.innerHTML = originalContent;
                }

                // Reset processing state with a small delay to prevent rapid clicking
                setTimeout(() => {
                    this.disabled = false;
                    isProcessing = false;
                }, 500); // 500ms delay
            })
            .catch(error => {
                console.error('Detailed error:', error);
                console.error('Error message:', error.message);
                showBookmarkMessage(`Error: ${error.message}`, 'error');
                this.innerHTML = originalContent;
                this.disabled = false;
                isProcessing = false; // Reset processing state
            });
        });
    });
});

function showLoginPrompt() {
    if (confirm('You need to login as a jobseeker to bookmark jobs. Would you like to login now?')) {
        window.location.href = '{{ route("login") }}';
    }
}

function showBookmarkMessage(message, type) {
    // Remove any existing bookmark messages first
    const existingToasts = document.querySelectorAll('.bookmark-job-toast');
    existingToasts.forEach(toast => {
        toast.remove();
    });

    // Create toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed bookmark-job-toast`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" onclick="this.parentElement.remove()"></button>
    `;

    document.body.appendChild(toast);

    // Auto-dismiss after 2 seconds (shorter time)
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 2000);
}
</script>
@endpush
