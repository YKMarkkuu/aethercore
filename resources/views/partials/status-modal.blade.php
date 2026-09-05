<div id="statusModal" class="status-modal hidden">
    <div class="status-modal-content">
        <div class="status-modal-header">
            <h2>Set Status</h2>
            <button class="status-modal-close" onclick="closeStatusModal()">✕</button>
        </div>
        <div class="status-modal-body">
            <form action="{{ route('status.update') }}" method="POST" id="statusForm">
                @csrf
                <button type="submit" name="status" value="online" class="status-option">
                    <span class="status-dot online">●</span>
                    <span class="status-label">Online</span>
                    <span class="status-check">✓</span>
                </button>
                <button type="submit" name="status" value="idle" class="status-option">
                    <span class="status-dot idle">◐</span>
                    <span class="status-label">Idle</span>
                    <span class="status-check">✓</span>
                </button>
                <button type="submit" name="status" value="dnd" class="status-option">
                    <span class="status-dot dnd">●</span>
                    <span class="status-label">Do Not Disturb</span>
                    <span class="status-check">✓</span>
                </button>
                <button type="submit" name="status" value="offline" class="status-option">
                    <span class="status-dot offline">○</span>
                    <span class="status-label">Offline</span>
                    <span class="status-check">✓</span>
                </button>
            </form>
        </div>
    </div>
</div>