<?php
// Start session
session_start();

// Check user session
if (!isset($_SESSION['user'])) {
    header("Location: /bd/login.php");
    exit();
}

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numeprodus = $_POST['nume_produs'];
    $pretunitar = $_POST['pret_unitar'];
    $cantitate = $_POST['cantitate'];
    $pretkg = $_POST['pret_kg'] ? $_POST['pret_kg'] : null;

    if (!empty($numeprodus) && !empty($pretunitar)) {
        $sql = "INSERT INTO Produse (Nume, Pret_Unitar, [Cantitate_flacon(ml)], [Pret/Kg] ) VALUES (?, ?, ?, ?)";
        $params = [$numeprodus, $pretunitar, $cantitate, $pretkg];

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $error = print_r(sqlsrv_errors(), true);
            $message = "Eroare la adaugare" . $error;
            $class = "bg-danger";
        } else {
            $_SESSION['message'] = "Produsul a fost adăugat cu succes!";
            $_SESSION['class'] = "bg-success";
            $message = "Produsul a fost adăugat cu succes!";
            $class = "bg-success";

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
                        <h2>Adauga Produse</h2>

                        <!-- Form pentru filtre -->
                        <form method="POST">

                            <div class="row mb-2 mt-4">
                                <label for="nume_produs" class="col-sm-2 col-form-label">Nume produs:</label>

                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nume_produs" name="nume_produs"
                                        required>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="pret_unitar" class="col-sm-2 col-form-label">Preț unitar RON:</label>
                                <div class="col-sm-2">
                                    <input type="number" class="form-control" id="pret_unitar" name="pret_unitar"
                                        step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <label for="cantitate" class="col-sm-2 col-form-label">Cantitate flacon(ml):</label>
                                <div class="col-sm-2">
                                    <input type="number" class="form-control" id="cantitate" name="cantitate"
                                        step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <label for="pret_kg" class="col-sm-2 col-form-label">Preț/Kg RON:</label>
                                <div class="col-sm-2">
                                    <input type="number" class="form-control" id="pret_kg" name="pret_kg" step="0.01"
                                        min="0">
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-success">Adaugă</button>
                                </div>
                                <div class="col-sm-1">
                                    <a href="/bd/dashboard/produse.php" class="btn btn-secondary">Înapoi</a>
                                </div>
                                <div class="col-sm-1">
                                    <input type="reset" class="btn btn-secondary" value="Reseteaza">
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


                    <script>
document.getElementById('pret_kg').addEventListener('input', function () {
    const pretUnitar = parseFloat(document.getElementById('pret_unitar').value);
    const pretKg = parseFloat(this.value);
    if (!isNaN(pretUnitar) && !isNaN(pretKg) && pretKg !== 0) {
        document.getElementById('cantitate').value = (1000 * pretUnitar / pretKg).toFixed(2);
    }
});

document.getElementById('cantitate').addEventListener('input', function () {
    const pretUnitar = parseFloat(document.getElementById('pret_unitar').value);
    const cantitate = parseFloat(this.value);
    if (!isNaN(pretUnitar) && !isNaN(cantitate) && cantitate !== 0) {
        document.getElementById('pret_kg').value = (1000 * pretUnitar / cantitate).toFixed(2);
    }
});

    function showToast() {
                                const params = getQueryParams();
                                if (params.message) {
                                    const responseDiv = document.getElementById("responseDiv");
                                    const toastMessage = document.getElementById("toastMessage");
                                    toastMessage.textContent = params.message;
                                    responseDiv.classList.add(params.class);
                                    responseDiv.style.display = "block";
                                }
                            }
    
    function hideToast() {
                                const responseDiv = document.getElementById("responseDiv");
                                responseDiv.style.display = "none";
                            }
    
    window.onload = showToast;
                    </script>
            </div>
            </main>
        </div>
    </div>
    </div>
</body>

</html>