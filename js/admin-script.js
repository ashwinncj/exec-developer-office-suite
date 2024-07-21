jQuery(document).ready(function ($) {
    const apiUrl = execDevOfficeSuite.apiUrl;
    const addLetterApiUrl = apiUrl.replace('letters', 'add-letter');
    const searchApiUrl = apiUrl.replace('letters', 'search-letters');
    const nonce = execDevOfficeSuite.nonce;
    let currentPage = 1;
    const perPage = 10;

    function showOptions() {
        $('#options-container').show();
        $('#letters-container, #pagination-container, #search-container, #create-letter-container, #back-button-container').hide();
    }

    function showLetters() {
        $('#options-container, #create-letter-container').hide();
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
            url: searchApiUrl,
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

    function addLetter(event) {
        event.preventDefault();
        const subject = $('#letter-subject').val();
        const content = $('#letter-content').val();
        const toField = $('#letter-to').val();
        const address = $('#letter-address').val();

        $.ajax({
            url: addLetterApiUrl,
            method: 'POST',
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
                console.log('Letter added:', response); // Debugging output
                alert('Letter added successfully');
                fetchLetters(currentPage);
                showLetters();
            },
            error: function (error) {
                console.error('Error adding letter:', error);
            }
        });
    }

    function displayLettersInBox(letters) {
        const lettersContainer = $('#letters-container');
        lettersContainer.empty();
        letters.forEach(letter => {
            const letterItem = `
                <div class="letter-item" data-id="${letter.id}">
                    <p>${letter.date}</p>
                    <p>${letter.to_field}</p>
                    <p><strong>Sub: ${letter.subject}</strong></p>
                    <a class="view-letter" data-id="${letter.id}">More</a>
                </div>
            `;
            lettersContainer.append(letterItem);
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
                            <td><a class="view-letter" data-id="${letter.id}">More</a></td>
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
        `);
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
        $.ajax({
            url: `${apiUrl}/${id}`,
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', nonce);
            },
            success: function (data) {
                console.log('Fetched letter details:', data); // Debugging output
                displayLetterDetails(data);
            },
            error: function (error) {
                console.error('Error fetching letter details:', error);
            }
        });
    }

    // Event Listeners
    $(document).on('click', '.pagination-button', function () {
        const page = $(this).data('page');
        currentPage = page;
        fetchLetters(page);
    });

    $(document).on('click', '.view-letter', function () {
        const id = $(this).data('id');
        fetchLetterDetails(id);
    });

    $('#letter-dialog .close-dialog').on('click', function () {
        $('#letter-dialog').hide();
    });

    $('#view-letters-button').on('click', function () {
        showLetters();
        fetchLetters(currentPage);
    });

    $('#create-letter-button').on('click', function () {
        $('#options-container').hide();
        $('#create-letter-container').show();
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

    $('#create-letter-form').on('submit', addLetter);

    // Initial Setup
    showOptions();
});
