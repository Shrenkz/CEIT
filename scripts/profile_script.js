document.addEventListener('DOMContentLoaded', () => {
    const customAlert = document.getElementById("customAlert");
    const alertMessage = document.getElementById("alertMessage");
    const alertYesBtn = document.getElementById("alertYesBtn");
    const alertNoBtn = document.getElementById("alertNoBtn");
    const profilePhotoInput = document.getElementById('photoUpload');

    // Function to show the custom alert
    function showCustomAlert(message, onYesCallback) {
        alertMessage.textContent = message;
        customAlert.classList.remove('hidden');

        alertYesBtn.onclick = function () {
            customAlert.classList.add('hidden');
            if (typeof onYesCallback === 'function') {
                onYesCallback();
            }
        };

        alertNoBtn.onclick = function () {
            customAlert.classList.add('hidden');
        };
    }

    // Handle unpin button click
    document.querySelectorAll('.unpin-btn').forEach(button => {
        button.addEventListener('click', function () {
            const documentId = this.getAttribute('data-id');

            showCustomAlert('unpin this document from your profile?', () => {
                fetch('../actions/unpin_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'documentId': documentId
                    })
                })
                    .then(response => response.text())
                    .then(result => {
                        alert(result);
                        if (result.includes('success')) {
                            this.closest('.document-item').remove();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    });

    // Handle view button click
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function () {
            const documentId = this.getAttribute('data-id');
            window.location.href = `./actions/view_document.php?id=${documentId}`;
        });
    });

    // PROFILE PHOTO
    profilePhotoInput.addEventListener('change', function () {
        const file = this.files[0];
        if (file) {
            const formData = new FormData();
            formData.append('profile_photo', file);

            fetch('../actions/upload_photo.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log('Upload Response:', data); // Debugging: Log the response
                    if (data.success) {
                        // Update the profile photo in the DOM
                        document.querySelector('.profile-photo-img').src = data.newPhotoUrl;
                        alert('Profile photo updated successfully.');
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error uploading profile photo:', error);
                    alert('There was an error uploading the profile photo.');
                });
        }
    });
});
