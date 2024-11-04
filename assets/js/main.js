$(document).ready(function() {
    $("#addPartForm").on("submit", function(e) {
        e.preventDefault();

        $.ajax({
            url: "add_part.php",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                alert("Part added successfully!");
                location.reload();
            },
            error: function() {
                alert("Error adding part.");
            }
        });
    });
});
