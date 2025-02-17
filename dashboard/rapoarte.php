<?php
session_start();

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

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
  die(print_r(sqlsrv_errors(), true));
}

$nume_angajat = isset($_GET['nume_angajat']) ? $_GET['nume_angajat'] : '';
$prenume_angajat = isset($_GET['prenume_angajat']) ? $_GET['prenume_angajat'] : '';
$data_min = isset($_GET['data_min']) ? $_GET['data_min'] : (new DateTime('-30 days'))->format('Y-m-d');
$data_max = isset($_GET['data_max']) ? $_GET['data_max'] : (new DateTime())->format('Y-m-d');
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';
$top = isset($_GET['top']) ? $_GET['top'] : 10;
$order = isset($_GET['order']) ? $_GET['order'] : 'VenituriLunare';

$sql = "SELECT TOP " . $top . " A.ID_Angajat, 
             A.Nume, A.Prenume, 
             ISNULL(((SELECT SUM(F.Suma) 
              FROM Programari P
                INNER JOIN Facturi F ON P.ID_Factura = F.ID_Factura
              WHERE P.ID_Angajat = A.ID_Angajat
              AND F.Data BETWEEN '" .$data_min. "' AND '" .$data_max ."'
              )
              - ISNULL((SELECT SUM(I.Suma) 
                 FROM Inventar I 
                 INNER JOIN Programari P ON I.ID_Programare = P.ID_Programare
                 inner join facturi F on P.ID_Factura = F.ID_Factura
                 WHERE P.ID_Angajat = A.ID_Angajat 
                 AND F.Data BETWEEN '" .$data_min. "' AND '" .$data_max ."'
                 ),0)), 0) AS VenituriLunare           
FROM Angajati A";

$venitSQL = "SELECT SUM(F.Suma) AS VenituriLunare
              FROM Facturi F 
              WHERE F.Data BETWEEN ? AND ?";
$venitstmt = sqlsrv_query($conn, $venitSQL, [$data_min, $data_max]);
$cheltuieliSQL = "SELECT SUM(I.Suma) + (SELECT SUM(Suma) FROM Salarii) AS CheltuieliLunare
                 FROM Inventar I 
                 INNER JOIN Programari P ON I.ID_Programare = P.ID_Programare
                    inner join facturi F on P.ID_Factura = F.ID_Factura
                    WHERE F.Data BETWEEN ? AND ?"; 
$cheltuielistmt = sqlsrv_query($conn, $cheltuieliSQL, [$data_min, $data_max]);
$venit = sqlsrv_fetch_array($venitstmt, SQLSRV_FETCH_ASSOC);
$cheltuieli = sqlsrv_fetch_array($cheltuielistmt, SQLSRV_FETCH_ASSOC);

$conditions = [];
$params = [];

if (!empty($nume_angajat)) {
  $conditions[] = "A.Nume LIKE ?";
  $params[] = "%" . $nume_angajat . "%";
}
if (!empty($prenume_angajat)) {
  $conditions[] = "A.Prenume LIKE ?";
  $params[] = "%" . $prenume_angajat . "%";
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
            <h2>Rapoarte și indici de performanță</h2>

            <form method="GET" class="mb-4">
              <div class="row mt-2">
                <div class="col-md-3">
                  <label for="nume_angajat">Nume angajat</label>
                  <input type="text" class="form-control" name="nume_angajat" id="nume_angajat"
                    value="<?php echo htmlspecialchars($nume_angajat); ?>">
                </div>
                <div class="col-md-3">
                  <label for="prenume_angajat">Prenume angajat</label>
                  <input type="text" class="form-control" name="prenume_angajat" id="prenume_angajat"
                    value="<?php echo htmlspecialchars($prenume_angajat); ?>">
                </div>
                <div class="col-md-2">
                  <label for="data_min">Data de început</label>
                  <input type="date" class="form-control" name="data_min" id="data_min"
                    value="<?php echo htmlspecialchars($data_min); ?>">
                </div>
                <div class="col-md-2">
                  <label for="data_max">Data de sfârșit</label>
                  <input type="date" class="form-control" name="data_max" id="data_max"
                    value="<?php echo htmlspecialchars($data_max); ?>">
                </div>
                <div class="col-md-2">
                  <label for="top">Top</label>
                  <input type="number" class="form-control" name="top" id="top" value="<?php echo $top; ?>">

              </div>
            </div>
              <div class="row mt-2 d-flex align-items-end">
                <div class="col-md-7 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary">Filtrează</button>
                </div>
                <div class="col-md-2">
                  <label for="order">Sortare</label>
                  <select class="form-select" name="order" id="order">
                    <option value="Nume" <?php if ($order === 'Nume')
                      echo 'selected'; ?>>Nume</option>
                    <option value="Prenume" <?php if ($order === 'Prenume')
                      echo 'selected'; ?>> Prenume</option>
                    <option value="Data" <?php if ($order === 'data')
                      echo 'selected'; ?>>Data</option>
                    <option value="VenituriLunare" <?php if ($order === 'VenituriLunare')
                      echo 'selected'; ?>>Venituri </option>
                  </select>
                </div>
                <div class="col-md-2">
                  <div class="row no-wrap">
                    <label class="form-check-label" for="direction1">Ascendent</label>
                    <input class="form-check-input" type="radio" name="direction" id="direction1" value="ASC" <?php if ($direction === 'ASC')
                      echo 'checked'; ?>>

                  </div>
                  <div class="row no-wrap">
                    <label class="form-check-label" for="direction2">Descendent</label>
                    <input class="form-check-input" type="radio" name="direction" id="direction2" value="DESC" <?php if ($direction === 'DESC')
                      echo 'checked'; ?>>

                  </div>
                </div>

              </div>
            </form>

            <!-- Tabel pentru afișarea facturilor -->
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Nume Angajat</th>
                  <th>Prenume Angajat</th>
                  <?php echo "<th> Venituri in perioada " . $data_min . " - " . $data_max . "</th>"; ?>
                </tr>
              </thead>
              <tbody>
                <?php
                $numar = 1;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                  echo "<tr ";
                  if($numar == 1) {
                    echo "class='table-warning'";
                  }
                  else if($numar == 2) {
                    echo "class='table-secondary'";
                  }
                  else if($numar == 3) {
                    echo "class='table-info'";
                    }
                    echo ">";
                  
                  echo "<td>" . $numar++ . "</td>";
                  echo "<td>" . htmlspecialchars($row['Nume']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['Prenume']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['VenituriLunare']) . " RON</td>";
                  echo "</tr>";}
                ?>
              </tbody>
            </table>
            <table class="table table-bordered">
              <tbody>
                <tr>
                  <td><strong> Venituri totale in perioada <?php echo $data_min . " - " . $data_max; ?></strong></td>
                  <td><?php echo $venit['VenituriLunare'] . " RON"; ?></td>
                </tr>
                <tr>
                  <td><strong> Cheltuieli totale in perioada <?php echo $data_min . " - " . $data_max; ?></strong></td>
                  <td><?php echo $cheltuieli['CheltuieliLunare'] . " RON"; ?></td>
                </tr>
                <tr>
                  
                  <?php
                    $profit = $venit['VenituriLunare'] - $cheltuieli['CheltuieliLunare'];
                    if ($profit > 0) {
                      echo "<td class='table-success'>";
                      echo "<strong> Profit total in perioada " . $data_min . " - " . $data_max . "</strong>";
                      echo "</td>";
                      echo "<td class='table-success'>" . $profit . " RON</td>";
                    } else if ($profit < 0) {
                      echo "<td class='table-danger'>";
                        echo "<strong> Pierderi totale in perioada " . $data_min . " - " . $data_max . "</strong>";
                        echo "</td>";
                        echo "<td class='table-danger'>" . $profit . " RON</td>";
                    } else {
                      echo "<td class='table-warning'>";
                        echo "<strong> Profit total in perioada " . $data_min . " - " . $data_max . "</strong>";
                        echo "</td>";
                        echo "<td class='table-warning'>" . $profit . " RON</td>";
                    }
                 ?>
                </tr>
              </tbody>

            <?php
            if (isset($_SESSION['message'])) {
              $message = $_SESSION['message'];
              $class = $_SESSION['class'];
              unset($_SESSION['message'], $_SESSION['class']);
            }
            ?>
            <div id="responseDiv" class="toast <?php echo $class; ?>"
              style="display: <?php echo isset($message) ? 'block' : 'none'; ?>;" role="alert" aria-live="assertive"
              aria-atomic="true">
              <div class="d-flex">
                <div class="toast-body flex-grow-1"><?php echo $message; ?></div>
                <button type="button" class="btn-close p-3" aria-label="Close" onclick="hideToast()"></button>
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