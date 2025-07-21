public function showTickets() {
$sqlWhere = '';
if (!isset($_SESSION["admin"])) {
$sqlWhere .= " WHERE t.user = '" . $_SESSION["userid"] . "' ";
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

$result = mysqli_query($this->dbConnect, $sqlQuery);
if (!$result) {
echo json_encode([
"draw" => intval($_POST["draw"]),
"recordsTotal" => 0,
"recordsFiltered" => 0,
"data" => [],
"error" => mysqli_error($this->dbConnect)
]);
return;
}

$ticketData = [];
while ($ticket = mysqli_fetch_assoc($result)) {
$ticketRows = [];

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

$ticketRows[] = $ticket['id'];
$ticketRows[] = $ticket['uniqid'];
$ticketRows[] = $title;
$ticketRows[] = $ticket['department'];
$ticketRows[] = $ticket['creater'];
$ticketRows[] = $time->ago($ticket['date']);
$ticketRows[] = $status;
$ticketRows[] = '<a href="view_ticket.php?id=' . $ticket[" uniqid"]
  . '" class="btn btn-success btn-xs update">View Ticket</a>' ; $ticketRows[]='<button type="button" name="update" id="'
  . $ticket["id"] . '" class="btn btn-warning btn-xs update" ' . $disabled . '>Edit</button>' ;
  $ticketRows[]='<button type="button" name="delete" id="' . $ticket["id"] . '" class="btn btn-danger btn-xs delete" ' .
  $disabled . '>Close</button>' ; $ticketData[]=$ticketRows; } $output=[ "draw"=> intval($_POST["draw"]),
  "recordsTotal" => count($ticketData),
  "recordsFiltered" => count($ticketData),
  "data" => $ticketData
  ];

  header('Content-Type: application/json'); // Recommended to ensure proper JSON response
  echo json_encode($output);
  exit;
  }