<?php
// Start session
session_start();

// Check user session
if (!isset($_SESSION['user'])) {
    header("Location: /bd/login.php");
    exit();
}

// Connect to the database
$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$id_serviciu = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id_serviciu) {
    die("ID serviciu invalid.");
}

$sql = "SELECT * FROM Servicii WHERE ID_Serviciu = ?";
$params = [$id_serviciu];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$serviciu = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$serviciu) {
    die("Serviciul nu a fost găsit.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = $_POST['nume_serviciu'];
    $pret = $_POST['pret'];
    $durata = $_POST['durata'];


    if (!empty($nume) && !empty($pret) && !empty($durata) ) {

        $sql = "UPDATE Servicii 
                SET Nume = ?, Pret = ?, Durata = ?
                WHERE ID_Serviciu = ?";
        $params = [$nume, $pret, $durata, $id_serviciu];

        // Execute query
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $error = print_r(sqlsrv_errors(), true);
            $message = "Eroare la actualizare: " . $error;
            $class = "bg-danger";
        } else {
            $_SESSION['message'] = "Serviciul a fost actualizat cu succes!";
            $_SESSION['class'] = "bg-success";
            header("Location: /bd/dashboard/servicii.php");
            exit();
        }
    } else {
        $message = "Toate câmpurile sunt obligatorii!";
        $class = "bg-warning";
    }
}
sqlsrv_close($conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="/bd/styles.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <nav>
        <ul>
            <li><a class="navlink" href="/bd/index.html">Acasa</a></li>
            <li><a class="navlink" href="/bd/servicii.html">Servicii</a></li>
            <li><a class="navlink" href="/bd/despre.html">Despre Noi</a></li>
            <li><a class="navlink" href="/bd/contact.html">Contact</a></li>
        </ul>
        <div class="login-nav">

            <a class="navlink" href="/bd/logout.php">Log Out</a>
        </div>
    </nav>
    <header>
        <h1>Tinker Bell</h1>
        <p>Salon de cosmetică</p>
    </header>
    <div class="welcome-admin">

    </div>
    <div class="container" style="margin-left: 4vw;">
        <div class="row">
            <div class="col-md-2">
                <div class="list-group">
                    <a href="/bd/dashboard.php" class="list-group-item list-group-item-action active">Dashboard</a>
                    <a href="/bd/dashboard/facturi.php" class="list-group-item list-group-item-action">Facturi</a>
                    <a href="/bd/dashboard/clienti.php" class="list-group-item list-group-item-action">Clienți</a>
                    <a href="/bd/dashboard/programari.php" class="list-group-item list-group-item-action">Programări</a>
                    <a href="/bd/dashboard/servicii.php" class="list-group-item list-group-item-action">Servicii</a>
                    <a href="/bd/dashboard/produse.php" class="list-group-item list-group-item-action">Produse</a>
                    <a href="/bd/dashboard/inventar.php" class="list-group-item list-group-item-action">Inventar</a>
                    <?php
                    if ($_SESSION['user'] == "admin") {

                        echo '<a href="/bd/dashboard/angajati.php" class="list-group-item list-group-item-action">Angajati</a>';
                        echo '<a href="/bd/dashboard/salarii.php" class="list-group-item list-group-item-action">Salarii</a>';
                        echo '<a href="/bd/dashboard/rapoarte.php" class="list-group-item list-group-item-action">Rapoarte</a>';
                    } ?>
                </div>
            </div>
            <div class="col-md-10">
                <main role="main" class="col-md-12 px-4">
                    <div class="container" style="margin-top: 20px;">
                        <h2>Modifică serviciul</h2>

                        <!-- Form pentru filtre -->
                        <form method="POST">
                            
                        <div class="row mb-2 mt-4">
                                <label for="nume_serviciu" class="col-sm-2 col-form-label">Nume Serviciu:</label>

                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nume_serviciu" name="nume_serviciu" value = "<?php echo $serviciu['Nume']; ?>" required>
                                        
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="durata" class="col-sm-2 col-form-label">Durata: (HH:MM)</label>
                                <div class="col-sm-2">
                                    <input type="time" class="form-control" id="durata" name="durata" value="<?php echo $serviciu['Durata']->format('H:i'); ?>"
                                        step="300" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="pret" class="col-sm-2 col-form-label">Preț RON:</label>
                                <div class="col-sm-2">
                                    <input type="number" class="form-control" id="pret" name="pret" step="0.01" min="0" value = "<?php echo $serviciu['Pret']; ?>"
                                        required>
                                </div>
                            </div>
                            <div class="row mb-2 mt-4">
                                <div class="col-sm-7">
                                    <button type="submit" class="btn btn-success">Actualizează</button>
                                </div>
                                <div class="col-sm-1">
                                    <a href="/bd/dashboard/servicii.php" class="btn btn-secondary">Renunță</a>
                                </div>

                            </div>
                        </form>



                        <?php
                        if (isset($_SESSION['message'])) {
                            $message = $_SESSION['message'];
                            $class = $_SESSION['class'];
                            unset($_SESSION['message'], $_SESSION['class']);
                        }
                        ?>
                        <div id="responseDiv" class="toast <?php echo $class; ?>"
                            style="display: <?php echo isset($message) ? 'block' : 'none'; ?>;" role="alert"
                            aria-live="assertive" aria-atomic="true">
                            <div class="d-flex">
                                <div class="toast-body flex-grow-1"><?php echo $message; ?></div>
                                <button type="button" class="btn-close p-3" aria-label="Close"
                                    onclick="hideToast()"></button>
                            </div>
                        </div>
                    </div>

                    <script src="/bd/login.js"></script>
            </div>
            </main>
        </div>
    </div>
    </div>
</body>

</html>