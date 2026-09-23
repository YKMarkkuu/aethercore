<!-- ===== REPORT MODAL ===== -->
<!-- Single shared copy, included once via layouts.app (same pattern as
     the Share and Create-Space modals). Any "Report" button anywhere
     on the page opens it via window.openReportModal(type, id), where
     type is one of the short keys Report::REPORTABLE_TYPES understands
     ('post', 'comment', 'message', 'space_message', 'user'). -->
<div class="settings-modal hidden" id="reportModal">
    <div class="settings-modal-content" style="max-width: 380px; height: auto;">
        <div class="settings-modal-header">
            <h2>Report</h2>
            <button class="settings-modal-close" onclick="closeReportModal()">✕</button>
        </div>
        <form id="reportForm" style="padding: 1rem;">
            <div style="margin-bottom: 0.6rem;">
                <label style="font-size: 0.65rem; color: #6a6a6a; display: block; margin-bottom: 0.3rem;">Reason</label>
                @foreach(['spam' => 'Spam', 'harassment' => 'Harassment or bullying', 'hate_speech' => 'Hate speech', 'nudity' => 'Nudity or sexual content', 'violence' => 'Violence', 'other' => 'Other'] as $value => $label)
                    <label style="display: flex; align-items: center; gap: 0.4rem; font-size: 0.75rem; padding: 0.2rem 0; cursor: pointer;">
                        <input type="radio" name="reason" value="{{ $value }}" required>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <div style="margin-bottom: 0.6rem;">
                <label style="font-size: 0.65rem; color: #6a6a6a; display: block; margin-bottom: 0.2rem;">Additional details (optional)</label>
                <textarea id="reportDetails" class="settings-input" rows="2" maxlength="1000" style="width: 100%; resize: none;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem;">
                <button type="button" class="settings-btn" onclick="closeReportModal()">Cancel</button>
                <button type="submit" class="settings-btn settings-btn-danger" id="reportSubmitBtn">Submit Report</button>
            </div>
        </form>
    </div>
</div>
