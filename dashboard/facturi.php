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

$nume_client = isset($_GET['nume_client']) ? $_GET['nume_client'] : '';
$prenume_client = isset($_GET['prenume_client']) ? $_GET['prenume_client'] : '';
$id_factura = isset($_GET['id_factura']) ? $_GET['id_factura'] : '';
$suma_min = isset($_GET['suma_min']) ? $_GET['suma_min'] : '';
$suma_max = isset($_GET['suma_max']) ? $_GET['suma_max'] : '';
$data_min = isset($_GET['data_min']) ? $_GET['data_min'] : '';
$data_max = isset($_GET['data_max']) ? $_GET['data_max'] : '';
$order = isset($_GET['order']) ? $_GET['order'] : 'id_factura';
$direction = isset($_GET['direction']) ? $_GET['direction'] : 'DESC';

$sql = "SELECT TOP 50 F.ID_Factura, F.Data, F.Suma, C.Nume, C.Prenume
        FROM Facturi F 
        INNER JOIN Clienti C ON F.ID_Client = C.ID_Client";

$conditions = [];
$params = [];


if (!empty($nume_client)) {
  $conditions[] = "C.Nume LIKE ?";
  $params[] = "%" . $nume_client . "%";
}
if (!empty($prenume_client)) {
  $conditions[] = "C.Prenume LIKE ?";
  $params[] = "%" . $prenume_client . "%";
}
if (!empty($suma_min)) {
  $conditions[] = "F.Suma >= ?";
  $params[] = $suma_min;
}
if (!empty($suma_max)) {
  $conditions[] = "F.Suma <= ?";
  $params[] = $suma_max;
}
if (!empty($data_min)) {
  $conditions[] = "F.Data >= ?";
  $params[] = $data_min;
}
if (!empty($data_max)) {
  $conditions[] = "F.Data <= ?";
  $params[] = $data_max;
}
if (!empty($id_factura)) {
  $conditions[] = "F.Id_Factura LIKE ?";
  $params[] = "%" . $id_factura . "%";
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
            <h2>Facturi</h2>
            <div class="col-md-2 d-flex align-items-end">
              <a href="/bd/dashboard/add-invoice.php" class="btn btn-success">Adauga factura</a>
            </div>

            <!-- Form pentru filtre -->
            <form method="GET" class="mb-4">
              <div class="row mt-2">
                <div class="col-md-3">
                  <label for="nume_client">Nume Client</label>
                  <input type="text" class="form-control" name="nume_client" id="nume_client"
                    value="<?php echo htmlspecialchars($nume_client); ?>">
                </div>
                <div class="col-md-3">
                  <label for="prenume_client">Prenume Client</label>
                  <input type="text" class="form-control" name="prenume_client" id="prenume_client"
                    value="<?php echo htmlspecialchars($prenume_client); ?>">
                </div>
                <div class="col-md-2">
                  <label for="suma_min">Suma Minimă</label>
                  <input type="number" class="form-control" name="suma_min" id="suma_min"
                    value="<?php echo htmlspecialchars($suma_min); ?>">
                </div>
                <div class="col-md-2">
                  <label for="suma_max">Suma Maximă</label>
                  <input type="number" class="form-control" name="suma_max" id="suma_max"
                    value="<?php echo htmlspecialchars($suma_max); ?>">
                </div>
                <div class="col-md-2">
                  <label for="id_factura">Numărul facturii</label>
                  <input type="text" class="form-control" name="id_factura" id="id_factura"
                    value="<?php echo htmlspecialchars($id_factura); ?>">
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




              </div>
              <div class="row mt-2 d-flex align-items-end">
                <div class="col-md-7 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary">Filtrează</button>
                </div>
                <div class="col-md-2">
                  <label for="order">Sortare</label>
                  <select class="form-select" name="order" id="order">
                    <option value="id_factura" <?php if ($order === 'id_factura')
                      echo 'selected'; ?>>ID
                    </option>
                    <option value="Nume" <?php if ($order === 'Nume')
                      echo 'selected'; ?>>Nume
                    </option>
                    <option value="Prenume" <?php if ($order === 'Prenume')
                      echo 'selected'; ?>>
                      Prenume</option>
                    <option value="Data" <?php if ($order === 'data')
                      echo 'selected'; ?>>Data</option>
                    <option value="Suma" <?php if ($order === 'Suma')
                      echo 'selected'; ?>>Suma </option>
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
                  <th>Nume Client</th>
                  <th>Prenume Client</th>
                  <th>Data</th>
                  <th>Suma</th>
                  <th>Acțiuni</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $numar = 1;
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                  echo "<tr>";
                  echo "<td>" . $row['ID_Factura'] . "</td>";
                  echo "<td>" . htmlspecialchars($row['Nume']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['Prenume']) . "</td>";
                  echo "<td>" . $row['Data']->format('Y-m-d') . "</td>";
                  echo "<td>" . htmlspecialchars($row['Suma']) . " RON</td>";
                  echo "<td>";
                  if ($_SESSION['user'] == "admin") {
                  echo "<a href='/bd/dashboard/delete-invoice.php?id=" . $row['ID_Factura'] . "'class='btn btn-danger btn-sm'>Șterge</a>";
                  echo "  ";}
                  echo "<a href='/bd/dashboard/more-invoice.php?id=" . $row['ID_Factura'] . "' class='btn btn-primary btn-sm'>Mai multe</a>";
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