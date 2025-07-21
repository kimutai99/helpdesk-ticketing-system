public function showTickets() {
// Turn off error display to prevent breaking JSON
error_reporting(0);
ini_set('display_errors', 0);

$sqlWhere = '';
if (!isset($_SESSION["admin"])) {
$sqlWhere .= " WHERE t.user = '" . $this->dbConnect->real_escape_string($_SESSION["userid"]) . "' ";
if (!empty($_POST["search"]["value"])) {
$sqlWhere .= " AND ";
}
} else if (!empty($_POST["search"]["value"])) {
$sqlWhere .= " WHERE ";
}

$time = new Time;

$sqlQuery = "SELECT t.id, t.uniqid, t.title, t.init_msg as message, t.date, t.last_reply, t.resolved,
u.name as creater, d.name as department, u.user_type, t.user, t.user_read, t.admin_read
FROM hd_tickets t
LEFT JOIN hd_users u ON t.user = u.id
LEFT JOIN hd_departments d ON t.department = d.id $sqlWhere";

if (!empty($_POST["search"]["value"])) {
$search = $this->dbConnect->real_escape_string($_POST["search"]["value"]);
$sqlQuery .= " (t.uniqid LIKE '%$search%' OR t.title LIKE '%$search%' OR t.resolved LIKE '%$search%' OR t.last_reply
LIKE '%$search%') ";
}

// Get total filtered records count (for DataTables)
$sqlCount = "SELECT COUNT(*) as total FROM hd_tickets t
LEFT JOIN hd_users u ON t.user = u.id
LEFT JOIN hd_departments d ON t.department = d.id $sqlWhere";
if (!empty($_POST["search"]["value"])) {
$sqlCount .= " (t.uniqid LIKE '%$search%' OR t.title LIKE '%$search%' OR t.resolved LIKE '%$search%' OR t.last_reply
LIKE '%$search%') ";
}
$countResult = $this->dbConnect->query($sqlCount);
$totalFiltered = ($countResult) ? $countResult->fetch_assoc()['total'] : 0;

if (!empty($_POST["order"])) {
$columns = ['t.id', 't.uniqid', 't.title', 'd.name', 'u.name', 't.date', 't.resolved'];
$colIndex = intval($_POST['order'][0]['column']);
$colName = isset($columns[$colIndex]) ? $columns[$colIndex] : 't.id';
$dir = ($_POST['order'][0]['dir'] === 'asc') ? 'ASC' : 'DESC';
$sqlQuery .= " ORDER BY $colName $dir ";
} else {
$sqlQuery .= " ORDER BY t.id DESC ";
}

if ($_POST["length"] != -1) {
$start = intval($_POST['start']);
$length = intval($_POST['length']);
$sqlQuery .= " LIMIT $start, $length";
}

$result = $this->dbConnect->query($sqlQuery);

if (!$result) {
// Return JSON error message correctly formatted
echo json_encode([
"draw" => intval($_POST["draw"]),
"recordsTotal" => 0,
"recordsFiltered" => 0,
"data" => [],
"error" => $this->dbConnect->error
]);
exit;
}

$ticketData = [];
while ($ticket = $result->fetch_assoc()) {
$status = $ticket['resolved'] == 0
? '<span class="label label-success">Open</span>'
: '<span class="label label-danger">Closed</span>';

$title = $ticket['title'];
if (
(isset($_SESSION["admin"]) && !$ticket['admin_read'] && $ticket['last_reply'] != $_SESSION["userid"]) ||
(!isset($_SESSION["admin"]) && !$ticket['user_read'] && $ticket['last_reply'] != $ticket['user'])
) {
$title = $this->getRepliedTitle($title);
}

$disabled = !isset($_SESSION["admin"]) ? 'disabled' : '';

$ticketRows = [
$ticket['id'],
$ticket['uniqid'],
$title,
$ticket['department'],
$ticket['creater'],
$time->ago($ticket['date']),
$status,
'<a href="view_ticket.php?id=' . htmlspecialchars($ticket[" uniqid"])
  . '" class="btn btn-success btn-xs update">View Ticket</a>' , '<button type="button" name="update" id="' .
  intval($ticket["id"]) . '" class="btn btn-warning btn-xs update" ' . $disabled . '>Edit</button>'
  , '<button type="button" name="delete" id="' . intval($ticket["id"]) . '" class="btn btn-danger btn-xs delete" ' .
  $disabled . '>Close</button>' ]; $ticketData[]=$ticketRows; } // Get total records without filtering (for DataTables)
  $sqlTotal="SELECT COUNT(*) as total FROM hd_tickets" ; $totalResult=$this->dbConnect->query($sqlTotal);
  $totalRecords = ($totalResult) ? $totalResult->fetch_assoc()['total'] : 0;

  $output = [
  "draw" => intval($_POST["draw"]),
  "recordsTotal" => intval($totalRecords),
  "recordsFiltered" => intval($totalFiltered),
  "data" => $ticketData
  ];

  header('Content-Type: application/json');
  echo json_encode($output);
  exit;
  }