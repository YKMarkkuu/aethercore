{{-- Content depends on whether the viewer owns THIS $space. --}}
<div style="max-width: 460px;">
    <h3 style="font-size: 0.9rem; font-weight: 700; color: var(--danger-text); margin-bottom: 0.5rem;">Danger Zone</h3>

    @if($space->isOwner(auth()->id()))
        <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); border-radius: 4px; padding: 0.75rem;">
            <p style="font-size: 0.75rem; color: var(--danger-text); margin-bottom: 0.6rem;">
                Deleting this Space is permanent. All channels, messages, and members will be removed. This cannot be undone.
            </p>
            <form action="{{ route('spaces.destroy', $space) }}" method="POST" data-confirm="Delete this Space? This can't be undone." data-confirm-danger="true" data-confirm-title="Delete Space" data-confirm-ok="Delete Space">
                @csrf
                @method('DELETE')
                <button type="submit" class="delete-btn">Delete Space</button>
            </form>
        </div>
    @else
        <div style="background: var(--danger-bg); border: 1px solid var(--danger-border); border-radius: 4px; padding: 0.75rem;">
            <p style="font-size: 0.75rem; color: var(--danger-text); margin-bottom: 0.6rem;">
                Leaving this Space removes you from all its channels and you will stop receiving messages.
            </p>
            <form action="{{ route('spaces.leave', $space) }}" method="POST" data-confirm="Leave this Space?" data-confirm-danger="true" data-confirm-title="Leave Space" data-confirm-ok="Leave Space">
                @csrf
                <button type="submit" class="delete-btn">Leave Space</button>
            </form>
        </div>
    @endif
</div>
