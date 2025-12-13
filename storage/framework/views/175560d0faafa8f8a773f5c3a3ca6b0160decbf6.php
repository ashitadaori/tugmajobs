<!-- Global Search Component -->
<div id="globalSearchContainer">
    <!-- Search Trigger Button -->
    <button class="global-search-trigger" id="searchTrigger" title="Search (Ctrl+K)">
        <i class="bi bi-search"></i>
        <span class="d-none d-md-inline ms-2">Search</span>
        <kbd class="search-kbd d-none d-lg-inline">Ctrl K</kbd>
    </button>

    <!-- Search Modal -->
    <div class="global-search-modal" id="searchModal" style="display: none;">
        <div class="search-modal-backdrop" id="searchBackdrop"></div>
        <div class="search-modal-content">
            <div class="search-header">
                <div class="search-input-wrapper">
                    <i class="bi bi-search search-icon"></i>
                    <input type="text"
                           class="search-input"
                           id="globalSearchInput"
                           placeholder="Search users, jobs, companies, applications..."
                           autocomplete="off">
                    <button class="search-close" id="searchCloseBtn">
                        <kbd>Esc</kbd>
                    </button>
                </div>

                <!-- Search Filters -->
                <div class="search-filters">
                    <button class="search-filter active" data-type="all">
                        <i class="bi bi-grid"></i>All
                    </button>
                    <button class="search-filter" data-type="users">
                        <i class="bi bi-people"></i>Users
                    </button>
                    <button class="search-filter" data-type="jobs">
                        <i class="bi bi-briefcase"></i>Jobs
                    </button>
                    <button class="search-filter" data-type="companies">
                        <i class="bi bi-building"></i>Companies
                    </button>
                    <button class="search-filter" data-type="applications">
                        <i class="bi bi-file-text"></i>Applications
                    </button>
                </div>
            </div>

            <div class="search-body" id="searchBody">
                <!-- Recent Searches -->
                <div class="search-section" id="recentSearchesSection">
                    <div class="search-section-header">
                        <span><i class="bi bi-clock-history me-2"></i>Recent Searches</span>
                        <button class="btn-text-sm" onclick="clearRecentSearches()">Clear</button>
                    </div>
                    <div id="recentSearchesList">
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-search" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Start typing to search...</p>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                <div class="search-section" id="searchResultsSection" style="display: none;">
                    <div class="search-section-header">
                        <span id="searchResultsTitle">Search Results</span>
                        <button class="btn-text-sm" onclick="saveCurrentSearch()">
                            <i class="bi bi-bookmark"></i>Save
                        </button>
                    </div>
                    <div id="searchResultsList"></div>
                </div>

                <!-- Saved Presets -->
                <div class="search-section" id="presetsSection">
                    <div class="search-section-header">
                        <span><i class="bi bi-bookmark-star me-2"></i>Saved Searches</span>
                    </div>
                    <div id="presetsList"></div>
                </div>
            </div>

            <div class="search-footer">
                <div class="search-tips">
                    <kbd>↑</kbd><kbd>↓</kbd> Navigate
                    <span class="mx-2">•</span>
                    <kbd>Enter</kbd> Select
                    <span class="mx-2">•</span>
                    <kbd>Esc</kbd> Close
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save Preset Modal -->
<div class="modal fade" id="savePresetModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Save Search Preset</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="savePresetForm">
                    <div class="mb-3">
                        <label class="form-label">Preset Name</label>
                        <input type="text" class="form-control" id="presetName" required maxlength="100">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary btn-sm" onclick="confirmSavePreset()">
                    <i class="bi bi-bookmark-check me-1"></i>Save
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.global-search-trigger {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 0.5rem 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    color: #6b7280;
}

.global-search-trigger:hover {
    border-color: #667eea;
    color: #667eea;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.search-kbd {
    background: #f3f4f6;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-family: 'Courier New', monospace;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.global-search-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 9999;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 100px;
}

.search-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    animation: fadeIn 0.2s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.search-modal-content {
    position: relative;
    width: 90%;
    max-width: 640px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    display: flex;
    flex-direction: column;
    max-height: calc(100vh - 200px);
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.search-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.search-icon {
    position: absolute;
    left: 1rem;
    color: #9ca3af;
    font-size: 1.25rem;
}

.search-input {
    width: 100%;
    padding: 0.75rem 3rem 0.75rem 3rem;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    font-size: 1rem;
    outline: none;
    transition: all 0.2s;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-close {
    position: absolute;
    right: 1rem;
    background: #f3f4f6;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.search-close:hover {
    background: #e5e7eb;
}

.search-filters {
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    padding-bottom: 0.5rem;
}

.search-filter {
    padding: 0.5rem 1rem;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.search-filter:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.search-filter.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.search-body {
    flex: 1;
    overflow-y: auto;
    padding: 1rem 1.5rem;
}

.search-section {
    margin-bottom: 1.5rem;
}

.search-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

.btn-text-sm {
    background: none;
    border: none;
    color: #667eea;
    font-size: 0.875rem;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    transition: all 0.2s;
}

.btn-text-sm:hover {
    color: #5568d3;
    background: #f3f4f6;
    border-radius: 4px;
}

.search-result-item, .recent-search-item {
    padding: 0.75rem;
    border-radius: 8px;
    margin-bottom: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
}

.search-result-item:hover, .recent-search-item:hover {
    background: #f9fafb;
    border-color: #e5e7eb;
}

.search-result-item.active {
    background: #ede9fe;
    border-color: #667eea;
}

.result-icon {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    margin-right: 0.75rem;
}

.icon-user { background: #dbeafe; color: #2563eb; }
.icon-job { background: #d1fae5; color: #10b981; }
.icon-company { background: #fef3c7; color: #f59e0b; }
.icon-application { background: #fce7f3; color: #ec4899; }

.result-title {
    font-weight: 600;
    color: #111827;
    margin-bottom: 0.25rem;
}

.result-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

.result-meta {
    font-size: 0.75rem;
    color: #9ca3af;
}

.result-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
}

.search-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 0 0 16px 16px;
}

.search-tips {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    color: #6b7280;
}

.search-tips kbd {
    background: white;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.7rem;
    font-family: 'Courier New', monospace;
    border: 1px solid #d1d5db;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    margin: 0 0.25rem;
}

.loading-results {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}

.no-results {
    text-align: center;
    padding: 2rem;
    color: #9ca3af;
}

@media (max-width: 768px) {
    .search-modal-content {
        width: 95%;
        margin: 0 auto;
    }

    .search-filters {
        flex-wrap: nowrap;
    }
}
</style>

<script>
let searchTimeout;
let currentSearchType = 'all';
let currentSearchQuery = '';
let searchResults = [];
let selectedResultIndex = -1;

// Initialize Global Search
document.addEventListener('DOMContentLoaded', function() {
    initializeGlobalSearch();
    loadRecentSearches();
    loadSavedPresets();
});

// Keyboard shortcut (Ctrl+K or Cmd+K)
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        openSearchModal();
    }

    // Close on Escape
    if (e.key === 'Escape') {
        closeSearchModal();
    }

    // Navigate results with arrow keys
    if (document.getElementById('searchModal').style.display !== 'none') {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            navigateResults(1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            navigateResults(-1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            selectCurrentResult();
        }
    }
});

function initializeGlobalSearch() {
    const trigger = document.getElementById('searchTrigger');
    const modal = document.getElementById('searchModal');
    const backdrop = document.getElementById('searchBackdrop');
    const closeBtn = document.getElementById('searchCloseBtn');
    const input = document.getElementById('globalSearchInput');

    trigger.addEventListener('click', openSearchModal);
    backdrop.addEventListener('click', closeSearchModal);
    closeBtn.addEventListener('click', closeSearchModal);

    // Search input
    input.addEventListener('input', function(e) {
        const query = e.target.value;
        clearTimeout(searchTimeout);

        if (query.length < 2) {
            showRecentSearches();
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query, currentSearchType);
        }, 300);
    });

    // Filter buttons
    document.querySelectorAll('.search-filter').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.search-filter').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentSearchType = this.dataset.type;

            if (currentSearchQuery) {
                performSearch(currentSearchQuery, currentSearchType);
            }
        });
    });
}

function openSearchModal() {
    document.getElementById('searchModal').style.display = 'flex';
    document.getElementById('globalSearchInput').focus();
    document.body.style.overflow = 'hidden';
}

function closeSearchModal() {
    document.getElementById('searchModal').style.display = 'none';
    document.getElementById('globalSearchInput').value = '';
    document.body.style.overflow = '';
    showRecentSearches();
    selectedResultIndex = -1;
}

function performSearch(query, type) {
    currentSearchQuery = query;

    document.getElementById('recentSearchesSection').style.display = 'none';
    document.getElementById('searchResultsSection').style.display = 'block';
    document.getElementById('searchResultsList').innerHTML = `
        <div class="loading-results">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Searching...</p>
        </div>
    `;

    fetch(`<?php echo e(route('admin.search.global')); ?>?q=${encodeURIComponent(query)}&type=${type}`)
        .then(res => res.json())
        .then(data => {
            searchResults = data.results || [];
            renderSearchResults(data);

            // Save to recent searches
            if (query.length >= 2) {
                saveRecentSearch(query, type);
            }
        })
        .catch(err => {
            document.getElementById('searchResultsList').innerHTML = `
                <div class="no-results">
                    <i class="bi bi-exclamation-circle" style="font-size: 2rem;"></i>
                    <p class="mt-2">Error performing search</p>
                </div>
            `;
        });
}

function renderSearchResults(data) {
    const container = document.getElementById('searchResultsList');
    const results = data.results;

    if (!results || results.length === 0) {
        container.innerHTML = `
            <div class="no-results">
                <i class="bi bi-search" style="font-size: 2rem;"></i>
                <p class="mt-2">No results found for "${data.query}"</p>
            </div>
        `;
        return;
    }

    // Show counts if searching all types
    let countsHtml = '';
    if (data.counts) {
        document.getElementById('searchResultsTitle').innerHTML = `
            Results (${data.total})
            <span class="text-muted small ms-2">
                ${data.counts.users} users, ${data.counts.jobs} jobs,
                ${data.counts.companies} companies, ${data.counts.applications} applications
            </span>
        `;
    }

    container.innerHTML = results.map((result, index) => `
        <div class="search-result-item ${index === 0 ? 'active' : ''}"
             data-index="${index}"
             onclick="selectResult(${index})">
            <div class="d-flex align-items-start">
                <div class="result-icon icon-${result.type}">
                    <i class="${result.icon}"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="result-title">${result.title}</div>
                    <div class="result-subtitle">${result.subtitle}</div>
                    ${result.meta ? `<div class="result-meta mt-1">${result.meta}</div>` : ''}
                </div>
                ${result.badge ? `<span class="result-badge bg-light">${result.badge}</span>` : ''}
            </div>
        </div>
    `).join('');

    selectedResultIndex = 0;
}

function navigateResults(direction) {
    const items = document.querySelectorAll('.search-result-item');
    if (items.length === 0) return;

    items[selectedResultIndex]?.classList.remove('active');

    selectedResultIndex += direction;
    if (selectedResultIndex < 0) selectedResultIndex = items.length - 1;
    if (selectedResultIndex >= items.length) selectedResultIndex = 0;

    items[selectedResultIndex]?.classList.add('active');
    items[selectedResultIndex]?.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
}

function selectCurrentResult() {
    if (selectedResultIndex >= 0 && selectedResultIndex < searchResults.length) {
        selectResult(selectedResultIndex);
    }
}

function selectResult(index) {
    const result = searchResults[index];
    if (result && result.url) {
        window.location.href = result.url;
    }
}

function showRecentSearches() {
    document.getElementById('searchResultsSection').style.display = 'none';
    document.getElementById('recentSearchesSection').style.display = 'block';
}

function loadRecentSearches() {
    fetch('<?php echo e(route('admin.search.get-recent')); ?>')
        .then(res => res.json())
        .then(searches => {
            renderRecentSearches(searches);
        });
}

function renderRecentSearches(searches) {
    const container = document.getElementById('recentSearchesList');

    if (searches.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">No recent searches</p>
            </div>
        `;
        return;
    }

    container.innerHTML = searches.map(search => `
        <div class="recent-search-item" onclick="repeatSearch('${search.query}', '${search.type}')">
            <div class="d-flex align-items-center">
                <i class="bi bi-clock-history me-3 text-muted"></i>
                <div class="flex-grow-1">
                    <div>${search.query}</div>
                    <small class="text-muted">in ${search.type}</small>
                </div>
                <small class="text-muted">${new Date(search.searched_at).toLocaleDateString()}</small>
            </div>
        </div>
    `).join('');
}

function repeatSearch(query, type) {
    document.getElementById('globalSearchInput').value = query;
    currentSearchType = type;

    document.querySelectorAll('.search-filter').forEach(f => f.classList.remove('active'));
    document.querySelector(`.search-filter[data-type="${type}"]`).classList.add('active');

    performSearch(query, type);
}

function saveRecentSearch(query, type) {
    fetch('<?php echo e(route('admin.search.save-recent')); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ query, type })
    });
}

function clearRecentSearches() {
    if (!confirm('Clear all recent searches?')) return;

    fetch('<?php echo e(route('admin.search.clear-recent')); ?>', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(() => {
        loadRecentSearches();
        showAdminToast('Recent searches cleared', 'success');
    });
}

function loadSavedPresets() {
    fetch('<?php echo e(route('admin.search.get-presets')); ?>')
        .then(res => res.json())
        .then(presets => {
            renderPresets(presets);
        });
}

function renderPresets(presets) {
    const container = document.getElementById('presetsList');

    if (presets.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-3">
                <small>No saved presets</small>
            </div>
        `;
        return;
    }

    container.innerHTML = presets.map(preset => `
        <div class="recent-search-item">
            <div class="d-flex align-items-center">
                <i class="bi bi-bookmark-star me-3 text-primary"></i>
                <div class="flex-grow-1">
                    <div><strong>${preset.name}</strong></div>
                </div>
                <button class="btn btn-sm btn-link text-danger" onclick="deletePreset(${preset.id})">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        </div>
    `).join('');
}

function saveCurrentSearch() {
    if (!currentSearchQuery) {
        alert('No active search to save');
        return;
    }

    new bootstrap.Modal(document.getElementById('savePresetModal')).show();
}

function confirmSavePreset() {
    const name = document.getElementById('presetName').value.trim();

    if (!name) {
        alert('Please enter a preset name');
        return;
    }

    const filters = {
        query: currentSearchQuery,
        type: currentSearchType
    };

    fetch('<?php echo e(route('admin.search.save-preset')); ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ name, filters })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast('Search preset saved', 'success');
            bootstrap.Modal.getInstance(document.getElementById('savePresetModal')).hide();
            document.getElementById('presetName').value = '';
            loadSavedPresets();
        }
    });
}

function deletePreset(id) {
    if (!confirm('Delete this preset?')) return;

    fetch(`<?php echo e(route('admin.search.delete-preset', '')); ?>/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showAdminToast('Preset deleted', 'success');
            loadSavedPresets();
        }
    });
}
</script>
<?php /**PATH D:\capstoneeeeeee\Capstone\job-portal-main\resources\views/admin/partials/global-search.blade.php ENDPATH**/ ?>