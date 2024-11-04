$(document).ready(function () {
    // Load existing spare parts data on page load
    loadParts();

    // Function to fetch and display spare parts
    function loadParts() {
        $.ajax({
            url: 'spare_parts_crud.php',
            type: 'GET',
            data: { action: 'fetch' },
            success: function (response) {
                $('#partsTableBody').html(response);
            },
            error: function () {
                showToast('Failed to load parts. Please try again.', 'error');
            }
        });
    }

    // Handle form submission for adding/updating parts
    $('#partForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        // Validate the form fields before submission
        if (!validateForm(formData)) {
            showToast('Please fill all fields correctly.', 'error');
            return;
        }

        // Show loading indicator while processing the request
        showLoadingIndicator(true);

        // Determine if this is an add or update action
        const isEditMode = $('#partId').val() !== ''; // Check if partId is set

        $.ajax({
            url: 'spare_parts_crud.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (response) {
                const res = JSON.parse(response);
                if (res.error) {
                    showToast(res.error, 'error');
                } else {
                    $('#partModal').modal('hide');
                    loadParts();  // Refresh the parts table
                    resetForm(isEditMode);  // Clear the form fields based on context

                    // Show different toast messages based on add/update action
                    if (isEditMode) {
                        alert('Spare part updated successfully!'); // Alert for edit
                    } else {
                        alert('New spare part added successfully!'); // Alert for add
                    }
                }
            },
            error: function () {
                showToast('Failed to submit form. Please try again.', 'error');
            },
            complete: function () {
                showLoadingIndicator(false); // Hide loading indicator after completion
            }
        });
    });

    // Handle click event for editing a part
    $(document).on('click', '.editBtn', function () {
        const partId = $(this).data('id');
        $.ajax({
            url: 'spare_parts_crud.php',
            type: 'GET',
            data: { action: 'get', id: partId },
            success: function (response) {
                const part = JSON.parse(response);
                if (part.error) {
                    showToast(part.error, 'error');
                } else {
                    // Populate the form with existing part details for editing
                    populateForm(part);
                    $('#partModalLabel').text('Edit Spare Part');
                    $('#partModal').modal('show');
                }
            },
            error: function () {
                showToast('Failed to fetch part details. Please try again.', 'error');
            }
        });
    });

    // Handle click event for deleting a part
    $(document).on('click', '.deleteBtn', function () {
        const partId = $(this).data('id');
        $('#deleteConfirmationModal').data('id', partId).modal('show'); // Show confirmation modal
    });

    // Confirm deletion of the part
    $('#confirmDeleteBtn').on('click', function () {
        const partId = $('#deleteConfirmationModal').data('id');
        $.ajax({
            url: 'spare_parts_crud.php',
            type: 'POST',
            data: { action: 'delete', id: partId },
            success: function (response) {
                const res = JSON.parse(response);
                if (res.error) {
                    showToast(res.error, 'error');
                } else {
                    loadParts(); // Refresh the parts table after deletion
                    showToast(res.success, 'success'); // Notify user of success
                }
            },
            error: function () {
                showToast('Failed to delete part. Please try again.', 'error');
            },
            complete: function () {
                $('#deleteConfirmationModal').modal('hide'); // Hide the confirmation modal
            }
        });
    });

    // Validate form inputs before submission
    function validateForm(formData) {
        const name = formData.get('name');
        const category = formData.get('category');
        const price = formData.get('price');
        const stock = formData.get('stock');
        const quantity = formData.get('quantity');

        return name && category && !isNaN(price) && !isNaN(stock) && !isNaN(quantity);
    }

    // Populate the form with part details for editing
    function populateForm(part) {
        $('#partId').val(part.id); // Set partId for editing
        $('#name').val(part.name);
        $('#category').val(part.category);
        $('#price').val(part.price);
        $('#stock').val(part.stock);
        $('#quantity').val(part.quantity);
        $('#currentImage').val(part.image); // Keep track of current image
    }

    // Reset the form fields after submission or editing
    function resetForm(isEditMode) {
        $('#partForm')[0].reset(); // Reset the form fields
        $('#partId').val(''); // Clear any hidden inputs for ID
        $('#partModalLabel').text(isEditMode ? 'Edit Spare Part' : 'Add New Spare Part'); // Reset modal title based on context
    }

    // Show toast notifications
    function showToast(message, type) {
        toastr[type](message); // Example using Toastr
    }

    // Show or hide a loading indicator
    function showLoadingIndicator(show) {
        $('#loadingIndicator').toggle(show); // Toggle loading indicator visibility
    }
});
