<?php
ob_start(); 
session_start();
require_once "config.php";


error_reporting(E_ALL);
ini_set('display_errors', 0);

try {

    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
        header("location: index.php");
        exit;
    }

    $format = isset($_GET['format']) ? $_GET['format'] : 'csv';
    $user_id = $_SESSION["id"];


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


    ob_clean();

    if($format === 'csv') {

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="tasks_export_'.date('Y-m-d').'.csv"');
        

        $output = fopen('php://output', 'w');
        

        fputcsv($output, ['Title', 'Description', 'Priority', 'Status', 'Due Date', 'Created At']);
        

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


        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);


        $pdf->SetCreator('TaskFlow');
        $pdf->SetAuthor('TaskFlow User');
        $pdf->SetTitle('Tasks Export');


        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);


        $pdf->SetMargins(15, 15, 15);


        $pdf->AddPage();


        $pdf->SetFont('helvetica', '', 12);


        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'TaskFlow - Tasks Export', 0, 1, 'C');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->Ln(10);


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

            $due_date = $task['due_date'] ? date('M d, Y', strtotime($task['due_date'])) : 'No due date';
            
            $html .= '<tr>
                        <td>'.htmlspecialchars($task['title']).'</td>
                        <td>'.ucfirst(htmlspecialchars($task['priority'])).'</td>
                        <td>'.ucfirst(htmlspecialchars($task['status'])).'</td>
                        <td>'.$due_date.'</td>
                    </tr>';
        }

        $html .= '</tbody></table>';


        $pdf->writeHTML($html, true, false, true, false, '');


        $pdf->Output('tasks_export_'.date('Y-m-d').'.pdf', 'D');
    }

} catch (Exception $e) {

    error_log("Export error: " . $e->getMessage());
    

    ob_clean();
    

    header("Location: tasks.php?error=export_failed");
    exit;
}

mysqli_close($conn);
?> 