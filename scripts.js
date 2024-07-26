document.addEventListener('DOMContentLoaded', (event) => {
    const modal = document.getElementById("advancedSearchModal");
    const btn = document.getElementById("advancedSearchBtn");
    const span = document.getElementsByClassName("close")[0];
    const addKeywordBtn = document.getElementById("addKeywordBtn");
    const keywordContainer = document.getElementById("keywordContainer");
    let keywordCount = 1;
    const customAlert = document.getElementById("customAlert");
    const alertMessage = document.getElementById("alertMessage");
    const alertOkBtn = document.getElementById("alertOkBtn");

    btn.onclick = () => {
        modal.style.display = "block";
    };

    span.onclick = () => {
        modal.style.display = "none";
    };

    window.onclick = (event) => {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    };

    addKeywordBtn.onclick = () => {
        if (keywordCount < 3) {
            keywordCount++;
            const newKeywordInput = document.createElement("input");
            newKeywordInput.type = "text";
            newKeywordInput.name = "keywords[]";
            newKeywordInput.id = "keyword" + keywordCount;
            newKeywordInput.placeholder = "Keyword";
            keywordContainer.appendChild(newKeywordInput);
        } else {
            addKeywordBtn.disabled = true;
            addKeywordBtn.textContent = "Max keywords reached";
        }
    };

    function showCustomAlert(message, callback) {
        alertMessage.textContent = message;
        customAlert.classList.remove('hidden');

        alertOkBtn.onclick = function () {
            customAlert.classList.add('hidden');
            if (typeof callback === 'function') {
                callback();
            }
        };
    }

    // Display results if search query is present
    const resultsContainer = document.getElementById('resultsContainer');
    const searchQuery = new URLSearchParams(window.location.search).get('search');

    if (searchQuery) {
        resultsContainer.style.display = 'block';
    }

    // Pin to profile
    document.querySelectorAll('.pin-btn').forEach(button => {
        button.addEventListener('click', function () {
            const documentId = this.getAttribute('data-document-id');
            const userId = document.getElementById('user-id').value;

            showCustomAlert('Are you sure you want to pin this document to your profile?', function () {
                // Make AJAX request to pin document
                fetch('pin_document.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'document_id': documentId,
                        'user_id': userId
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showCustomAlert('The document has been pinned to your profile.');
                        } else {
                            showCustomAlert('There was a problem pinning the document.');
                        }
                    })
                    .catch(error => {
                        showCustomAlert('There was a problem with the request.');
                    });
            });
        });
    });

    // Unpin document
    const unpinButtons = document.querySelectorAll('.unpin-btn');
    if (unpinButtons.length > 0) {
        unpinButtons.forEach(button => {
            button.addEventListener('click', function () {
                const documentId = this.getAttribute('data-id');
                showCustomAlert('Are you sure you want to unpin this document from your profile?', function () {
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
                                showCustomAlert('The document has been unpinned from your profile.');
                            } else {
                                showCustomAlert('There was a problem unpinning the document.');
                            }
                        })
                        .catch(error => {
                            showCustomAlert('There was a problem with the request.');
                        });
                });
            });
        });
    }

    // View button
    const viewButtons = document.querySelectorAll('.view-btn');
    if (viewButtons.length > 0) {
        viewButtons.forEach(button => {
            button.addEventListener('click', function () {
                const documentId = this.getAttribute('data-id');
                if (documentId) {
                    window.location.href = `view_document.php?id=${documentId}`;
                }
            });
        });
    } else {
        console.log('No view buttons found.');
    }

    document.getElementById('applyFilters').addEventListener('click', function () {
        const keywordElements = document.querySelectorAll('#keywordContainer input[name="keywords[]"]');
        let keywords = [];
        keywordElements.forEach(element => {
            if (element.value.trim() !== '') {
                keywords.push(element.value.trim());
            }
        });

        const format = document.getElementById('format').value;
        const version = document.getElementById('version').value;

        const filters = {
            keywords: keywords,
            format: format,
            version: version
        };

        console.log('Filters:', filters); // Debugging line

        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_filtered_data.php?filters=' + encodeURIComponent(JSON.stringify(filters)), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        console.log('Raw response:', xhr.responseText); // Debugging line
                        const results = JSON.parse(xhr.responseText);
                        displayResults(results);
                        document.querySelector('input[name="search"]').value = '';
                        document.getElementById('advancedSearchModal').style.display = 'none';
                        document.getElementById('resultCount').textContent = `Results found: ${results.length}`;
                    } catch (e) {
                        console.error("Error parsing JSON:", e);
                        console.error("Raw response:", xhr.responseText); // Debugging line
                        showCustomAlert('Failed to parse response from server.');
                    }
                } else {
                    console.error("HTTP error:", xhr.status, xhr.statusText);
                    showCustomAlert('Server returned an error: ' + xhr.statusText);
                }
            }
        };

        xhr.onerror = function () {
            console.error("Request failed");
            showCustomAlert('Request failed. Please check your network connection.');
        };

        xhr.send();
    });

    // Function to display the filtered results
    function displayResults(results) {
        const resultsContainer = document.getElementById('results');
        resultsContainer.innerHTML = '';

        if (Array.isArray(results) && results.length > 0) {
            results.forEach(result => {
                const resultItem = document.createElement('div');
                resultItem.className = 'result-item';

                const title = document.createElement('h4');
                title.textContent = result.title || 'No title';
                resultItem.appendChild(title);

                const description = document.createElement('p');
                description.textContent = result.description || 'No description';
                resultItem.appendChild(description);

                const version = document.createElement('p');
                version.innerHTML = `<small>Version: ${result.version || 'N/A'}</small>`;
                resultItem.appendChild(version);

                const format = document.createElement('p');
                format.innerHTML = `<small>Format: ${result.format || 'N/A'}</small>`;
                resultItem.appendChild(format);

                const pinBtn = document.createElement('button');
                pinBtn.className = 'pin-btn';
                pinBtn.textContent = 'Pin to Profile';
                resultItem.appendChild(pinBtn);

                const viewBtn = document.createElement('button');
                viewBtn.className = 'view-btn';
                viewBtn.textContent = 'View';
                resultItem.appendChild(viewBtn);

                resultsContainer.appendChild(resultItem);
            });
        } else {
            resultsContainer.textContent = 'No results found for the applied filters.';
        }
    }


    // Handle view button click
    document.querySelectorAll('.view-btn').forEach(button => {
        button.addEventListener('click', function () {
            const documentId = this.getAttribute('data-id');
            if (documentId) {
                window.location.href = `view_document.php?id=${documentId}`;
            }
        });
    });
});
