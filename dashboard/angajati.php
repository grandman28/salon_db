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
$order = isset($_GET['order']) ? $_GET['order'] : 'ID_Angajat';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
$nume_angajat = isset($_GET['nume_angajat']) ? $_GET['nume_angajat'] : '';
$prenume_angajat = isset($_GET['prenume_angajat']) ? $_GET['prenume_angajat'] : '';
$id_angajat = isset($_GET['id_angajat']) ? $_GET['id_angajat'] : '';
$telefon = isset($_GET['telefon']) ? $_GET['telefon'] : '';
$data_min = isset($_GET['data_min']) ? $_GET['data_min'] : '';
$data_max = isset($_GET['data_max']) ? $_GET['data_max'] : '';
$functie = isset($_GET['functie']) ? $_GET['functie'] : '';
$sql = "SELECT TOP 50 A.id_angajat, A.Nume, A.Prenume, A.Telefon, A.Functie, 
        (SELECT COUNT(*) FROM Programari P WHERE P.ID_Angajat = A.ID_Angajat AND P.Data > GETDATE()) AS ProgramariViitoare
        FROM Angajati A";

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
if (!empty($telefon)) {
    $conditions[] = "A.Telefon LIKE ?";
    $params[] = "%" . $telefon . "%";
}
if (!empty($data_min)) {
    $conditions[] = "A.DataNasterii >= ?";
    $params[] = $data_min;
}
if (!empty($data_max)) {
    $conditions[] = "A.DataNasterii <= ?";
    $params[] = $data_max;
}
if (!empty($functie)) {
    $conditions[] = "A.Functie LIKE ?";
    $params[] = "%" . $functie . "%";
}
if (!empty($id_angajat)) {
    $conditions[] = "A.id_angajat = ?";
    $params[] = $id_angajat;
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY " . $order . " " . $direction;

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
                        <h2>Angajati</h2>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="/bd/dashboard/add-angajat.php" class="btn btn-success">Adauga angajat</a>
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
                                    <label for="suma_min">Telefon</label>
                                    <input type="number" class="form-control" name="telefon" id="telefon"
                                        value="<?php echo htmlspecialchars($telefon); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="data_min">Data nasterii - min</label>
                                    <input type="date" class="form-control" name="data_min" id="data_min"
                                        value="<?php echo htmlspecialchars($data_min); ?>" placeholder="Data nasterii">
                                </div>
                                <div class="col-md-2">
                                    <label for="data_max">Data nasterii - max</label>
                                    <input type="date" class="form-control" name="data_max" id="data_max"
                                        value="<?php echo htmlspecialchars($data_max); ?>" placeholder="Data nasterii">
                                </div>
                                <div class="col-md-2">
                                    <label for="id_angajat">ID angajat</label>
                                    <input type="text" class="form-control" name="id_angajat" id="id_angajat"
                                        value="<?php echo htmlspecialchars($id_angajat); ?>">
                                </div>

                            </div>
                            <div class="row mt-2 d-flex align-items-end">
                                <div class="col-md-7   d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filtrează</button>
                                </div>
                                <div class="col-md-2">
                                    <label for="order">Sortare</label>
                                    <select class="form-select" name="order" id="order">
                                        <option value="id_angajat" <?php if ($order === 'id_angajat')
                                            echo 'selected'; ?>>ID
                                        </option>
                                        <option value="Nume" <?php if ($order === 'Nume')
                                            echo 'selected'; ?>>Nume
                                        </option>
                                        <option value="Prenume" <?php if ($order === 'Prenume')
                                            echo 'selected'; ?>>
                                            Prenume</option>
                                        <option value="DataNasterii" <?php if ($order === 'DataNasterii')
                                            echo 'selected'; ?>>Data Nasterii</option>
                                        <option value="ProgramariViitoare" <?php if ($order === 'ProgramariViitoare')
                                            echo 'selected'; ?>>Programari Viitoare</option>

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
                                    <th>Prenume Angajat</th>
                                    <th>Telefon</th>
                                    <th>Funcția</th>
                                    <th>Programari Viitoare</th>
                                    <th>Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['id_angajat'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Nume']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Prenume']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Telefon']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Functie']) . "</td>";
                                    echo "<td>";
                                    if ($row['ProgramariViitoare'] > 0) {
                                        echo "<span class='badge bg-primary'>" . $row['ProgramariViitoare'] . "</span>";
                                    } else {
                                        echo "<span class='badge bg-secondary'>" . $row['ProgramariViitoare'] . "</span>";
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                    echo "<a href='/bd/dashboard/delete-angajat.php?id=" . $row['id_angajat']. "' class='btn btn-danger btn-sm'>Șterge</a>";
                                    echo "  ";
                                    echo "<a href='/bd/dashboard/more-angajat.php?id=" . $row['id_angajat'] . "' class='btn btn-primary btn-sm'>Mai multe</a>";
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