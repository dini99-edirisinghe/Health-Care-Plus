<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION["usertype"] != 'a') {
    header("location: ../login.php");
    exit;
}

include("../connection.php");

/* =========================
   HANDLE VIEW / PRINT FIRST
   ========================= */
if (isset($_GET['action'], $_GET['id'])) {

    $action = $_GET['action'];
    $ticket_id = intval($_GET['id']);

    $sql = "SELECT mt.*, 
                   a.apponum, 
                   p.pname, 
                   d.docname, 
                   s.title, 
                   s.scheduledate, 
                   s.scheduletime
            FROM manual_tickets mt
            JOIN appointment a ON mt.appointment_id = a.appoid
            JOIN patient p ON a.pid = p.pid
            JOIN schedule s ON a.scheduleid = s.scheduleid
            JOIN doctor d ON s.docid = d.docid
            WHERE mt.ticket_id = ?";

    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        echo "<script>alert('Ticket not found');window.location='issue-ticket.php';</script>";
        exit;
    }

    $ticket = $result->fetch_assoc();

    /* ========== PRINT VIEW ========== */
    if ($action === 'print') {
?>
<!DOCTYPE html>
<html>
<head>
<title>Print Ticket</title>
<style>
body{
    font-family: Arial, sans-serif;
    background:#fff;
}
.print-box{
    max-width:700px;
    margin:auto;
}
h2{
    text-align:center;
    border-bottom:2px solid #333;
    padding-bottom:10px;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
td,th{
    border:1px solid #333;
    padding:10px;
}
th{
    background:#f4f4f4;
    width:35%;
}
button{
    margin-top:20px;
    padding:10px 20px;
}
@media print{
    button{ display:none; }
}
</style>
</head>
<body onload="window.print()">
<div class="print-box">
<h2>MANUAL TICKET</h2>
<table>
<tr><th>Ticket ID</th><td>OC-TK-<?php echo str_pad($ticket['ticket_id'],4,'0',STR_PAD_LEFT); ?></td></tr>
<tr><th>Appointment</th><td>#<?php echo $ticket['apponum']; ?></td></tr>
<tr><th>Patient</th><td><?php echo $ticket['pname']; ?></td></tr>
<tr><th>Doctor</th><td>Dr. <?php echo $ticket['docname']; ?></td></tr>
<tr><th>Session</th><td><?php echo $ticket['title']; ?></td></tr>
<tr><th>Date & Time</th><td><?php echo $ticket['scheduledate']." @ ".substr($ticket['scheduletime'],0,5); ?></td></tr>
<tr><th>Details</th><td><?php echo nl2br($ticket['ticket_details']); ?></td></tr>
</table>
<button onclick="window.location='appointment.php'">Back</button>
</div>
</body>
</html>
<?php
exit;
}

if ($action === 'view') {
    $popup = true;
}
}

/* ========== INSERT ========== */
if (isset($_POST['issue_ticket'])) {
    $stmt = $database->prepare(
        "INSERT INTO manual_tickets (appointment_id, issued_by, ticket_details) VALUES (?,1,?)"
    );
    $stmt->bind_param("is", $_POST['appointment_id'], $_POST['ticket_details']);
    $stmt->execute();
    $success = "Ticket issued successfully!";
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Manual Tickets</title>

<style>
body{
    font-family:Segoe UI, sans-serif;
    background:linear-gradient(135deg,#e3f2fd,#fce4ec);
    margin:0;
    padding:0;
}
.container{
    max-width:1100px;
    margin:30px auto;
    background:#fff;
    padding:25px;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.15);
}
.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}
.back-btn{
    text-decoration:none;
    background:#6c757d;
    color:#fff;
    padding:10px 18px;
    border-radius:6px;
}
h1{
    margin:0;
    color:#333;
}
.success{
    color:green;
    text-align:center;
    margin-bottom:15px;
}
table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}
th{
    background:#1976d2;
    color:white;
    padding:12px;
}
td{
    padding:12px;
    border-bottom:1px solid #ddd;
}
tr:hover{
    background:#f1f7ff;
}
.action-btn{
    padding:6px 12px;
    border-radius:5px;
    text-decoration:none;
    color:#fff;
    margin:0 3px;
}
.view{ background:#4caf50; }
.print{ background:#ff9800; }

/* POPUP */
.overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.6);
}
.popup{
    background:#fff;
    width:60%;
    margin:8% auto;
    padding:25px;
    border-radius:12px;
}
.close{
    float:right;
    font-size:26px;
    text-decoration:none;
    color:#333;
}
</style>
</head>

<body>

<div class="container">

<div class="header">
<h1>Manual Tickets</h1>
<a href="appointment.php" class="back-btn">← Back</a>
</div>

<?php if (!empty($success)) echo "<p class='success'>$success</p>"; ?>

<table>
<tr>
<th>Ticket</th>
<th>Appointment</th>
<th>Issued</th>
<th>Actions</th>
</tr>

<?php
$q = "SELECT mt.ticket_id, a.apponum, p.pname, d.docname, mt.issued_date
      FROM manual_tickets mt
      JOIN appointment a ON mt.appointment_id=a.appoid
      JOIN patient p ON a.pid=p.pid
      JOIN schedule s ON a.scheduleid=s.scheduleid
      JOIN doctor d ON s.docid=d.docid
      ORDER BY mt.issued_date DESC";

$r = $database->query($q);

while($row = $r->fetch_assoc()){
echo "
<tr>
<td>OC-TK-".str_pad($row['ticket_id'],4,'0',STR_PAD_LEFT)."</td>
<td>#{$row['apponum']} - {$row['pname']} (Dr. {$row['docname']})</td>
<td>{$row['issued_date']}</td>
<td>
<a class='action-btn view' href='?action=view&id={$row['ticket_id']}'>View</a>
<a class='action-btn print' href='?action=print&id={$row['ticket_id']}'>Print</a>
</td>
</tr>";
}
?>
</table>
</div>

<?php if (!empty($popup)): ?>
<div class="overlay">
<div class="popup">
<a class="close" href="issue-ticket.php">&times;</a>
<h2>Ticket Details</h2>
<p><b>Ticket ID:</b> OC-TK-<?php echo str_pad($ticket['ticket_id'],4,'0',STR_PAD_LEFT); ?></p>
<p><b>Patient:</b> <?php echo $ticket['pname']; ?></p>
<p><b>Doctor:</b> Dr. <?php echo $ticket['docname']; ?></p>
<p><b>Session:</b> <?php echo $ticket['title']; ?></p>
<p><b>Date & Time:</b> <?php echo $ticket['scheduledate']." @ ".substr($ticket['scheduletime'],0,5); ?></p>
<p><b>Details:</b><br><?php echo nl2br($ticket['ticket_details']); ?></p>
<br>
<a href="issue-ticket.php" class="back-btn">Close</a>
</div>
</div>
<?php endif; ?>

</body>
</html>
