@if(!($sessionDriverSupported ?? false))
    <p class="settings-hint">Session management isn't available yet on this server.</p>
@else
    @forelse(($settingsSessions ?? []) as $s)
        <div class="device-item">
            <div>
                <div class="device-name">
                    {{ \Illuminate\Support\Str::limit($s->user_agent ?? 'Unknown device', 60) }}
                    @if($s->id === ($currentSessionId ?? null))
                        <span class="device-status">This device</span>
                    @endif
                </div>
                <div class="device-detail">{{ $s->ip_address }} · last active {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}</div>
            </div>
            @if($s->id !== ($currentSessionId ?? null))
                <form action="{{ route('settings.sessions.destroy', $s->id) }}" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="device-logout-btn">Log Out</button>
                </form>
            @endif
        </div>
    @empty
        <p class="settings-hint">No other active sessions found.</p>
    @endforelse
@endif
