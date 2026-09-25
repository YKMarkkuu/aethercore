@if(!($sessionDriverSupported ?? false))
    <p class="settings-hint">Session management isn't available yet on this server.</p>
@else
    @forelse(($settingsSessions ?? []) as $s)
        <div class="settings-v2-row">
            <div>
                <div class="settings-v2-row-label">
                    {{ \Illuminate\Support\Str::limit($s->user_agent ?? 'Unknown device', 60) }}
                    @if($s->id === ($currentSessionId ?? null))
                        <span class="settings-v2-pill">This device</span>
                    @endif
                </div>
                <div class="settings-v2-row-sub">{{ $s->ip_address }} · last active {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}</div>
            </div>
            @if($s->id !== ($currentSessionId ?? null))
                <form action="{{ route('settings.sessions.destroy', $s->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="settings-v2-btn-outline-danger">Log Out</button>
                </form>
            @endif
        </div>
    @empty
        <p class="settings-hint">No other active sessions found.</p>
    @endforelse
@endif
