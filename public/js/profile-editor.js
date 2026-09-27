(function () {
    const form = document.getElementById('editProfileForm');
    if (!form) return;

    const editMode = document.getElementById('editMode');
    const editButton = document.getElementById('editProfileBtn');
    const displayName = document.getElementById('displayNameDisplay');
    const bioDisplay = document.getElementById('bioDisplay');
    const bioInput = form.querySelector('[name="bio"]');

    form.dataset.savedBio = bioInput.value;

    function updateBioPreview(bio) {
        bioDisplay.textContent = bio;
        const sidebarBio = document.getElementById('rightProfileBio');
        if (sidebarBio) sidebarBio.textContent = bio;
    }

    window.toggleEditMode = function (enterOnly = false) {
        const isEditing = editMode.style.display === 'none';
        if (!isEditing && enterOnly) return;

        const shouldEdit = enterOnly || isEditing;

        editMode.style.display = shouldEdit ? 'block' : 'none';
        editButton.style.display = shouldEdit ? 'none' : '';
        displayName.style.display = shouldEdit ? 'none' : 'block';
        bioDisplay.style.display = 'block';

        if (!shouldEdit) {
            bioInput.value = form.dataset.savedBio;
            updateBioPreview(bioInput.value);
        }
    };

    bioInput.addEventListener('input', function () {
        updateBioPreview(bioInput.value);
    });

    form.addEventListener('submit', async function (event) {
        event.preventDefault();

        const saveButton = form.querySelector('button[type="submit"]');
        saveButton.disabled = true;
        saveButton.textContent = 'Saving...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
                body: new FormData(form),
            });
            const data = await response.json();

            if (!response.ok) {
                throw new Error(Object.values(data.errors || {}).flat()[0] || 'Could not save your profile.');
            }

            displayName.textContent = data.display_name;
            form.dataset.savedBio = data.bio || '';
            bioInput.value = form.dataset.savedBio;
            updateBioPreview(form.dataset.savedBio);
            window.toggleEditMode();
        } catch (error) {
            alert(error.message || 'Could not save your profile. Please try again.');
        } finally {
            saveButton.disabled = false;
            saveButton.textContent = 'Save Changes';
        }
    });
})();