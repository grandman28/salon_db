<?php
session_start();

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($_SESSION['user'] != "admin") {
    header("Location: /bd/login.php");
    $_SESSION['message'] = "Nu aveți permisiune să accesați această pagină";
    $_SESSION['class'] = "ng-danger";
    exit();
}

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Verificăm dacă ID-ul facturii este trecut în URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_factura = intval($_GET['id']);


    if ($id_factura > 0) {

        $deleteProgramariQuery = "UPDATE Programari SET ID_Factura = NULL WHERE ID_Factura = ?";
        $stmtProgramari = sqlsrv_query($conn, $deleteProgramariQuery, array($id_factura));
        if ($stmtProgramari === false) {
            $_SESSION['message'] = "Nu s-a putut șterge referința la factură din programări.";
            $_SESSION['class'] = "bg-danger";
            header("Location: /bd/dashboard/facturi.php");
            exit();
        }

        // Ștergem factura
        $deleteFacturaQuery = "DELETE FROM Facturi WHERE ID_Factura = ?";
        $stmtFactura = sqlsrv_query($conn, $deleteFacturaQuery, array($id_factura));





        if ($stmtFactura === false) {
            $_SESSION['message'] = "Nu s-a putut șterge înregistrarea.";
            $_SESSION['class'] = "bg-danger";
            header("Location: /bd/dashboard/facturi.php");
            sqlsrv_close($conn);
            exit();
        }



        $_SESSION['message'] = "Factura a fost ștearsă cu succes!";
        $_SESSION['class'] = "bg-success";
        header("Location: /bd/dashboard/facturi.php");
        sqlsrv_close($conn);
        exit();
    } else {
        $_SESSION['message'] = "ID-ul facturii nu este valid.";
        $_SESSION['class'] = "bg-danger";
        header("Location: /bd/dashboard/facturi.php");
        sqlsrv_close($conn);
        exit();
    }
} else {
    $_SESSION['message'] = "ID-ul facturii nu a fost furnizat sau este invalid.";
    $_SESSION['class'] = "bg-danger";
    header("Location: /bd/dashboard/facturi.php");
    sqlsrv_close($conn);
    exit();
}

sqlsrv_close($conn);
?>