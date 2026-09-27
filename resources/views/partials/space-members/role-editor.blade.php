@php
    $editorKey = $role->id ?? 'new';
@endphp

<form action="{{ $role ? route('space-roles.update', [$space, $role]) : route('space-roles.store', $space) }}" method="POST" style="display: flex; flex-direction: column; gap: 0.5rem;">
    @csrf
    @if($role)
        @method('PATCH')
    @endif

    <div>
        <label style="font-size: 0.6rem; color: #6a6a6a; display: block;">Name</label>
        <input type="text" name="name" value="{{ old('name', $role->name ?? '') }}" maxlength="50" required class="settings-input">
    </div>

    <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
        <div>
            <label style="font-size: 0.6rem; color: #6a6a6a; display: block;">Color</label>
            <input type="color" name="color" value="{{ old('color', $role->color ?? '#99aab5') }}" style="width: 40px; height: 28px; padding: 0; border: 2px solid var(--border-default); border-radius: 4px;">
        </div>
        <div>
            <label style="font-size: 0.6rem; color: #6a6a6a; display: block;">Position</label>
            <input type="number" name="position" min="0" max="{{ max($actorPosition - 1, 0) }}" value="{{ old('position', $role->position ?? 0) }}" class="settings-input" style="width: 80px;">
        </div>
    </div>

    <div>
        <label style="font-size: 0.6rem; color: #6a6a6a; display: block; margin-bottom: 0.2rem;">Permissions</label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.2rem;">
            @foreach(\App\Enums\SpacePermission::cases() as $perm)
                <label style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.65rem; color: #1e1e1e;">
                    <input type="checkbox" name="permissions[]" value="{{ $perm->value }}" @checked(in_array($perm->value, old('permissions', $role->permissions ?? [])))>
                    {{ ucwords(str_replace('_', ' ', $perm->value)) }}
                </label>
            @endforeach
        </div>
    </div>

    <div style="display: flex; gap: 0.4rem;">
        <button type="submit" class="settings-btn">Save</button>
        <button type="button" class="settings-btn" onclick="toggleRoleEditor('{{ $editorKey }}')">Cancel</button>
    </div>
</form>

@if($role && !$role->is_default)
    <form action="{{ route('space-roles.destroy', [$space, $role]) }}" method="POST" style="margin-top: 0.4rem;" onsubmit="return confirm('Delete the {{ $role->name }} role? Members holding it will move to the default role.')">
        @csrf
        @method('DELETE')
        <button type="submit" class="settings-btn settings-btn-danger" style="font-size: 0.65rem;">Delete Role</button>
    </form>
@endif