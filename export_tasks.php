<?php
ob_start(); // Start output buffering
session_start();
require_once "config.php";

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    // Check if user is logged in
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: index.php");
        exit;
    }

    $format = isset($_GET['format']) ? $_GET['format'] : 'csv';
    $user_id = $_SESSION["id"];

    // Fetch tasks
    $sql = "SELECT title, description, priority, status, due_date, created_at 
            FROM tasks WHERE user_id = ? ORDER BY created_at DESC";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $tasks = [];
    while($row = mysqli_fetch_assoc($result)) {
        $tasks[] = $row;
    }

    // Clear any previous output
    ob_clean();

    if($format === 'csv') {
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tasks_export_'.date('Y-m-d').'.csv"');
        
        // Create CSV file
        $output = fopen('php://output', 'w');
        
        // Add headers
        fputcsv($output, ['Title', 'Description', 'Priority', 'Status', 'Due Date', 'Created At']);
        
        // Add data
        foreach($tasks as $task) {
            fputcsv($output, [
                $task['title'],
                $task['description'],
                $task['priority'],
                $task['status'],
                $task['due_date'],
                $task['created_at']
            ]);
        }
        
        fclose($output);
    } elseif($format === 'pdf') {
        require_once('vendor/tecnickcom/tcpdf/tcpdf.php');

        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('TaskFlow');
        $pdf->SetAuthor('TaskFlow User');
        $pdf->SetTitle('Tasks Export');

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(15, 15, 15);

        // Add a page
        $pdf->AddPage();

        // Set font
        $pdf->SetFont('helvetica', '', 12);

        // Title
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'TaskFlow - Tasks Export', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln(10);

        // Create the table content
        $html = '<table border="1" cellpadding="5">
                    <thead>
                        <tr style="background-color: #f8f9fa;">
                            <th style="font-weight: bold;">Title</th>
                            <th style="font-weight: bold;">Priority</th>
                            <th style="font-weight: bold;">Status</th>
                            <th style="font-weight: bold;">Due Date</th>
                        </tr>
                    </thead>
                    <tbody>';

        foreach($tasks as $task) {
            // Format the date
            $due_date = $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date';
            
            $html .= '<tr>
                        <td>'.htmlspecialchars($task['title']).'</td>
                        <td>'.ucfirst(htmlspecialchars($task['priority'])).'</td>
                        <td>'.ucfirst(htmlspecialchars($task['status'])).'</td>
                        <td>'.$due_date.'</td>
                    </tr>';
        }

        $html .= '</tbody></table>';

        // Print the table
        $pdf->writeHTML($html, true, false, true, false, '');

        // Close and output PDF document
        $pdf->Output('tasks_export_'.date('Y-m-d').'.pdf', 'D');
    }

} catch (Exception $e) {
    // Log error
    error_log("Export error: " . $e->getMessage());
    
    // Clear output buffer
    ob_clean();
    
    // Redirect back with error
    header("Location: tasks.php?error=export_failed");
    exit;
}

mysqli_close($conn);
?> 