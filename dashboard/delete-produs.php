<?php
session_start();

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_produs = intval($_GET['id']); 
    if ($id_produs > 0) {
        $deleteQuery = "DELETE FROM Produse WHERE ID_Produs = ?";
        $stmt = sqlsrv_query($conn, $deleteQuery, array($id_produs));

        if ($stmt === false) {
            $_SESSION['message'] = "Nu s-a putut șterge înregistrarea.";
            $_SESSION['class'] = "bg-danger";
            header("Location: /bd/dashboard/produse.php");
            sqlsrv_close($conn);
            exit();
        }

       
        $_SESSION['message'] = "Produsul a fost șters cu succes!";
        $_SESSION['class'] = "bg-success";
        header("Location: /bd/dashboard/produse.php");
        sqlsrv_close($conn);
        exit();
    } else {
        $_SESSION['message'] = "ID-ul produsului nu este valid.";
        $_SESSION['class'] = "bg-danger";
        header("Location: /bd/dashboard/produse.php");
        sqlsrv_close($conn);
        exit();
    }
} else {
    $_SESSION['message'] = "ID-ul produsului nu a fost furnizat sau este invalid.";
    $_SESSION['class'] = "bg-danger";
    header("Location: /bd/dashboard/produse.php");
    sqlsrv_close($conn);
    exit();
}

sqlsrv_close($conn);
?>
