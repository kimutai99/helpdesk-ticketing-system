<?php
include 'init.php';

header('Content-Type: application/json'); // Ensure JSON output

try {
    if (!empty($_POST['action'])) {
        switch ($_POST['action']) {
            case 'auth':
                $users->login();
                break;

            case 'listTicket':
                $tickets->showTickets(); // Must echo valid JSON internally
                break;

            case 'createTicket':
                $tickets->createTicket();
                echo json_encode(['status' => 'success']);
                break;

            case 'getTicketDetails':
                $tickets->getTicketDetails(); // Should echo JSON
                break;

            case 'updateTicket':
                $tickets->updateTicket();
                echo json_encode(['status' => 'updated']);
                break;

            case 'closeTicket':
                $tickets->closeTicket();
                echo json_encode(['status' => 'closed']);
                break;

            case 'saveTicketReplies':
                $tickets->saveTicketReplies();
                echo json_encode(['status' => 'replied']);
                break;

            default:
                echo json_encode(['error' => 'Invalid action']);
                break;
        }
    } else {
        echo json_encode(['error' => 'No action received']);
    }
} catch (Exception $e) {
    // Debugging support — send back error message to client
    echo json_encode([
        'error' => 'Server Error',
        'message' => $e->getMessage()
    ]);
}