{{-- Owner-only. The caller checks ownership before including this, and
     it is re-checked here so the partial is safe if ever included
     elsewhere. Operates ONLY on the $space being viewed. --}}
@if($space->isOwner(auth()->id()))
<form action="{{ route('spaces.update', $space) }}" method="POST" enctype="multipart/form-data" data-ajax-reset="false" style="display: flex; flex-direction: column; gap: 0.7rem; max-width: 460px;">
    @csrf
    @method('PUT')

    <div>
        <label for="spaceSettingsName" style="font-size: 0.65rem; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Space Name</label>
        <input type="text" id="spaceSettingsName" name="name" value="{{ $space->name }}" maxlength="100" required class="settings-input">
    </div>

    <div>
        <label for="spaceSettingsDescription" style="font-size: 0.65rem; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Description</label>
        <textarea id="spaceSettingsDescription" name="description" maxlength="300" rows="3" class="settings-input" style="resize: none;">{{ $space->description }}</textarea>
    </div>

    <div>
        <label for="spaceSettingsIcon" style="font-size: 0.65rem; color: var(--text-muted); display: block; margin-bottom: 0.2rem;">Icon</label>
        <div style="display: flex; align-items: center; gap: 0.7rem;">
            <div class="space-icon" style="width: 56px; height: 56px; font-size: 1.2rem;">
                @if($space->getIconUrl())
                    <img src="{{ $space->getIconUrl() }}" alt="{{ $space->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                @else
                    {{ strtoupper($space->name[0] ?? '?') }}
                @endif
            </div>
            <input type="file" id="spaceSettingsIcon" name="icon" accept="image/jpeg,image/png,image/webp,image/gif" class="settings-input">
        </div>
        <span class="settings-hint">JPG, PNG, WebP or GIF, up to 5 MB.</span>
    </div>

    <div>
        <button type="submit" class="settings-btn">Save Changes</button>
    </div>
</form>
@endif
