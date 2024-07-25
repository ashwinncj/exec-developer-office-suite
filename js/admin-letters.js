jQuery(document).ready(function ($) {
    const apiUrl = execDevOfficeSuite.apiUrl;
    const addLetterApiUrl = apiUrl.replace('letters', 'add-letter');
    const nonce = execDevOfficeSuite.nonce;
    let currentPage = 1;
    const perPage = 10;

    function showOptions() {
        $('#options-container').show();
        $('#letters-container, #pagination-container, #search-container, #create-letter-container, #letter-dialog, #back-button-container').hide();
    }

    function showLetters() {
        $('#options-container, #create-letter-container, #letter-dialog').hide();
        $('#letters-container, #pagination-container, #search-container, #back-button-container').show();
    }

    function fetchLetters(page = 1) {
        console.log('Fetching letters, page:', page); // Debugging output
        $.ajax({
            url: apiUrl,
            method: 'GET',
            data: {
                page: page,
                per_page: perPage,
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function (data, textStatus, request) {
                console.log('Fetched letters data:', data); // Debugging output
                const totalPages = request.getResponseHeader('X-WP-TotalPages');
                displayLetters(data);
                setupPagination(totalPages, page);
            },
            error: function (error) {
                console.error('Error fetching letters:', error);
            }
        });
    }

    function searchLetters(query) {
        console.log('Searching letters, query:', query); // Debugging output
        $.ajax({
            url: apiUrl.replace('letters', 'search-letters'),
            method: 'GET',
            data: {
                search: query,
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function (data) {
                console.log('Searched letters data:', data); // Debugging output
                displayLetters(data);
                setupPagination(1, 1);
            },
            error: function (error) {
                console.error('Error searching letters:', error);
            }
        });
    }

    function addOrUpdateLetter(event) {
        event.preventDefault();
        const letterId = $('#letter-id').val();
        const subject = $('#letter-subject').val();
        const content = $('#letter-content').val();
        const toField = $('#letter-to').val();
        const address = $('#letter-address').val();

        const url = letterId ? `${apiUrl}/${letterId}` : addLetterApiUrl;
        const method = letterId ? 'POST' : 'POST'; // Update method should also be POST
        
        $('#letter-id').val(); // Set this back to blank.

        $.ajax({
            url: url,
            method: method,
            data: {
                subject: subject,
                content: content,
                to_field: toField,
                address: address,
            },
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function (response) {
                console.log('Letter added/updated:', response); // Debugging output
                alert('Letter ' + (letterId ? 'updated' : 'added') + ' successfully');
                fetchLetters(currentPage);
                showLetters();
                $('#create-letter-form')[0].reset();
                $('#letter-id').val('');
                $('#submit-letter-button').text('Submit');
            },
            error: function (error) {
                console.error('Error adding/updating letter:', error);
            }
        });
    }

    function displayLetters(letters) {
        const lettersContainer = $('#letters-container');
        lettersContainer.empty();

        // Create the table and table headers
        const table = `
            <table class="letters-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>To</th>
                        <th>Subject</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    ${letters.map(letter => `
                        <tr class="letter-item" data-id="${letter.id}">
                            <td>${letter.date}</td>
                            <td>${letter.to_field}</td>
                            <td><strong>${letter.subject}</strong></td>
                            <td>
                                <a class="view-letter" data-id="${letter.id}">More</a>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;

        lettersContainer.append(table);
    }

    function displayLetterDetails(letter) {
        const dialog = $('#letter-dialog');
        dialog.find('.dialog-content').html(`
            <p><strong><u>Date:</u></strong> ${letter.date}</p>
            <p><strong><u>To:</u></strong> ${letter.to_field}</p>
            <p><strong><u>Address:</u></strong> ${letter.address}</p>
            <p><strong><u>Subject:</u></strong> ${letter.subject}</p>
            <p><strong><u>Content:</u></strong></p>
            <p>${letter.content}</p>
            <p class="edit-letter" id="edit-letter" data-id="${letter.id}">Edit</p>
            <p class="export-letter" id="export-letter" data-id="${letter.id}">Export PDF</p>
        `);
        $('#letters-container, #pagination-container').hide();
        dialog.show();
    }

    function setupPagination(totalPages, currentPage) {
        const paginationContainer = $('#pagination-container');
        paginationContainer.empty();
        for (let i = 1; i <= totalPages; i++) {
            const pageItem = `<button class="pagination-button" data-page="${i}">${i}</button>`;
            paginationContainer.append(pageItem);
        }
        $('.pagination-button').removeClass('active');
        $(`.pagination-button[data-page="${currentPage}"]`).addClass('active');
    }

    function fetchLetterDetails(id) {
        console.log('Fetching letter details, id:', id); // Debugging output
        return $.ajax({
            url: `${apiUrl}/${id}`,
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function (data) {
                console.log('Fetched letter details:', data); // Debugging output
                return data;
            },
            error: function (error) {
                console.error('Error fetching letter details:', error);
                return null;
            }
        });
    }

    function populateEditForm(letter) {
        $('#letter-id').val(letter.id);
        $('#letter-subject').val(letter.subject);
        $('#letter-content').val(letter.content);
        $('#letter-to').val(letter.to_field);
        // Assuming you have initialized TinyMCE on the textarea with id 'letter-content'
        tinymce.get('letter-content').setContent(letter.address);
        $('#letter-address').val(letter.address);
        $('#submit-letter-button').text('Update');
        $('#create-letter-container').show();
        $('#letter-dialog').hide();
    }

    // Event Listeners
    $(document).on('click', '.pagination-button', function () {
        const page = $(this).data('page');
        currentPage = page;
        fetchLetters(page);
    });

    $(document).on('click', '.view-letter', function () {
        const id = $(this).data('id');
        fetchLetterDetails(id)
            .then(function (data) {
                displayLetterDetails(data);
            });
    });

    $(document).on('click', '.edit-letter', function () {
        const id = $(this).data('id');
        fetchLetterDetails(id)
            .then(function (data) {
                populateEditForm(data);
            });
    });

    $('#letter-dialog .close-dialog').on('click', function () {
        $('#letter-dialog').hide();
        showLetters();
    });

    $('#view-letters-button').on('click', function () {
        showLetters();
        fetchLetters(currentPage);
    });

    $('#create-letter-button').on('click', function () {
        $('#options-container, #create-letter-container, #letter-dialog').hide();
        $('#create-letter-container').show();
        $('#letter-id').val('');
        // Clear the other fields of form.
        $('#create-letter-form')[0].reset();
        $('#submit-letter-button').text('Submit');
        $('#letters-container, #pagination-container').hide();
    });

    $('#back-to-options').on('click', function () {
        showOptions();
    });

    $('#back-to-options-letters').on('click', function () {
        showOptions();
    });

    $('#search-button').on('click', function () {
        const query = $('#search-input').val();
        searchLetters(query);
    });

    $('#create-letter-form').on('submit', addOrUpdateLetter);

    $('#cancel-edit').on('click', function () {
        showLetters();
        $('#create-letter-form')[0].reset();
        $('#letter-id').val('');
        $('#submit-letter-button').text('Submit');
    });

    $(document).on('click', '#export-letter', function () {
        const id = $(this).data('id');
        exportLetter(id);
    });
    
    function exportLetter(id) {
        console.log('Exporting letter, id:', id); // Debugging output
        $.ajax({
            url: `${apiUrl.replace('letters', 'export-letter')}/${id}`,
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function (response) {
                console.log('Exported letter PDF data:', response); // Debugging output
                const pdfData = response.pdf;
                const link = document.createElement('a');
                link.href = 'data:application/pdf;base64,' + pdfData;
                link.download = `letter-${id}.pdf`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            },
            error: function (error) {
                console.error('Error exporting letter:', error);
            }
        });
    }
    
    // Initial Setup
    showOptions();
});
