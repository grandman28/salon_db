<?php
session_start();

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

$id_client = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$id_client) {
    die("ID client invalid.");
}

$sql = "SELECT * FROM Clienti WHERE ID_Client = ?";
$params = [$id_client];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$client = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$client) {
    die("Clientul nu a fost găsit.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = $_POST['nume'];
    $prenume = $_POST['prenume'];
    $telefon = $_POST['telefon'];
    $email = $_POST['email'] ? $_POST['email'] : null;
    $dataNasterii = $_POST['dataNasterii'] ? $_POST['dataNasterii'] : null;
    $strada = $_POST['strada'] ? $_POST['strada'] : null;
    $numar = $_POST['numar'] ? $_POST['numar'] : null;
    $bloc = $_POST['bloc'] ? $_POST['bloc'] : null;
    $localitate = $_POST['localitate'] ? $_POST['localitate'] : null;
    $judet = $_POST['judet'] ? $_POST['judet'] : null;
    $promotii = isset($_POST['promotii']) ? $_POST['promotii'] : "Nu";
    $sex = $_POST['sex'] ? $_POST['sex'] : null;

    if (!empty($nume) && !empty($prenume) && !empty($telefon) && !empty($promotii)) {
        $sql = "UPDATE Clienti 
                SET Nume = ?, Prenume = ?, Telefon = ?, Email = ?, DataNasterii = ?, 
                    Strada = ?, Numar = ?, Bloc = ?, Localitate = ?, Judet = ?, Promotii = ?, Sex = ?
                WHERE ID_Client = ?";
        $params = [$nume, $prenume, $telefon, $email, $dataNasterii, $strada, $numar, $bloc, $localitate, $judet, $promotii, $sex, $id_client];

        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt === false) {
            $error = print_r(sqlsrv_errors(), true);
            $message = "Eroare la actualizare: " . $error;
            $class = "bg-danger";
        } else {
            $_SESSION['message'] = "Clientul a fost actualizat cu succes!";
            $_SESSION['class'] = "bg-success";
            header("Location: /bd/dashboard/clienti.php");
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
                        <h2>Modifică clientul</h2>

                        <!-- Form pentru filtre -->
                        <form method="POST">
                            <div class="row mb-2 mt-4">
                                <label for="nume" class="col-sm-2 col-form-label">Nume Client:</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="nume" name="nume"
                                        value="<?= htmlspecialchars($client['Nume']) ?>" required>
                                </div>
                            </div>
                            <div class="row mb-2 mt-4">
                                <label for="prenume" class="col-sm-2 col-form-label">Prenume Client:</label>
                                <div class="col-sm-6">
                                    <input type="text" class="form-control" id="prenume" name="prenume"
                                        value="<?= htmlspecialchars($client['Prenume']) ?>" required>
                                </div>
                            </div>
                            <div class="row mb-2 mt-4">
                                <label for="telefon" class="col-sm-1 col-form-label">Telefon:</label>
                                <div class="col-sm-3">
                                    <input type="tel" class="form-control" id="telefon" name="telefon"
                                        value="<?= htmlspecialchars($client['Telefon']) ?>" required>
                                </div>
                            </div>
                            <div class="row mb-2 mt-4">
                                <label for="email" class="col-sm-1 col-form-label">Email:</label>
                                <div class="col-sm-3">
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?= htmlspecialchars($client['Email']) ?>">
                                </div>
                            </div>
                            <div class="row mb-2 mt-4">
                                <label for="dataNasterii" class="col-sm-2 col-form-label">Data Nașterii:</label>
                                <div class="col-sm-2">
                                    <input type="date" class="form-control" id="dataNasterii" name="dataNasterii"
                                        value="<?= $client['DataNasterii'] === null ? '' : htmlspecialchars($client['DataNasterii']->format("d:m:Y")) ?>">
                                </div>
                                <label for="promotii" class="col-sm-1 col-form-label">Promotii:</label>
                                <div class="col-sm-1">
                                    <input type="checkbox" id="promotii" name="promotii" value="Da"
                                        <?= $client['Promotii'] === 'Da' ? 'checked' : '' ?>>
                                </div>
                            </div>
                            <div class="row mb-2 mt-4">
                                <label for="strada" class="col-sm-1 col-form-label">Strada:</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" id="strada" name="strada"
                                        value="<?= htmlspecialchars($client['Strada']) ?>">
                                </div>
                                <label for="numar" class="col-sm-1 col-form-label">Numar:</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control" id="numar" name="numar"
                                        value="<?= htmlspecialchars($client['Numar']) ?>">
                                </div>
                                <label for="bloc" class="col-sm-1 col-form-label">Bloc:</label>
                                <div class="col-sm-1">
                                    <input type="text" class="form-control" id="bloc" name="bloc"
                                        value="<?= htmlspecialchars($client['Bloc']) ?>">
                                </div>
                            </div>
                            <div class="row mb-2 mt-4">
                                <label for="localitate" class="col-sm-1 col-form-label">Localitate:</label>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control" id="localitate" name="localitate"
                                        value="<?= htmlspecialchars($client['Localitate']) ?>">
                                </div>
                                <label for="judet" class="col-sm-1 col-form-label">Judet:</label>
                                <div class="col-sm-2">
                                    <input type="text" class="form-control" id="judet" name="judet"
                                        value="<?= htmlspecialchars($client['Judet']) ?>">
                                </div>
                                <label for="sex" class="col-sm-1 col-form-label">Sex:</label>
                                <select class="form-select col-sm-2 sex" name="sex" id="sex">
                                    <option value="M" <?= $client['Sex'] === 'M' ? 'selected' : '' ?>>Masculin</option>
                                    <option value="F" <?= $client['Sex'] === 'F' ? 'selected' : '' ?>>Feminin</option>
                                </select>
                            </div>



                            <div class="row mb-2 mt-4">
                                <div class="col-sm-7">
                                    <button type="submit" class="btn btn-success">Actualizează</button>
                                </div>
                                <div class="col-sm-1">
                                    <a href="/bd/dashboard/clienti.php" class="btn btn-secondary">Renunță</a>
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