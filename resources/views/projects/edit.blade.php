@extends('layouts.dashboard')

@section('title', 'Edit Project')

@section('content')

<div class="container-fluid">

    <div class="row">

        <div class="col-12">

            <div class="card shadow-sm border-0 rounded-4">

                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit Project: {{ $project->name }}</h4>
                        <small class="text-muted">Modify project details, client information, team assignments, and attachments.</small>
                    </div>
                    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Project Details
                    </a>
                </div>

                <div class="card-body p-4">

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
                            <i class="fa-solid fa-circle-exclamation fs-5"></i>
                            <div>{{ session('error') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <!-- Section 1: Basic & Client Details -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small" style="letter-spacing: 0.5px;">
                            <i class="fa-solid fa-circle-info text-primary me-1.5"></i> Project & Client Information
                        </h6>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Project Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control shadow-2xs" placeholder="e.g. Apollo Lander App" value="{{ old('name', $project->name) }}" required>
                                @error('name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-user-tie text-secondary"></i>
                                    <span>Project Owner / Client Name</span>
                                </label>
                                <input type="text" name="client_name" class="form-control shadow-2xs" placeholder="e.g. Acme Corp / John Smith" value="{{ old('client_name', $project->client_name) }}">
                                @error('client_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold d-flex align-items-center gap-1.5">
                                    <i class="fa-solid fa-globe text-secondary"></i>
                                    <span>Owner / Client Country</span>
                                </label>
                                <input type="text" name="client_country" list="countryList" class="form-control shadow-2xs" placeholder="e.g. United States, India, UK..." value="{{ old('client_country', $project->client_country) }}">
                                <datalist id="countryList">
                                    <option value="United States">
                                    <option value="India">
                                    <option value="United Kingdom">
                                    <option value="Canada">
                                    <option value="Australia">
                                    <option value="Germany">
                                    <option value="France">
                                    <option value="United Arab Emirates">
                                    <option value="Singapore">
                                    <option value="Saudi Arabia">
                                    <option value="Netherlands">
                                </datalist>
                                @error('client_country')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 2: Timeline & Status -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select shadow-2xs">
                                    <option value="planning" {{ old('status', $project->status) === 'planning' ? 'selected' : '' }}>🔵 Planning</option>
                                    <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>🟢 Active</option>
                                    <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>⚪ Completed</option>
                                    <option value="suspended" {{ old('status', $project->status) === 'suspended' ? 'selected' : '' }}>🟡 Suspended</option>
                                </select>
                                @error('status')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Start Date</label>
                                <input type="date" name="start_date" class="form-control shadow-2xs" value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}">
                                @error('start_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Target End Date</label>
                                <input type="date" name="end_date" class="form-control shadow-2xs" value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}">
                                @error('end_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Section 3: Team Assignment & Lead Designation -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small" style="letter-spacing: 0.5px;">
                            <i class="fa-solid fa-users text-primary me-1.5"></i> Team Assignment & Project Lead
                        </h6>

                        <!-- Hidden input for selected team lead ID -->
                        <input type="hidden" name="team_lead_id" id="team_lead_id" value="{{ old('team_lead_id', $project->team_lead_id) }}">

                        <div class="mb-4 position-relative" id="team-members-wrapper">
                            <label class="form-label fw-bold d-flex justify-content-between align-items-center mb-1">
                                <span>Assign Team Members & Choose Lead</span>
                                <span class="badge bg-light text-secondary border fw-normal" id="selected-count-badge">0 selected</span>
                            </label>

                            <!-- Dropdown Trigger Box -->
                            <div class="form-control d-flex align-items-center justify-content-between p-2 cursor-pointer shadow-2xs" id="dropdownTrigger" style="min-height: 46px; cursor: pointer;" onclick="toggleMembersDropdown(event)">
                                <div class="d-flex flex-wrap align-items-center gap-1.5 flex-grow-1" id="selectedTagsContainer">
                                    <span class="text-muted small ps-1" id="placeholderText">Click to select team members and assign project lead...</span>
                                </div>
                                <div class="d-flex align-items-center gap-2 ps-2">
                                    <button type="button" class="btn btn-link btn-sm text-muted p-0 text-decoration-none d-none" id="clearAllBtn" onclick="clearAllMembers(event)" title="Clear all selections">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                    <i class="fa-solid fa-chevron-down text-muted transition-transform" id="dropdownChevron" style="font-size: 0.8rem;"></i>
                                </div>
                            </div>

                            <!-- Floating Searchable Dropdown Menu -->
                            <div class="card shadow-lg border rounded-3 position-absolute start-0 end-0 mt-1 d-none" id="membersDropdownMenu" style="z-index: 1060; max-width: 100%;">
                                <!-- Search Header -->
                                <div class="p-2 border-bottom bg-light rounded-top-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                                        <input type="text" class="form-control border-start-0" id="memberSearchInput" placeholder="Search employees by name, department, designation..." oninput="filterMembersList()" autocomplete="off">
                                        <button class="btn btn-outline-secondary" type="button" onclick="document.getElementById('memberSearchInput').value=''; filterMembersList();" title="Clear search">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-2 px-1">
                                        <small class="text-muted" id="dropdownSummaryText">Showing {{ $users->count() }} employees</small>
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-primary" style="font-size: 0.75rem;" onclick="selectAllMembers(true)">Select All</button>
                                            <span class="text-muted" style="font-size: 0.75rem;">|</span>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-danger" style="font-size: 0.75rem;" onclick="selectAllMembers(false)">Deselect All</button>
                                            <span class="text-muted" style="font-size: 0.75rem;">|</span>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none text-warning fw-semibold" style="font-size: 0.75rem;" onclick="setTeamLead(null, event)" title="Remove designated team lead">Remove Lead</button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Member Items List -->
                                <div class="p-1" style="max-height: 260px; overflow-y: auto;" id="membersListContainer">
                                    @forelse($users as $user)
                                        @php
                                            $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name ?? '', 0, 1));
                                            $deptName = $user->department?->name ?? 'No Department';
                                            $roleName = $user->role?->name ?? 'Employee';
                                            $designation = $user->designation ?? $roleName;
                                            $isSelected = in_array($user->id, old('user_ids', $assignedUserIds));
                                            $currentLead = old('team_lead_id', $project->team_lead_id);
                                            $isLead = ($currentLead == $user->id);
                                        @endphp
                                        <div class="member-item d-flex align-items-center justify-content-between p-2 rounded-2 cursor-pointer transition-colors user-select-none {{ $isLead ? 'bg-warning-subtle' : ($isSelected ? 'bg-primary-subtle' : '') }}" 
                                             data-user-id="{{ $user->id }}" 
                                             data-name="{{ strtolower($user->full_name) }}" 
                                             data-dept="{{ strtolower($deptName) }}" 
                                             data-designation="{{ strtolower($designation) }}"
                                             onclick="toggleUserSelection('{{ $user->id }}', event)">
                                            
                                            <!-- Checkbox and User Info -->
                                            <div class="d-flex align-items-center gap-2.5 flex-grow-1 overflow-hidden">
                                                <div class="form-check m-0 pointer-events-none">
                                                    <input class="form-check-input member-checkbox" type="checkbox" name="user_ids[]" value="{{ $user->id }}" id="user_{{ $user->id }}" {{ ($isSelected || $isLead) ? 'checked' : '' }} onchange="syncTagView()">
                                                </div>
                                                <div class="rounded-circle bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                                    {{ $initials }}
                                                </div>
                                                <div class="text-truncate">
                                                    <div class="fw-semibold text-dark text-sm user-display-name text-truncate" style="font-size: 0.88rem;">{{ $user->full_name }}</div>
                                                    <div class="text-muted text-xs text-truncate" style="font-size: 0.75rem;">
                                                        <span>{{ $designation }}</span> &bull; <span class="badge bg-light text-secondary border px-1.5 py-0.5">{{ $deptName }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Assign as Lead Action Button -->
                                            <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2" onclick="event.stopPropagation()">
                                                <button type="button" 
                                                        class="btn btn-sm btn-lead-toggle rounded-pill px-2.5 py-1 fw-bold d-flex align-items-center gap-1 {{ $isLead ? 'btn-warning text-dark shadow-2xs' : 'btn-outline-secondary' }}" 
                                                        style="font-size: 0.72rem;" 
                                                        id="lead_btn_{{ $user->id }}"
                                                        onclick="setTeamLead('{{ $user->id }}', event)" 
                                                        title="{{ $isLead ? 'Current Team Lead (Click to remove)' : 'Assign this member as Team Lead' }}">
                                                    <i class="fa-solid fa-crown {{ $isLead ? 'text-dark' : 'text-warning' }}"></i>
                                                    <span class="lead-btn-text">{{ $isLead ? 'Team Lead' : 'Make Lead' }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="p-3 text-center text-muted small">
                                            <i class="fa-solid fa-users-slash d-block mb-1 fs-5 text-secondary"></i>
                                            No eligible employees found for assignment.
                                        </div>
                                    @endforelse
                                    <div class="p-3 text-center text-muted small d-none" id="noResultsFound">
                                        <i class="fa-solid fa-magnifying-glass d-block mb-1 fs-5 text-secondary"></i>
                                        No matching employees found.
                                    </div>
                                </div>
                            </div>

                            @error('user_ids')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                            @error('team_lead_id')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Section 4: Project Documents Upload & Existing Attachments -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small" style="letter-spacing: 0.5px;">
                            <i class="fa-solid fa-paperclip text-primary me-1.5"></i> Project Documents & Attachments
                        </h6>

                        <div class="mb-4">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50 mb-3">
                                <label class="form-label fw-bold d-flex align-items-center gap-1.5 mb-1" for="documents">
                                    <i class="fa-solid fa-cloud-arrow-up text-primary"></i>
                                    <span>Upload Additional Files / Specifications</span>
                                    <small class="text-muted fw-normal">(Multiple files supported, max 20MB each)</small>
                                </label>
                                <input type="file" name="documents[]" id="documents" class="form-control shadow-2xs" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.zip,.rar,.png,.jpg,.jpeg,.txt">
                                <div class="form-text text-muted small mt-1">
                                    Upload new project briefs, specifications, contracts, or screenshots.
                                </div>
                            </div>

                            <!-- Existing Files list if any -->
                            @if($project->files && $project->files->count() > 0)
                                <div class="p-3 border rounded-3 bg-white">
                                    <div class="fw-bold small text-dark mb-2 d-flex align-items-center justify-content-between">
                                        <span><i class="fa-solid fa-folder-open text-warning me-1"></i> Already Attached Files ({{ $project->files->count() }})</span>
                                        <a href="{{ route('projects.show', $project->id) }}" class="small text-decoration-none">Manage in Project Page &rarr;</a>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($project->files as $file)
                                            <span class="badge bg-light text-dark border p-2 rounded-3 d-inline-flex align-items-center gap-1.5 font-monospace" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-file text-secondary"></i>
                                                {{ Str::limit($file->name, 25) }}
                                                <span class="text-muted">({{ number_format($file->file_size / 1024, 1) }} KB)</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @error('documents')
                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Section 5: Description (Neeche/Bottom) -->
                        <h6 class="text-uppercase text-muted fw-bold mb-3 small" style="letter-spacing: 0.5px;">
                            <i class="fa-solid fa-align-left text-primary me-1.5"></i> Project Description & Notes
                        </h6>

                        <div class="mb-4">
                            <textarea name="description" rows="5" class="form-control shadow-2xs" placeholder="Provide full scope, objectives, technical stack, or project milestones...">{{ old('description', $project->description) }}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-light border rounded-pill px-4">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Save Changes
                            </button>
                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection

@push('styles')
<style>
    .member-item:hover {
        background-color: var(--bs-light-bg-subtle, rgba(0, 0, 0, 0.04));
    }
    .cursor-pointer {
        cursor: pointer;
    }
    .pointer-events-none {
        pointer-events: none;
    }
    .user-select-none {
        user-select: none;
    }
    .member-tag {
        font-size: 0.78rem;
        transition: all 0.15s ease;
    }
    .member-tag .btn-close-tag {
        font-size: 0.65rem;
        cursor: pointer;
        opacity: 0.7;
    }
    .member-tag .btn-close-tag:hover {
        opacity: 1;
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleMembersDropdown(e) {
        if (e.target.closest('.member-tag') || e.target.closest('#clearAllBtn')) {
            return;
        }
        const menu = document.getElementById('membersDropdownMenu');
        const chevron = document.getElementById('dropdownChevron');
        const isOpen = !menu.classList.contains('d-none');
        
        if (isOpen) {
            closeMembersDropdown();
        } else {
            menu.classList.remove('d-none');
            chevron.style.transform = 'rotate(180deg)';
            setTimeout(() => {
                document.getElementById('memberSearchInput').focus();
            }, 50);
        }
    }

    function closeMembersDropdown() {
        const menu = document.getElementById('membersDropdownMenu');
        const chevron = document.getElementById('dropdownChevron');
        if (menu) menu.classList.add('d-none');
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }

    // Close on click outside
    document.addEventListener('click', function(e) {
        const wrapper = document.getElementById('team-members-wrapper');
        if (wrapper && !wrapper.contains(e.target)) {
            closeMembersDropdown();
        }
    });

    function toggleUserSelection(userId, e) {
        const checkbox = document.getElementById('user_' + userId);
        if (!checkbox) return;
        
        checkbox.checked = !checkbox.checked;
        
        // If unchecking someone who is the lead, remove lead
        const leadInput = document.getElementById('team_lead_id');
        if (!checkbox.checked && leadInput.value == userId) {
            leadInput.value = '';
        }

        syncTagView();
    }

    function setTeamLead(userId, e) {
        if (e) e.stopPropagation();
        const leadInput = document.getElementById('team_lead_id');
        
        if (leadInput.value == userId || !userId) {
            // Unset lead
            leadInput.value = '';
        } else {
            leadInput.value = userId;
            // Auto check this user as a team member
            const cb = document.getElementById('user_' + userId);
            if (cb) cb.checked = true;
        }

        syncTagView();
    }

    function removeUserTag(userId, e) {
        if (e) e.stopPropagation();
        const checkbox = document.getElementById('user_' + userId);
        if (checkbox) {
            checkbox.checked = false;
        }
        const leadInput = document.getElementById('team_lead_id');
        if (leadInput.value == userId) {
            leadInput.value = '';
        }
        syncTagView();
    }

    function selectAllMembers(status) {
        const items = document.querySelectorAll('.member-item');
        items.forEach(item => {
            if (item.style.display !== 'none') {
                const cb = item.querySelector('.member-checkbox');
                if (cb) cb.checked = status;
            }
        });
        if (!status) {
            document.getElementById('team_lead_id').value = '';
        }
        syncTagView();
    }

    function clearAllMembers(e) {
        if (e) e.stopPropagation();
        const checkboxes = document.querySelectorAll('.member-checkbox');
        checkboxes.forEach(cb => cb.checked = false);
        document.getElementById('team_lead_id').value = '';
        syncTagView();
    }

    function filterMembersList() {
        const searchVal = document.getElementById('memberSearchInput').value.trim().toLowerCase();
        const items = document.querySelectorAll('.member-item');
        let visibleCount = 0;

        items.forEach(item => {
            const name = item.getAttribute('data-name') || '';
            const dept = item.getAttribute('data-dept') || '';
            const desig = item.getAttribute('data-designation') || '';

            if (name.includes(searchVal) || dept.includes(searchVal) || desig.includes(searchVal)) {
                item.style.display = 'flex';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noResultsFound');
        const summaryText = document.getElementById('dropdownSummaryText');
        
        if (noResults) {
            if (visibleCount === 0 && items.length > 0) {
                noResults.classList.remove('d-none');
            } else {
                noResults.classList.add('d-none');
            }
        }
        if (summaryText) {
            summaryText.textContent = `Showing ${visibleCount} employees`;
        }
    }

    function syncTagView() {
        const container = document.getElementById('selectedTagsContainer');
        const placeholder = document.getElementById('placeholderText');
        const badge = document.getElementById('selected-count-badge');
        const clearBtn = document.getElementById('clearAllBtn');
        const items = document.querySelectorAll('.member-item');
        const leadInput = document.getElementById('team_lead_id');
        const currentLeadId = leadInput ? leadInput.value : null;

        // Remove existing tags
        const existingTags = container.querySelectorAll('.member-tag');
        existingTags.forEach(tag => tag.remove());

        let selectedCount = 0;

        items.forEach(item => {
            const cb = item.querySelector('.member-checkbox');
            const userId = item.getAttribute('data-user-id');
            const userName = item.querySelector('.user-display-name')?.textContent || 'User';
            const isLead = (currentLeadId && currentLeadId == userId);
            const leadBtn = item.querySelector('.btn-lead-toggle');
            const leadBtnText = item.querySelector('.lead-btn-text');

            // Update row styles and lead button
            if (isLead) {
                item.className = 'member-item d-flex align-items-center justify-content-between p-2 rounded-2 cursor-pointer transition-colors user-select-none bg-warning-subtle';
                if (leadBtn) {
                    leadBtn.className = 'btn btn-sm btn-lead-toggle rounded-pill px-2.5 py-1 fw-bold d-flex align-items-center gap-1 btn-warning text-dark shadow-2xs';
                    if (leadBtnText) leadBtnText.textContent = 'Team Lead';
                }
            } else if (cb && cb.checked) {
                item.className = 'member-item d-flex align-items-center justify-content-between p-2 rounded-2 cursor-pointer transition-colors user-select-none bg-primary-subtle';
                if (leadBtn) {
                    leadBtn.className = 'btn btn-sm btn-lead-toggle rounded-pill px-2.5 py-1 fw-bold d-flex align-items-center gap-1 btn-outline-secondary';
                    if (leadBtnText) leadBtnText.textContent = 'Make Lead';
                }
            } else {
                item.className = 'member-item d-flex align-items-center justify-content-between p-2 rounded-2 cursor-pointer transition-colors user-select-none';
                if (leadBtn) {
                    leadBtn.className = 'btn btn-sm btn-lead-toggle rounded-pill px-2.5 py-1 fw-bold d-flex align-items-center gap-1 btn-outline-secondary';
                    if (leadBtnText) leadBtnText.textContent = 'Make Lead';
                }
            }

            if (cb && cb.checked) {
                selectedCount++;

                // Create tag in trigger box
                const tag = document.createElement('span');
                if (isLead) {
                    tag.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle shadow-2xs rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 member-tag';
                    tag.innerHTML = `
                        <i class="fa-solid fa-crown text-warning" style="font-size: 0.75rem;"></i>
                        <span class="fw-bold">${userName}</span>
                        <span class="badge bg-warning text-dark px-1.5 py-0.5 rounded-pill" style="font-size: 0.62rem;">Lead</span>
                        <i class="fa-solid fa-xmark text-muted btn-close-tag ms-1" onclick="removeUserTag('${userId}', event)"></i>
                    `;
                } else {
                    tag.className = 'badge bg-white text-dark border shadow-2xs rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 member-tag';
                    tag.innerHTML = `
                        <i class="fa-solid fa-user-check text-primary" style="font-size: 0.7rem;"></i>
                        <span class="fw-semibold">${userName}</span>
                        <i class="fa-solid fa-xmark text-muted btn-close-tag ms-1" onclick="removeUserTag('${userId}', event)"></i>
                    `;
                }
                container.appendChild(tag);
            }
        });

        if (selectedCount > 0) {
            placeholder.classList.add('d-none');
            clearBtn.classList.remove('d-none');
            badge.textContent = `${selectedCount} selected`;
            badge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold';
        } else {
            placeholder.classList.remove('d-none');
            clearBtn.classList.add('d-none');
            badge.textContent = '0 selected';
            badge.className = 'badge bg-light text-secondary border fw-normal';
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        syncTagView();
    });
</script>
@endpush
