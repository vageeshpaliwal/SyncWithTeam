@extends('layouts.dashboard')

@section('title', 'Team Chat & Discussions')

@section('content')
<div class="container-fluid px-0 h-100">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden chat-main-wrapper" style="height: calc(100vh - 130px); min-height: 560px;">
        <div class="row g-0 h-100">
            
            <!-- Left Chat Sidebar: Channels & Direct Messages -->
            <div class="col-12 col-md-4 col-lg-3 border-end d-flex flex-column h-100 chat-sidebar-pane" style="background-color: var(--card-bg, #FFFFFF);">
                <!-- Search & Header -->
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold text-dark fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-comments text-primary"></i> Team Chat
                    </h5>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.7rem;">Live</span>
                </div>

                <!-- Navigation List -->
                <div class="overflow-y-auto flex-grow-1 p-2 chat-sidebar-scroll">
                    
                    <!-- 1. Channels Section -->
                    <div class="px-2 pt-2 pb-1 text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        Channels
                    </div>
                    <ul class="nav nav-pills flex-column gap-1 mb-3">
                        <li class="nav-item">
                            <a href="{{ route('chat.index', ['type' => 'general']) }}" 
                               class="nav-link d-flex align-items-center gap-2.5 px-3 py-2 rounded-3 {{ $activeType === 'general' ? 'active shadow-2xs' : 'text-secondary hover-bg-light' }}">
                                <i class="fa-solid fa-hashtag {{ $activeType === 'general' ? 'text-white' : 'text-primary' }}"></i>
                                <span class="fw-semibold small">general</span>
                            </a>
                        </li>
                    </ul>

                    <!-- 2. Project Rooms Section -->
                    <div class="px-2 pt-2 pb-1 text-uppercase text-muted fw-bold d-flex align-items-center justify-content-between" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        <span>Project Rooms</span>
                        <span class="badge bg-light text-secondary rounded-pill font-monospace" style="font-size: 0.65rem;">{{ count($projects) }}</span>
                    </div>
                    <ul class="nav nav-pills flex-column gap-1 mb-3">
                        @forelse($projects as $proj)
                            @php
                                $colorSeed = md5($proj->name);
                                $hue = hexdec(substr($colorSeed, 0, 2)) % 360;
                                $dotColor = "hsl({$hue}, 75%, 45%)";
                                $isProjActive = ($activeType === 'project' && $activeId == $proj->id);
                            @endphp
                            <li class="nav-item">
                                <a href="{{ route('chat.index', ['type' => 'project', 'id' => $proj->id]) }}" 
                                   class="nav-link d-flex align-items-center justify-content-between px-3 py-2 rounded-3 {{ $isProjActive ? 'active shadow-2xs' : 'text-secondary hover-bg-light' }}">
                                    <div class="d-flex align-items-center gap-2.5 text-truncate">
                                        <span class="rounded-circle d-inline-block flex-shrink-0" style="width: 8px; height: 8px; background-color: {{ $isProjActive ? '#FFF' : $dotColor }};"></span>
                                        <span class="fw-semibold small text-truncate">{{ $proj->name }}</span>
                                    </div>
                                    @if($proj->status)
                                        <span class="badge {{ $isProjActive ? 'bg-white text-primary' : 'bg-light text-muted' }} rounded-pill" style="font-size: 0.65rem;">
                                            {{ ucfirst($proj->status) }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="px-3 py-1 text-muted small fst-italic">No projects yet</li>
                        @endforelse
                    </ul>

                    <!-- 3. Direct Messages Section -->
                    <div class="px-2 pt-2 pb-1 text-uppercase text-muted fw-bold d-flex align-items-center justify-content-between" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                        <span>Direct Messages</span>
                        <span class="badge bg-light text-secondary rounded-pill font-monospace" style="font-size: 0.65rem;">{{ count($teamMembers) }}</span>
                    </div>
                    <ul class="nav nav-pills flex-column gap-1">
                        @forelse($teamMembers as $member)
                            @php
                                $isDirectActive = ($activeType === 'direct' && $activeId == $member->id);
                                $unread = $unreadCounts[$member->id] ?? 0;
                            @endphp
                            <li class="nav-item">
                                <a href="{{ route('chat.index', ['type' => 'direct', 'id' => $member->id]) }}" 
                                   class="nav-link d-flex align-items-center justify-content-between px-3 py-2 rounded-3 {{ $isDirectActive ? 'active shadow-2xs' : 'text-secondary hover-bg-light' }}">
                                    <div class="d-flex align-items-center gap-2.5 text-truncate">
                                        <div class="position-relative flex-shrink-0">
                                            @if($member->avatar)
                                                <img src="{{ asset('storage/' . $member->avatar) }}" alt="Avatar" class="rounded-circle" style="width: 28px; height: 28px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs" 
                                                     style="width: 28px; height: 28px; font-size: 0.75rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                                                    {{ strtoupper(substr($member->first_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="width: 8px; height: 8px;"></span>
                                        </div>
                                        <div class="d-flex flex-column text-truncate text-start">
                                            <span class="fw-semibold small text-truncate lh-1 {{ $isDirectActive ? 'text-white' : 'text-dark' }}">{{ $member->full_name }}</span>
                                            <span class="{{ $isDirectActive ? 'text-white-50' : 'text-muted' }} text-truncate mt-1" style="font-size: 0.7rem;">{{ $member->display_designation }}</span>
                                        </div>
                                    </div>
                                    @if($unread > 0)
                                        <span class="badge rounded-pill bg-danger font-monospace px-2 py-0.5" style="font-size: 0.65rem;">
                                            {{ $unread }}
                                        </span>
                                    @endif
                                </a>
                            </li>
                        @empty
                            <li class="px-3 py-1 text-muted small fst-italic">No team members</li>
                        @endforelse
                    </ul>

                </div>
            </div>

            <!-- Right Chat Conversation Pane -->
            <div class="col-12 col-md-8 col-lg-9 d-flex flex-column h-100 chat-main-pane" style="background-color: var(--body-bg, #F8FAFC);">
                
                <!-- Chat Stream Header -->
                <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2 chat-header-pane" style="background-color: var(--card-bg, #FFFFFF);">
                    <div class="d-flex align-items-center gap-3">
                        @if($activeType === 'general')
                            <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-hashtag fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark fs-6">general</h6>
                                <small class="text-muted">Company-wide team discussions, announcements & coordination</small>
                            </div>
                        @elseif($activeType === 'project' && $activeTarget)
                            <div class="rounded-3 bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-diagram-project fs-5"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark fs-6">{{ $activeTarget->name }}</h6>
                                <small class="text-muted">Project discussion room • Client: {{ $activeTarget->client_name ?: 'Internal' }}</small>
                            </div>
                        @elseif($activeType === 'direct' && $activeTarget)
                            <div class="position-relative flex-shrink-0">
                                @if($activeTarget->avatar)
                                    <img src="{{ asset('storage/' . $activeTarget->avatar) }}" alt="Avatar" class="rounded-circle shadow-sm" style="width: 40px; height: 40px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                         style="width: 40px; height: 40px; font-size: 1rem; background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);">
                                        {{ strtoupper(substr($activeTarget->first_name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-white rounded-circle" style="width: 10px; height: 10px;"></span>
                            </div>
                            <div>
                                <h6 class="mb-0 fw-bold text-dark fs-6">{{ $activeTarget->full_name }}</h6>
                                <small class="text-muted">{{ $activeTarget->display_designation }} • {{ $activeTarget->email }}</small>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-secondary border px-3 py-1.5 rounded-pill font-monospace small">
                            <i class="fa-solid fa-circle text-success me-1" style="font-size: 0.55rem;"></i> Active Now
                        </span>
                    </div>
                </div>

                <!-- Messages Stream Area -->
                <div id="chat-messages-container" class="flex-grow-1 p-4 overflow-y-auto d-flex flex-column gap-3 chat-stream-scroll">
                    <!-- Loading skeleton -->
                    <div id="chat-loading" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                        <span class="small fw-semibold">Loading conversation...</span>
                    </div>
                </div>

                <!-- Input Footer -->
                <div class="p-3 border-top chat-footer-pane" style="background-color: var(--card-bg, #FFFFFF);">
                    <!-- Attachment preview chip -->
                    <div id="attachment-preview" class="d-none align-items-center gap-2 mb-2 p-2 bg-light rounded-3 border d-inline-flex">
                        <i class="fa-solid fa-paperclip text-primary"></i>
                        <span id="attachment-name" class="small fw-semibold text-truncate" style="max-width: 240px;"></span>
                        <button type="button" onclick="clearAttachment()" class="btn btn-sm btn-link text-danger p-0 ms-2 text-decoration-none">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <form id="chat-send-form" onsubmit="handleSendMessage(event)" class="d-flex align-items-center gap-2">
                        @csrf
                        <input type="hidden" name="channel_type" value="{{ $activeType }}">
                        @if($activeType === 'project')
                            <input type="hidden" name="project_id" value="{{ $activeId }}">
                        @elseif($activeType === 'direct')
                            <input type="hidden" name="receiver_id" value="{{ $activeId }}">
                        @endif

                        <input type="file" id="chat-file-input" name="attachment" class="d-none" onchange="handleFileSelected(this)">

                        <button type="button" onclick="document.getElementById('chat-file-input').click()" 
                                class="btn btn-light rounded-circle shadow-2xs d-flex align-items-center justify-content-center flex-shrink-0" 
                                title="Attach File or Image" style="width: 42px; height: 42px;">
                            <i class="fa-solid fa-paperclip text-secondary"></i>
                        </button>

                        <div class="input-group flex-grow-1">
                            <input type="text" id="chat-message-input" name="message" 
                                   class="form-control rounded-pill px-4 py-2 border shadow-2xs text-dark" 
                                   placeholder="{{ $activeType === 'general' ? 'Message #general...' : ($activeType === 'project' ? 'Message #' . ($activeTarget->name ?? 'project') . '...' : 'Message ' . ($activeTarget->first_name ?? 'colleague') . '...') }}" 
                                   autocomplete="off" style="font-size: 0.925rem;">
                        </div>

                        <button type="submit" id="chat-send-btn" 
                                class="btn btn-primary rounded-circle shadow-sm d-flex align-items-center justify-content-center flex-shrink-0" 
                                style="width: 42px; height: 42px;" title="Send Message">
                            <i class="fa-solid fa-paper-plane" style="font-size: 0.9rem;"></i>
                        </button>
                    </form>
                    <div class="d-flex justify-content-between align-items-center mt-1 px-2">
                        <small class="text-muted" style="font-size: 0.72rem;">Press <kbd class="px-1 py-0.5 rounded bg-light border text-muted">Enter</kbd> to send</small>
                        <small class="text-muted" style="font-size: 0.72rem;">Max file size: 10MB</small>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script>
    let lastMessageId = 0;
    let pollInterval = null;
    const currentChannelType = "{{ $activeType }}";
    const currentTargetId = "{{ $activeId }}";
    const messagesContainer = document.getElementById('chat-messages-container');
    const loadingElem = document.getElementById('chat-loading');
    const messageInput = document.getElementById('chat-message-input');
    const fileInput = document.getElementById('chat-file-input');
    const attachmentPreview = document.getElementById('attachment-preview');
    const attachmentName = document.getElementById('attachment-name');

    document.addEventListener('DOMContentLoaded', function() {
        loadMessages(true);
        // Start background polling every 3 seconds
        pollInterval = setInterval(() => loadMessages(false), 3000);
    });

    window.addEventListener('beforeunload', function() {
        if (pollInterval) clearInterval(pollInterval);
    });

    function handleFileSelected(input) {
        if (input.files && input.files[0]) {
            attachmentName.textContent = input.files[0].name;
            attachmentPreview.classList.remove('d-none');
            attachmentPreview.classList.add('d-flex');
        }
    }

    function clearAttachment() {
        fileInput.value = '';
        attachmentPreview.classList.add('d-none');
        attachmentPreview.classList.remove('d-flex');
    }

    function scrollToBottom() {
        if (messagesContainer) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    function renderMessage(msg) {
        const isMe = msg.is_me;
        const msgDiv = document.createElement('div');
        msgDiv.id = `msg-${msg.id}`;
        msgDiv.className = `d-flex gap-2.5 ${isMe ? 'flex-row-reverse text-end' : 'flex-row text-start'} mb-1`;

        let avatarHtml = '';
        if (msg.user_avatar) {
            avatarHtml = `<img src="${msg.user_avatar}" alt="Avatar" class="rounded-circle flex-shrink-0 shadow-2xs" style="width: 34px; height: 34px; object-fit: cover;">`;
        } else {
            avatarHtml = `<div class="rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs" style="width: 34px; height: 34px; font-size: 0.85rem; background: ${isMe ? 'linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%)' : '#64748B'};">${msg.user_initial}</div>`;
        }

        let attachmentHtml = '';
        if (msg.attachment_url) {
            if (msg.is_image) {
                attachmentHtml = `
                    <div class="mt-2 rounded-3 overflow-hidden border shadow-2xs" style="max-width: 280px;">
                        <a href="${msg.attachment_url}" target="_blank">
                            <img src="${msg.attachment_url}" alt="Attachment" class="img-fluid rounded-3 hover-opacity transition-all" style="max-height: 200px; object-fit: cover;">
                        </a>
                    </div>`;
            } else {
                attachmentHtml = `
                    <div class="mt-2 p-2.5 rounded-3 border bg-light d-flex align-items-center gap-2 shadow-2xs" style="max-width: 280px;">
                        <i class="fa-solid fa-file text-primary fs-5"></i>
                        <span class="small fw-semibold text-truncate text-dark">${msg.attachment_name || 'Attached File'}</span>
                        <a href="${msg.attachment_url}" download target="_blank" class="btn btn-sm btn-primary rounded-circle ms-auto p-1 d-flex align-items-center justify-content-center" style="width: 26px; height: 26px;">
                            <i class="fa-solid fa-arrow-down" style="font-size: 0.7rem;"></i>
                        </a>
                    </div>`;
            }
        }

        let messageTextHtml = '';
        if (msg.message) {
            // Convert urls into links
            const linkedText = msg.message.replace(/(https?:\/\/[^\s]+)/g, '<a href="$1" target="_blank" class="text-decoration-underline">$1</a>');
            messageTextHtml = `<div class="chat-bubble-text small ${isMe ? 'text-white' : 'text-dark'}" style="word-break: break-word; font-size: 0.9rem;">${linkedText}</div>`;
        }

        const bubbleBg = isMe 
            ? 'background: linear-gradient(135deg, #4F46E5 0%, #4338CA 100%); color: #FFFFFF;' 
            : 'background-color: var(--card-bg, #FFFFFF); border: 1px solid var(--border-color, #E2E8F0);';

        msgDiv.innerHTML = `
            ${avatarHtml}
            <div class="d-flex flex-column ${isMe ? 'align-items-end' : 'align-items-start'}" style="max-width: 72%;">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-bold small ${isMe ? 'text-primary' : 'text-dark'}" style="font-size: 0.8rem;">${isMe ? 'You' : msg.user_name}</span>
                    <span class="text-muted font-monospace" style="font-size: 0.68rem;">${msg.created_at_time}</span>
                </div>
                <div class="p-3 rounded-4 shadow-2xs" style="${bubbleBg} border-radius: ${isMe ? '18px 4px 18px 18px' : '4px 18px 18px 18px'} !important;">
                    ${messageTextHtml}
                    ${attachmentHtml}
                </div>
            </div>
        `;

        return msgDiv;
    }

    function loadMessages(isInitial = false) {
        let url = `{{ route('chat.messages') }}?channel_type=${currentChannelType}&last_id=${isInitial ? 0 : lastMessageId}`;
        if (currentTargetId) {
            url += `&target_id=${currentTargetId}`;
        }

        fetch(url, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (loadingElem) loadingElem.remove();

            if (data.status === 'success' && data.messages && data.messages.length > 0) {
                let hasNew = false;
                data.messages.forEach(msg => {
                    if (!document.getElementById(`msg-${msg.id}`)) {
                        const elem = renderMessage(msg);
                        messagesContainer.appendChild(elem);
                        lastMessageId = Math.max(lastMessageId, msg.id);
                        hasNew = true;
                    }
                });

                if (hasNew) {
                    scrollToBottom();
                }
            } else if (isInitial && messagesContainer.children.length === 0) {
                messagesContainer.innerHTML = `
                    <div class="text-center py-5 my-auto text-muted">
                        <i class="fa-regular fa-comments fs-1 opacity-25 mb-3 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">No messages here yet</h6>
                        <small class="text-secondary">Be the first to start the conversation in this thread!</small>
                    </div>`;
            }
        })
        .catch(err => {
            console.warn('Failed to fetch messages:', err);
        });
    }

    function handleSendMessage(e) {
        e.preventDefault();
        const form = document.getElementById('chat-send-form');
        const msgText = messageInput.value.trim();
        const hasFile = fileInput.files && fileInput.files.length > 0;

        if (!msgText && !hasFile) return;

        const formData = new FormData(form);
        const sendBtn = document.getElementById('chat-send-btn');
        sendBtn.disabled = true;

        fetch("{{ route('chat.send') }}", {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            sendBtn.disabled = false;
            if (data.status === 'success' && data.message) {
                messageInput.value = '';
                clearAttachment();

                // Check if empty placeholder exists
                const emptyPlaceholder = messagesContainer.querySelector('.text-center.py-5');
                if (emptyPlaceholder) emptyPlaceholder.remove();

                if (!document.getElementById(`msg-${data.message.id}`)) {
                    const elem = renderMessage(data.message);
                    messagesContainer.appendChild(elem);
                    lastMessageId = Math.max(lastMessageId, data.message.id);
                    scrollToBottom();
                }
                messageInput.focus();
            } else {
                alert(data.message || 'Could not send message.');
            }
        })
        .catch(err => {
            sendBtn.disabled = false;
            console.error('Error sending message:', err);
            alert('Failed to send message. Please check connection.');
        });
    }
</script>
@endsection
