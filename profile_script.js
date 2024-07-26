const unpinButtons = document.querySelectorAll('.unpin-btn');
    if (unpinButtons.length > 0) {
        unpinButtons.forEach(button => {
            button.addEventListener('click', function () {
                const documentId = this.getAttribute('data-id');
                fetch('unpin_document.php', {
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
                        alert(result); // Display the server's response
                        if (result.includes('success')) {
                            this.closest('.document-item').remove(); // Remove the document from the view
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    } else {
        console.log('No unpin buttons found.');
    }

    const viewButtons = document.querySelectorAll('.view-btn');
    if (viewButtons.length > 0) {
        viewButtons.forEach(button => {
            button.addEventListener('click', function () {
                const documentId = this.getAttribute('data-id');
                window.location.href = `view_document.php?id=${documentId}`; 
            });
        });
    } else {
        console.log('No view buttons found.');
    }