<?php
session_start();

// Verifică sesiunea utilizatorului
if (!isset($_SESSION['user'])) {
    header("Location: /bd/login.php");
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
$order = isset($_GET['order']) ? $_GET['order'] : 'id_serviciu';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'ASC';
$nume = isset($_GET['nume']) ? $_GET['nume'] : '';
$id_serviciu= isset($_GET['id_serviciu']) ? $_GET['id_serviciu'] : '';
$pret = isset($_GET['pret']) ? $_GET['pret'] : '';
$durata_min = isset($_GET['durata_min']) ? $_GET['durata_min'] : '';
$durata_max = isset($_GET['durata_max']) ? $_GET['durata_max'] : '';
// Construiește interogarea SQL
$sql = "SELECT TOP 50 S.Nume, S.Pret, S.Durata, S.ID_Serviciu
        FROM Servicii S";

$conditions = [];
$params = [];

// Adaugă filtre în interogare
if ($nume) {
    $conditions[] = "S.Nume LIKE ?";
    $params[] = "%" . $nume . "%";
}

if ($id_serviciu) {
    $conditions[] = "S.ID_Serviciu LIKE ?";
    $params[] = "%" . $id_serviciu . "%";
}
if ($pret) {
    $conditions[] = "S.Pret LIKE ?";
    $params[] = "%" . $pret . "%";
}
if ($durata_min) {
    $conditions[] = "S.Durata >= ?";
    $params[] = $durata_min;
}
if ($durata_max) {
    $conditions[] = "S.Durata <= ?";
    $params[] = $durata_max;
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
                        <h2>Servicii</h2>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="/bd/dashboard/add-service.php" class="btn btn-success">Adauga serviciu</a>
                        </div>

                        <!-- Form pentru filtre -->
                        <form method="GET" class="mb-4">

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <label for="nume">Nume Serviciu</label>
                                    <input type="text" class="form-control" name="nume" id="nume"
                                        value="<?php echo htmlspecialchars($nume); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="durata_min">Durata minima</label>
                                    <input type="time" class="form-control" name="durata_min" id="durata_min"
                                        value="<?php echo htmlspecialchars($data_min); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="durata_max">Durata maxima</label>
                                    <input type="time" class="form-control" name="durata_max" id="durata_max" 
                                        value="<?php echo htmlspecialchars($durata_max); ?>" >
                                </div>
                                <div class="col-md-2">
                                    <label for="id_serviciu">ID serviciu</label>
                                    <input type="text" class="form-control" name="id_serviciu" id="id_serviciu"
                                        value="<?php echo htmlspecialchars($id_serviciu); ?>">
                                </div>

                            </div>
                            <div class="row mt-2 d-flex align-items-end">
                                <div class="col-md-7   d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filtrează</button>
                                </div>
                                <div class="col-md-2">
                                    <label for="order">Sortare</label>
                                    <select class="form-select" name="order" id="order">
                                        <option value="id_serviciu" <?php if ($order === 'id_serviciu')
                                            echo 'selected'; ?>>ID Serviciu</option>
                                        <option value="nume" <?php if ($order === 'nume')
                                            echo 'selected'; ?>>Nume</option>
                                        <option value="pret" <?php if ($order === 'pret')
                                            echo 'selected'; ?>>Pret</option>
                                        <option value="durata" <?php if ($order === 'durata')
                                            echo 'selected'; ?>>Durata</option>

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

                        <!-- Tabel pentru afișarea clientilor -->
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nume Serviciu</th>
                                    <th>Pret</th>
                                    <th>Durata</th>
                                    <th>Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['ID_Serviciu'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Nume']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Pret']) . " RON </td>";
                                    echo "<td>" . htmlspecialchars($row['Durata']->format('H:i')) . " ore </td>";
                                    echo "<td>";
                                    echo "<a href='/bd/dashboard/delete-serviciu.php?id=" . $row['ID_Serviciu'] . "' class='btn btn-danger btn-sm'>Șterge</a>";
                                    echo "  ";
                                    echo "<a href='/bd/dashboard/more-serviciu.php?id=" . $row['ID_Serviciu']. "' class='btn btn-primary btn-sm'>Mai multe</a>";
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