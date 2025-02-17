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
$order = isset($_GET['order']) ? $_GET['order'] : 'ID_Produs';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'ASC';
$nume = isset($_GET['nume']) ? $_GET['nume'] : '';
$ID_Produs= isset($_GET['ID_Produs']) ? $_GET['ID_Produs'] : '';
$pret_min = isset($_GET['pret_min']) ? $_GET['pret_min'] : '';
$pret_max = isset($_GET['pret_max']) ? $_GET['pret_max'] : '';
$cantitate_min = isset($_GET['cantitate_min']) ? $_GET['cantitate_min'] : '';
$cantitate_max = isset($_GET['cantitate_max']) ? $_GET['cantitate_max'] : '';

$sql = "SELECT TOP 50 P.Nume, P.[Pret_Unitar], P.[Pret/Kg], P.ID_Produs, P.[Cantitate_flacon(ml)]
        FROM Produse P";

$conditions = [];
$params = [];

if ($nume) {
    $conditions[] = "P.Nume LIKE ?";
    $params[] = "%" . $nume . "%";
}

if ($ID_Produs) {
    $conditions[] = "P.ID_Produs LIKE ?";
    $params[] = "%" . $ID_Produs . "%";
}
if ($pret_min) {
    $conditions[] = "P.[Pret_Unitar] >= ?";
    $params[] = $pret_min;
}
if ($pret_max) {
    $conditions[] = "P.[Pret_Unitar] <= ?";
    $params[] = $pret_max;
}
if ($cantitate_min) {
    $conditions[] = "P.[Cantitate_flacon(ml)] >= ?";
    $params[] = $cantitate_min;
}
if ($cantitate_max) {
    $conditions[] = "P.[Cantitate_flacon(ml)] <= ?";
    $params[] = $cantitate_max;
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
                        <h2>Produse</h2>
                        <div class="col-md-4 d-flex align-items-end">
                            <a href="/bd/dashboard/add-produs.php" class="btn btn-success">Adauga produs</a>
                        </div>

                        <!-- Form pentru filtre -->
                        <form method="GET" class="mb-4">

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <label for="nume">Nume produs</label>
                                    <input type="text" class="form-control" name="nume" id="nume"
                                        value="<?php echo htmlspecialchars($nume); ?>">
                                </div>
                                
                                <div class="col-md-2">
                                    <label for="pret_min">Pret minim</label>
                                    <input type="text" class="form-control" name="pret_min" id="pret_min"
                                        value="<?php echo htmlspecialchars($pret_min); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="pret_max">Pret maxim</label>
                                    <input type="text" class="form-control" name="pret_max" id="pret_max"
                                        value="<?php echo htmlspecialchars($pret_max); ?>">
                                </div>
                                <div class="col-md-2">  
                                    <label for="cantitate_min">Cantitate minimă</label>
                                    <input type="text" class="form-control" name="cantitate_min" id="cantitate_min"
                                        value="<?php echo htmlspecialchars($cantitate_min); ?>">
                                        </div>
                                <div class="col-md-2">
                                    <label for="cantitate_max">Cantitate maximă</label>
                                    <input type="text" class="form-control" name="cantitate_max" id="cantitate_max"
                                        value="<?php echo htmlspecialchars($cantitate_max); ?>">
                                        </div>
                                        
                                <div class="col-md-2">
                                    <label for="ID_Produs">ID produs</label>
                                    <input type="text" class="form-control" name="ID_Produs" id="ID_Produs"
                                        value="<?php echo htmlspecialchars($ID_Produs); ?>">
                                        </div>

                                </div>


                            
                            <div class="row mt-2 d-flex align-items-end">
                                <div class="col-md-7   d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filtrează</button>
                                </div>
                                <div class="col-md-2">
                                    <label for="order">Sortare</label>
                                    <select class="form-select" name="order" id="order">
                                        <option value="ID_Produs" <?php if ($order === 'ID_Produs')
                                            echo 'selected'; ?>>ID produs</option>
                                        <option value="nume" <?php if ($order === 'nume')
                                            echo 'selected'; ?>>Nume</option>
                                        <option value="pret_unitar" <?php if ($order === 'pret_unitar')
                                            echo 'selected'; ?>>Pret unitar</option>
                                        <option value="[Cantitate_Flacon(ml)]" <?php if ($order === '[Cantitate_Flacon(ml)]')
                                            echo 'selected'; ?>>Cantitate</option>
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
                                    <th>Nume produs</th>
                                    <th>Pret unitar</th>
                                    <th>Cantitate Flacon</th>
                                    <th>Pret/kg</th>
                                    <th>Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['ID_Produs'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Nume']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Pret_Unitar']) . " RON </td>";
                                    echo "<td>" . htmlspecialchars($row['Cantitate_flacon(ml)']) . " ml</td>";
                                    echo "<td>" . htmlspecialchars($row['Pret/Kg']) . " RON</td>";
                                    echo "<td>";
                                    echo "<a href='/bd/dashboard/delete-produs.php?id=" . $row['ID_Produs'] . "' class='btn btn-danger btn-sm'>Șterge</a>";
                                    echo "  ";
                                    echo "<a href='/bd/dashboard/more-produs.php?id=" . $row['ID_Produs']. "' class='btn btn-primary btn-sm'>Mai multe</a>";
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