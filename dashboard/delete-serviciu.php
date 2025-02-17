<?php
session_start();

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Verificăm dacă ID-ul facturii este trecut în URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_serviciu = intval($_GET['id']); // Preluăm ID-ul facturii din URL

    // Asigură-te că ID-ul este valid
    if ($id_serviciu > 0) {
        // Șterge factura
        $deleteQuery = "DELETE FROM Servicii WHERE ID_Serviciu = ?";
        $stmt = sqlsrv_query($conn, $deleteQuery, array($id_serviciu));

        if ($stmt === false) {
            $_SESSION['message'] = "Nu s-a putut șterge înregistrarea.";
            $_SESSION['class'] = "bg-danger";
            header("Location: /bd/dashboard/servicii.php");
            sqlsrv_close($conn);
            exit(); // Asigură-te că nu continuă execuția codului după redirect
        }

        // Mesaj de succes
        $_SESSION['message'] = "Serviciul a fost șters cu succes!";
        $_SESSION['class'] = "bg-success";
        header("Location: /bd/dashboard/servicii.php");
        sqlsrv_close($conn);
        exit(); // Asigură-te că nu continuă execuția codului după redirect
    } else {
        $_SESSION['message'] = "ID-ul serviciului nu este valid.";
        $_SESSION['class'] = "bg-danger";
        header("Location: /bd/dashboard/servicii.php");
        sqlsrv_close($conn);
        exit();
    }
} else {
    $_SESSION['message'] = "ID-ul serviciului nu a fost furnizat sau este invalid.";
    $_SESSION['class'] = "bg-danger";
    header("Location: /bd/dashboard/servicii.php");
    sqlsrv_close($conn);
    exit();
}

sqlsrv_close($conn);
?>
