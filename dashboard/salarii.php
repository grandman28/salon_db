<?php
session_start();

// Verifică sesiunea utilizatorului
if (!isset($_SESSION['user'])) {
    header("Location: /bd/login.php");
    exit();
}
if ($_SESSION['user'] != "admin") {
    header("Location: /bd/login.php");
    $_SESSION['message'] = "Nu aveți permisiune să accesați această pagină";
    $_SESSION['class'] = "bg-danger";
    exit();
}

// Conexiunea la baza de date
$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

// Variabile pentru filtrare
$order = isset($_GET['order']) ? $_GET['order'] : 'Data';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
$nume_angajat = isset($_GET['nume_angajat']) ? $_GET['nume_angajat'] : '';
$prenume_angajat = isset($_GET['prenume_angajat']) ? $_GET['prenume_angajat'] : '';
$ID_Salariu = isset($_GET['ID_Salariu']) ? $_GET['ID_Salariu'] : '';
$data_min = isset($_GET['data_min']) ? $_GET['data_min'] : '';
$data_max = isset($_GET['data_max']) ? $_GET['data_max'] : '';
$functie = isset($_GET['functie']) ? $_GET['functie'] : '';
$salariuBazaMin = isset($_GET['salariuBazaMin']) ? $_GET['salariuBazaMin'] : '';
$salariuBazaMax = isset($_GET['salariuBazaMax']) ? $_GET['salariuBazaMax'] : '';
$bonusMin = isset($_GET['bonusMin']) ? $_GET['bonusMin'] : '';
$bonusMax = isset($_GET['bonusMax']) ? $_GET['bonusMax'] : '';
$sumamin = isset($_GET['sumamin']) ? $_GET['sumamin'] : '';
$sumamax = isset($_GET['sumamax']) ? $_GET['sumamax'] : '';
// Construiește interogarea SQL
$sql = "SELECT TOP 50 S.ID_Salariu, A.Nume + ' ' + A.Prenume AS NumeAngajat, A.Functie, S.[Salariu de baza], S.[Bonus %], S.Suma, S.Data
        FROM Angajati A
        JOIN Salarii S ON A.ID_Angajat = S.ID_Angajat";

$conditions = [];
$params = [];

// Adaugă filtre în interogare
if (!empty($nume_angajat)) {
    $conditions[] = "A.Nume LIKE ?";
    $params[] = "%" . $nume_angajat . "%";
}
if (!empty($prenume_angajat)) {
    $conditions[] = "A.Prenume LIKE ?";
    $params[] = "%" . $prenume_angajat . "%";
}
if (!empty($functie)) {
    $conditions[] = "A.Functie LIKE ?";
    $params[] = "%" . $functie . "%";
}
if (!empty($ID_Salariu)) {
    $conditions[] = "S.ID_Salariu = ?";
    $params[] = $ID_Salariu;
}
if (!empty($salariuBazaMin)) {
    $conditions[] = "S.[Salariu de baza] >= ?";
    $params[] = $salariuBazaMin;
}
if (!empty($salariuBazaMax)) {
    $conditions[] = "S.[Salariu de baza] <= ?";
    $params[] = $salariuBazaMax;
}
if (!empty($bonusMin)) {
    $conditions[] = "S.[Bonus %] >= ?";
    $params[] = $bonusMin;
}
if (!empty($bonusMax)) {
    $conditions[] = "S.[Bonus %] <= ?";
    $params[] = $bonusMax;
}

if (!empty($sumamin)) {
    $conditions[] = "S.Suma = ?";
    $params[] = $sumamax;
}
if (!empty($sumamax)) {
    $conditions[] = "S.Suma = ?";
    $params[] = $sumamax;
}

// Adaugă condițiile în SQL
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY " . $order . " " . $direction;

// Execută interogarea
$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

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
                  }?>
                </div>
            </div>
            <div class="col-md-10">
                <main role="main" class="col-md-12 px-4">
                    <div class="container" style="margin-top: 20px;">
                        <h2>Salarii</h2>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="/bd/dashboard/add-salariu.php" class="btn btn-success">Adauga salariu</a>
                        </div>

                        <!-- Form pentru filtre -->
                        <form method="GET" class="mb-4">

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <label for="nume_angajat">Nume Angajat</label>
                                    <input type="text" class="form-control" name="nume_angajat" id="nume_angajat"
                                        value="<?php echo htmlspecialchars($nume_angajat); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="prenume_angajat">Prenume Angajat</label>
                                    <input type="text" class="form-control" name="prenume_angajat" id="prenume_angajat"
                                        value="<?php echo htmlspecialchars($prenume_angajat); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="functie">Functie</label>
                                    <input type="text" class="form-control" name="functie" id="functie"
                                        value="<?php echo htmlspecialchars($functie); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="data_min">Data salariului - min</label>
                                    <input type="date" class="form-control" name="data_min" id="data_min"
                                        value="<?php echo htmlspecialchars($data_min); ?>" placeholder="Data nasterii">
                                </div>
                                <div class="col-md-2">
                                    <label for="data_max">Data salariului - max</label>
                                    <input type="date" class="form-control" name="data_max" id="data_max"
                                        value="<?php echo htmlspecialchars($data_max); ?>" placeholder="Data nasterii">
                                </div>
                                <div class="col-md-2">
                                    <label for="ID_Salariu">ID salariu</label>
                                    <input type="text" class="form-control" name="ID_Salariu" id="ID_Salariu"
                                        value="<?php echo htmlspecialchars($ID_Salariu); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="salariu_min">Salariu baza minim</label>
                                    <input type="number" class="form-control" name="salariuBazaMin" id="salariuBazaMin"
                                        value="<?php echo htmlspecialchars($salariuBazaMin); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="salariu_max">Salariu baza maxim</label>
                                    <input type="number" class="form-control" name="salariuBazaMax" id="salariuBazaMax"
                                        value="<?php echo htmlspecialchars($salariuBazaMax); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="bonus_min">Bonus % minim</label>
                                    <input type="number" class="form-control" name="bonusMin" id="bonusMin"
                                        value="<?php echo htmlspecialchars($bonusMin); ?>">
                                        </div>
                                        <div class="col-md-2">
                                    <label for="bonus_max">Bonus % maxim</label>
                                    <input type="number" class="form-control" name="bonusMax" id="bonusMax"
                                        value="<?php echo htmlspecialchars($bonusMax); ?>">
                                        </div>
                                        <div class="col-md-2">
                                    <label for="suma_min">Suma totala minim</label>
                                    <input type="number" class="form-control" name="sumamin" id="sumamin"
                                        value="<?php echo htmlspecialchars($sumamin); ?>">
                                        </div>
                                        <div class="col-md-2">
                                    <label for="suma_max">Suma totala maxim</label>
                                    <input type="number" class="form-control" name="sumamax" id="sumamax"
                                        value="<?php echo htmlspecialchars($sumamax); ?>">
                                        </div>

                            </div>
                            <div class="row mt-2 d-flex align-items-end">
                                <div class="col-md-7   d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filtrează</button>
                                </div>
                                <div class="col-md-2">
                                    <label for="order">Sortare</label>
                                    <select class="form-select" name="order" id="order">
                                        <option value="ID_Salariu" <?php if ($order === 'ID_Salariu')
                                            echo 'selected'; ?>>ID
                                        </option>
                                        <option value="Nume" <?php if ($order === 'Nume')
                                            echo 'selected'; ?>>Nume Angajat
                                        </option>
                                        <option value="Salariu de baza" <?php if ($order === 'Salariu de baza')
                                            echo 'selected'; ?>>Salariu de baza
                                        </option>
                                        <option value="Bonus %" <?php if ($order === 'Bonus %')
                                            echo 'selected'; ?>>Bonus %
                                        </option>
                                        <option value="Suma" <?php if ($order === 'Suma')
                                            echo 'selected'; ?>>Suma
                                        </option>
                                        <option value="Data" <?php if ($order === 'Data')
                                            echo 'selected'; ?>>Data
                                        </option>


                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <div class="row no-wrap">
                                        <label class="form-check-label" for="direction1">Ascendent</label>
                                        <input class="form-check-input" type="radio" name="direction" id="direction1"
                                            value="ASC" <?php if ($direction === 'ASC')
                                                echo 'checked'; ?>>

                                    </div>
                                    <div class="row no-wrap">
                                        <label class="form-check-label" for="direction2">Descendent</label>
                                        <input class="form-check-input" type="radio" name="direction" id="direction2"
                                            value="DESC" <?php if ($direction === 'DESC')
                                                echo 'checked'; ?>>

                                    </div>
                                </div>
                            </div>

                        </form>

                        <!-- Tabel pentru afișarea angajatilor -->
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nume Angajat</th>
                                    <th>Funcția</th>
                                    <th>Data salariului</th>
                                    <th>Salariu de baza</th>
                                    <th>Bonus %</th>
                                    <th>Salariul total</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['ID_Salariu'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['NumeAngajat']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Functie']) . "</td>";
                                    echo "<td>" . $row['Data']->format('d.m.Y') . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Salariu de baza']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Bonus %']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Suma']) . "</td>";
                                    echo "<td>";
                                    echo "<a href='/bd/dashboard/delete-salariu.php?id=" . $row['ID_Salariu']. "' class='btn btn-danger btn-sm'>Șterge</a>";
                                    echo "  ";
                                    echo "<a href='/bd/dashboard/more-salariu.php?id=" . $row['ID_Salariu'] . "' class='btn btn-primary btn-sm'>Mai multe</a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>


                        </table>

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
<?php
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>