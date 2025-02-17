<?php
session_start();

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_factura = intval($_GET['id']);


    if ($id_factura > 0) {
        $check = "SELECT COUNT(ID_Factura) FROM Facturi WHERE ID_Client = ? AND ID_factura is not null";
        $stmt = sqlsrv_query($conn, $check, array($id_factura));
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
        $count = $row[0];
        if ($count > 0) {
            $_SESSION['message'] = "Nu se poate șterge clientul deoarece are facturi asociate.";
            $_SESSION['class'] = "bg-danger";
            header("Location: /bd/dashboard/clienti.php");
            sqlsrv_close($conn);
            exit();
        }
        $deleteQuery = "DELETE FROM Clienti WHERE ID_Client = ?";
        $stmt = sqlsrv_query($conn, $deleteQuery, array($id_factura));

        if ($stmt === false) {
            $_SESSION['message'] = "Nu s-a putut șterge clientul.";
            $_SESSION['class'] = "bg-danger";
            header("Location: /bd/dashboard/clienti.php");
            sqlsrv_close($conn);
            exit();
        }

        $_SESSION['message'] = "Clientul a fost șters cu succes!";
        $_SESSION['class'] = "bg-success";
        header("Location: /bd/dashboard/clienti.php");
        sqlsrv_close($conn);
        exit();
    } else {
        $_SESSION['message'] = "ID-ul facturii nu este valid.";
        $_SESSION['class'] = "bg-danger";
        header("Location: /bd/dashboard/clienti.php");
        sqlsrv_close($conn);
        exit();
    }
} else {
    $_SESSION['message'] = "ID-ul clientului nu a fost furnizat sau este invalid.";
    $_SESSION['class'] = "bg-danger";
    header("Location: /bd/dashboard/clienti.php");
    sqlsrv_close($conn);
    exit();
}

sqlsrv_close($conn);
?>
