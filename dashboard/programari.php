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

$order = isset($_GET['order']) ? $_GET['order'] : 'id_programare';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
$nume_angajat = isset($_GET['nume_angajat']) ? $_GET['nume_angajat'] : '';
$nume_client = isset($_GET['nume_client']) ? $_GET['nume_client'] : '';
$id_programare = isset($_GET['id_programare']) ? $_GET['id_programare'] : '';
$nume_serviciu = isset($_GET['nume_serviciu']) ? $_GET['nume_serviciu'] : '';
$durata_min = isset($_GET['durata_min']) ? $_GET['durata_min'] : '';
$durata_max = isset($_GET['durata_max']) ? $_GET['durata_max'] : '';
$data_min = isset($_GET['data_min']) ? $_GET['data_min'] : '';
$data_max = isset($_GET['data_max']) ? $_GET['data_max'] : '';


$sql = "SELECT TOP 50 S.Nume, S.Durata, P.ID_Programare, P.ID_Client,
        A.Nume + ' ' + A.Prenume AS Nume_Angajat, C.Nume + ' ' + C.Prenume AS Nume_Client,
        P.ID_Factura, P.Data, P.Mentiuni 
        FROM Programari P JOIN Servicii S ON P.ID_Serviciu = S.ID_Serviciu
        JOIN Angajati A ON P.ID_Angajat = A.ID_Angajat
        JOIN Clienti C ON P.ID_Client = C.ID_Client";

$conditions = [];
$params = [];

if ($nume_angajat) {
    $conditions[] = "A.Nume LIKE ? OR A.Prenume LIKE ?";
    $params[] = "%" . $nume_angajat . "%";
    $params[] = "%" . $nume_angajat . "%";
}
if ($nume_client) {
    $conditions[] = "C.Nume LIKE ? OR C.Prenume LIKE ?";
    $params[] = "%" . $nume_client . "%";
    $params[] = "%" . $nume_client . "%";
}
if ($id_programare) {
    $conditions[] = "P.ID_Programare LIKE ?";
    $params[] = "%" . $id_programare . "%";
}
if ($nume_serviciu) {
    $conditions[] = "S.Nume LIKE ?";
    $params[] = "%" . $nume_serviciu . "%";
}
if ($durata_min) {
    $conditions[] = "S.Durata >= ?";
    $params[] = $durata_min;
}
if ($durata_max) {
    $conditions[] = "S.Durata <= ?";
    $params[] = $durata_max;
}
if ($data_min) {
    $conditions[] = "P.Data >= ?";
    $params[] = $data_min;
}
if ($data_max) {
    $conditions[] = "P.Data <= ?";
    $params[] = $data_max;
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
                        <h2>Programari</h2>
                        <div class="col-md-3 d-flex align-items-end">
                            <a href="/bd/dashboard/add-programare.php" class="btn btn-success">Adauga programare</a>
                        </div>

                        <!-- Form pentru filtre -->
                        <form method="GET" class="mb-4">

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <label for="nume_serviciu">Nume Serviciu</label>
                                    <input type="text" class="form-control" name="nume_serviciu" id="nume_serviciu"
                                        value="<?php echo htmlspecialchars($nume_serviciu); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label for="nume_angajat">Nume Angajat</label>
                                    <input type="text" class="form-control" name="nume_angajat" id="nume_angajat"
                                        value="<?php echo htmlspecialchars($nume_angajat); ?>">
                                        </div>
                                <div class="col-md-3">
                                    <label for="nume_client">Nume Client</label>
                                    <input type="text" class="form-control" name="nume_client" id="nume_client"
                                        value="<?php echo htmlspecialchars($nume_client); ?>"></div>
                                <div class="col-md-2">
                                    <label for="data_min">Data de inceput</label>
                                    <input type="date" class="form-control" name="data_min" id="data_min"
                                        value="<?php echo htmlspecialchars($data_min); ?>"></div>
                                <div class="col-md-2">
                                    <label for="data_max">Data de sfarsit</label>
                                    <input type="date" class="form-control" name="data_max" id="data_max"
                                        value="<?php echo htmlspecialchars($data_max); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="durata_min">Durata minima</label>
                                    <input type="time" class="form-control" name="durata_min" id="durata_min"
                                        value="<?php echo htmlspecialchars($durata_min); ?>">
                                </div>
                                <div class="col-md-2">
                                    <label for="durata_max">Durata maxima</label>
                                    <input type="time" class="form-control" name="durata_max" id="durata_max" 
                                        value="<?php echo htmlspecialchars($durata_max); ?>" >
                                </div>
                                <div class="col-md-2">
                                    <label for="id_programare">ID Programare</label>
                                    <input type="text" class="form-control" name="id_programare" id="id_programare"
                                        value="<?php echo htmlspecialchars($id_programare); ?>"></div>

                            </div>
                            <div class="row mt-2 d-flex align-items-end">
                                <div class="col-md-7   d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary">Filtrează</button>
                                </div>
                                <div class="col-md-2">
                                    <label for="order">Sortare</label>
                                    <select class="form-select" name="order" id="order">
                                        <option value="id_programare" <?php if ($order === 'id_programare')
                                            echo 'selected'; ?>>ID Programare</option>
                                        <option value="nume_client" <?php if ($order === 'nume_client')
                                            echo 'selected'; ?>>Nume Client</option>
                                    <option value="data" <?php if ($order === 'data')
                                            echo 'selected'; ?>>Data</option>
                                        <option value="durata" <?php if ($order === 'durata')
                                            echo 'selected'; ?>>Durata</option>
                                            <option value="ID_factura" <?php if ($order === 'ID_factura')
                                            echo 'selected'; ?>>Facturat</option>

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
                                    <th>Nume Client</th>
                                    <th>Data Programarii</th>
                                    <th>Durata</th>
                                    <th>Mentiuni</th>
                                    <th>Actiuni</th>
                                    
                                </tr>
                            </thead>
                            <tbody>

                                <?php
                                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                                    echo "<tr";
                                    if (!$row['ID_Factura'] && $row['Data'] < date_sub(new DateTime(), date_interval_create_from_date_string('1 day'))) {
                                        echo " class='table-danger'";
                                    }
                                    echo ">";
                                    echo "<td>" . $row['ID_Programare'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Nume'])."<br><span class='badge bg-primary'> " . htmlspecialchars($row['Nume_Angajat']) . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['Nume_Client']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['Data']->format('Y-m-d')) . "<br>" . htmlspecialchars($row['Data']->format('H:i'));
                                    echo "</td>";
                                    $check = "SELECT Durata FROM Programari WHERE ID_Programare = ?";
                                    $stmt2 = sqlsrv_query($conn, $check, array($row['ID_Programare']));
                                    $row2 = sqlsrv_fetch_array($stmt2, SQLSRV_FETCH_ASSOC);
                                    if ($row2['Durata'] == null) {
                                        
                                    echo "<td>" . htmlspecialchars($row['Durata']->format('H:i')) . " ore </td>";
                                    } else {
                                        echo "<td>" . htmlspecialchars($row2['Durata']->format('H:i')) . " ore </td>";
                                    }
                                    echo "<td>" . htmlspecialchars($row['Mentiuni']);
                                    if (!$row['ID_Factura']) {
                                        echo "<span class='badge bg-danger'>Nefacturat</span>";
                                    }
                                    echo "</td>";
                                    echo "<td>";
                                   
                                        
                                    echo "<a href='/bd/dashboard/delete-programare.php?id=" . $row['ID_Programare'] . "' class='btn btn-danger btn-sm'>Șterge</a>";
                                    echo "  ";
                                    echo "<a href='/bd/dashboard/more-programare.php?id=" . $row['ID_Programare'] . "' class='btn btn-primary btn-sm'>Mai multe</a> ";
                                    if (!$row['ID_Factura']) {
                                        echo "<br><a href='/bd/dashboard/add-invoice.php?id=" . $row['ID_Programare'] . " &idc=" . $row['ID_Client'] . "' class='btn btn-success btn-sm mt-1'>Facturează</a>";
                                    } else {
                                        echo "<br><a href='/bd/dashboard/more-invoice.php?id=" . $row['ID_Factura'] . "' class='btn btn-info btn-sm mt-1'>Detalii Factură</a>";
                                    }
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