(function () {
    const form = document.getElementById('topFriendsForm');
    const modal = document.getElementById('topFriendsModal');
    if (!form || !modal) return;

    const choices = Array.from(form.querySelectorAll('.top-friend-checkbox'));
    const search = document.getElementById('topFriendsSearch');
    const selectedList = document.getElementById('topFriendsSelectedList');
    const selectedInputs = document.getElementById('topFriendsSelectedInputs');
    const countLabel = document.getElementById('topFriendsCount');
    const noMatches = document.getElementById('topFriendsNoMatches');
    const orderedIds = JSON.parse(form.dataset.selectedIds || '[]')
        .map(String)
        .filter(id => choices.some(choice => choice.value === id));
    let savedIds = orderedIds.slice();

    function showMessage(message, type) {
        if (window.Toast?.show) {
            window.Toast.show(message, type);
        } else {
            window.alert(message);
        }
    }

    function updateProfileList(friends) {
        const list = document.getElementById('topFriendsList');
        const emptyMessage = document.getElementById('topFriendsEmpty');
        if (!list || !emptyMessage) return false;

        list.replaceChildren();

        friends.forEach((friend, index) => {
            const row = document.createElement('div');
            row.dataset.friendId = friend.id;
            row.style.cssText = 'display:flex;align-items:center;gap:0.4rem;padding:0.15rem 0.3rem;background:#f8f5ec;border:1px solid #d0c8c0;border-radius:4px;';

            const rank = document.createElement('span');
            rank.textContent = '#' + (index + 1);
            rank.style.cssText = 'font-size:0.55rem;font-weight:700;color:#1a4a9e;min-width:16px;';
            row.appendChild(rank);

            const avatar = document.createElement('div');
            avatar.style.cssText = 'width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#3a7bd5,#1a4a9e);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:0.55rem;color:#fff;flex-shrink:0;overflow:hidden;';
            if (friend.avatar_url) {
                const image = document.createElement('img');
                image.src = friend.avatar_url;
                image.alt = '';
                image.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                avatar.appendChild(image);
            } else {
                avatar.textContent = friend.display_name.charAt(0).toUpperCase();
            }
            row.appendChild(avatar);

            const info = document.createElement('div');
            info.style.cssText = 'flex:1;min-width:0;';
            const link = document.createElement('a');
            link.href = friend.profile_url;
            link.textContent = friend.display_name;
            link.style.cssText = 'font-size:0.65rem;color:#1e1e1e;text-decoration:none;display:block;';
            info.appendChild(link);

            const nowPlaying = document.createElement('div');
            nowPlaying.className = 'friend-now-playing';
            nowPlaying.style.cssText = 'display:none;font-size:0.5rem;color:#3a7bd5;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;align-items:center;gap:0.2rem;';
            const nowPlayingText = document.createElement('span');
            nowPlayingText.className = 'friend-now-playing-text';
            nowPlaying.appendChild(nowPlayingText);
            info.appendChild(nowPlaying);
            row.appendChild(info);
            list.appendChild(row);
        });

        emptyMessage.style.display = friends.length ? 'none' : 'inline';
        return true;
    }

    function renderSelection() {
        selectedList.replaceChildren();
        selectedInputs.replaceChildren();
        countLabel.textContent = orderedIds.length + ' / 8';

        choices.forEach(choice => {
            choice.checked = orderedIds.includes(choice.value);
            choice.disabled = orderedIds.length >= 8 && !choice.checked;
        });

        orderedIds.forEach((id, index) => {
            const choice = choices.find(item => item.value === id);
            if (!choice) return;

            const option = choice.closest('.top-friend-option');
            const displayName = option.dataset.displayName;
            const username = option.dataset.username;
            const row = document.createElement('li');
            row.style.cssText = 'display:flex;align-items:center;gap:0.35rem;padding:0.35rem 0.25rem;border-bottom:1px solid #e0dcd0;font-size:0.72rem;';

            const rank = document.createElement('span');
            rank.textContent = String(index + 1).padStart(2, '0');
            rank.style.cssText = 'font-size:0.62rem;font-weight:700;color:#1a4a9e;width:1.2rem;';
            row.appendChild(rank);

            const name = document.createElement('span');
            name.textContent = displayName;
            name.title = username;
            name.style.cssText = 'flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;';
            row.appendChild(name);

            const controls = document.createElement('span');
            controls.style.cssText = 'display:flex;gap:0.2rem;';
            [
                { label: 'Move up', symbol: '↑', offset: -1 },
                { label: 'Move down', symbol: '↓', offset: 1 },
                { label: 'Remove', symbol: '×', offset: 0 },
            ].forEach(control => {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = control.symbol;
                button.title = control.label;
                button.setAttribute('aria-label', control.label + ' ' + displayName);
                button.disabled = control.offset < 0 ? index === 0 : control.offset > 0 ? index === orderedIds.length - 1 : false;
                button.style.cssText = 'width:25px;height:25px;padding:0;border:1px solid #b0a8a0;background:#ece9d8;color:#1e1e1e;cursor:pointer;';
                button.addEventListener('click', () => {
                    if (control.offset === 0) {
                        orderedIds.splice(index, 1);
                    } else {
                        const destination = index + control.offset;
                        [orderedIds[index], orderedIds[destination]] = [orderedIds[destination], orderedIds[index]];
                    }
                    renderSelection();
                });
                controls.appendChild(button);
            });
            row.appendChild(controls);
            selectedList.appendChild(row);

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'friends[]';
            input.value = id;
            selectedInputs.appendChild(input);
        });
    }

    function closeModal() {
        orderedIds.splice(0, orderedIds.length, ...savedIds);
        renderSelection();
        modal.classList.add('hidden');
    }

    window.toggleTopFriendsModal = function () {
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            search.focus();
        } else {
            closeModal();
        }
    };

    choices.forEach(choice => {
        choice.addEventListener('change', () => {
            if (choice.checked) {
                if (orderedIds.length === 8) {
                    choice.checked = false;
                    showMessage('Choose up to 8 friends.', 'error');
                    return;
                }
                orderedIds.push(choice.value);
            } else {
                const index = orderedIds.indexOf(choice.value);
                if (index !== -1) orderedIds.splice(index, 1);
            }
            renderSelection();
        });
    });

    search.addEventListener('input', () => {
        const query = search.value.trim().toLocaleLowerCase();
        let visibleCount = 0;
        form.querySelectorAll('.top-friend-option').forEach(option => {
            const matches = option.dataset.search.includes(query);
            option.hidden = !matches;
            if (matches) visibleCount++;
        });
        noMatches.style.display = visibleCount ? 'none' : 'block';
    });

    modal.addEventListener('click', event => {
        if (event.target === modal) closeModal();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        const saveButton = form.querySelector('[type="submit"]');
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(form),
                credentials: 'same-origin',
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.message || 'Could not save your Top 8.');
            }

            if (!Array.isArray(data.friends) || !updateProfileList(data.friends)) {
                window.location.reload();
                return;
            }
            savedIds = orderedIds.slice();
            modal.classList.add('hidden');
            showMessage(data.message || 'Top 8 Friends updated!', 'success');
        } catch (error) {
            showMessage(error.message || 'Could not save your Top 8. Please try again.', 'error');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save Changes';
        }
    });

    renderSelection();
})();