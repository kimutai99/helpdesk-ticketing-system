$(document).ready(function() {
  $('#ticketReply').on('submit', function(e) {
    e.preventDefault(); // prevent default form submission

    var formData = $(this).serialize();

    $.ajax({
      url: 'action.php',  // your handler script where 'saveTicketReplies' action is handled
      type: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response) {
        if (response.status === 'replied') {
          alert('Reply added successfully!');
          // Optionally, you can clear the textarea
          $('#message').val('');
          // Reload the page or dynamically append the new reply (more advanced)
          location.reload();
        } else if (response.error) {
          alert('Error: ' + response.error);
        }
      },
      error: function(xhr, status, error) {
        alert('AJAX error: ' + error);
      }
    });
  });
});
