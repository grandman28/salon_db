<?php
session_start();

$serverName = "GRANDMAN-TP\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$id_factura = intval($_GET['id']);

// Obține detalii despre factură și client
$query = "SELECT Facturi.*, Clienti.Nume, Clienti.Prenume, Clienti.Strada, Clienti.Numar, Clienti.Localitate, Clienti.Judet, Clienti.Telefon 
          FROM Facturi 
          JOIN Clienti ON Facturi.ID_Client = Clienti.ID_Client
          WHERE Facturi.ID_Factura = ?";
$stmt = sqlsrv_query($conn, $query, array($id_factura));
$factura = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Obține elementele din factură
$queryServices = "SELECT Programari.*, Servicii.Nume AS Serviciu, Servicii.Pret AS ServiciuPret, 
            Servicii.Durata as Durata, Angajati.Nume AS AngajatNume, Angajati.Prenume AS AngajatPrenume 
               FROM Programari 
               LEFT JOIN Servicii ON Programari.ID_Serviciu = Servicii.ID_Serviciu
               LEFT JOIN Angajati ON Programari.ID_Angajat = Angajati.ID_Angajat
               WHERE Programari.ID_Factura = ?";
$stmtServices = sqlsrv_query($conn, $queryServices, array($id_factura));

$queryItems = "SELECT Programari.*, Inventar.[Cantitate_folosita(ml)] AS Cantitate, Produse.Nume AS Produs, Inventar.Suma AS ProdusPret
               FROM Programari 
               LEFT JOIN Inventar ON Programari.ID_Programare = Inventar.ID_Programare
               LEFT JOIN Produse ON Inventar.ID_Produs = Produse.ID_Produs
               WHERE Programari.ID_Factura = ?";
$stmtItems = sqlsrv_query($conn, $queryItems, array($id_factura));
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
                  <a href="/bd/dashboard.html" class="list-group-item list-group-item-action active">Dashboard</a>
                  <a href="/bd/dashboard/facturi.php" class="list-group-item list-group-item-action" >Facturi</a>
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
                    <div id="printableArea">
      <?php echo "<h2>Factura nr. ".$id_factura."</h2>"; ?>

      <!-- Tabel pentru afișarea facturilor -->
        <div class="row">
       <div class="col-md-6">
      <p><strong>Client:</strong> <?= htmlspecialchars($factura['Nume'] . ' ' . $factura['Prenume']) ?></p>
    <p><strong>Adresă:</strong> <?= htmlspecialchars($factura['Strada'] . ' ' . $factura['Numar'] . ', ' . $factura['Localitate'] . ', ' . $factura['Judet']) ?></p>
    <p><strong>Data:</strong> <?= htmlspecialchars($factura['Data']->format('Y-m-d')) ?></p>
    <p><strong>Numar de telefon:</strong>  <?= htmlspecialchars($factura['Telefon'])?></p>
    </div>
    <div class="col-md-6" style="text-align: right;">
        <p><strong>Nume firma:</strong> Tinker Bell Salon de cosmetica SRL </p>
        <p><strong>Adresa:</strong> Str. Mihai Eminescu, nr. 13, Bucuresti, Romania</p>
        <p><strong>CIF:</strong> 123456789</p>
        <p><strong>IBAN:</strong> RO12RZBR0000060000000001</p>
        <p><strong>Telefon:</strong> 0723456789</p>
        </div>
        </div>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Denumire</th>
                <th>Cantitate</th>
                <th>Preț</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $numar = 1;
            $total = 0;
            while ($item = sqlsrv_fetch_array($stmtServices, SQLSRV_FETCH_ASSOC)) {
                if ($item['Serviciu'] == null) {
                    continue;
                }
                $denumire = $item['Serviciu'] . ' - ' . $item['AngajatNume'] . ' ' . $item['AngajatPrenume'];
                            
                $cantitate = $item['Durata']->format('H:i') . ' ore';
                $pret = $item['ServiciuPret'];
                $total += $pret;

                echo "<tr>";
                echo "<td>" . $numar++ . "</td>";
                echo "<td>" . htmlspecialchars($denumire) . "</td>";
                echo "<td>" . htmlspecialchars($cantitate) . "</td>";
                echo "<td>" . htmlspecialchars($pret) . " RON</td>";
                echo "</tr>";
            }
            while ($item = sqlsrv_fetch_array($stmtItems, SQLSRV_FETCH_ASSOC)) {
                if($item['Produs'] == null) {
                    continue;
                }
                $denumire = $item['Produs'];            
                $cantitate = $item['Cantitate'] . ' ml';
                $pret = $item['ProdusPret'];
                $total += $pret;

                echo "<tr>";
                echo "<td>" . $numar++ . "</td>";
                echo "<td>" . htmlspecialchars($denumire) . "</td>";
                echo "<td>" . htmlspecialchars($cantitate) . "</td>";
                echo "<td>" . htmlspecialchars($pret) . " RON</td>";
                echo "</tr>";
            }
            if(round($factura['Suma']) < round($total) && $numar > 1) {
                $cantitate = round(($total - $factura['Suma']) * 100 / $total) . '%';
                echo "<tr>";
                echo "<td>" . $numar++ . "</td>";
                echo "<td>" . htmlspecialchars("Discount") . "</td>";
                echo "<td>" . htmlspecialchars($cantitate) . "</td>";
                echo "<td>" . htmlspecialchars($factura['Suma'] - $total) . " RON</td>";
                echo "</tr>";
            }
            else if(round($factura['Suma']) > round($total)) {
                echo "<tr>";
                echo "<td>" . $numar++ . "</td>";
                echo "<td>" . htmlspecialchars("Alte produse si servicii") . "</td>";
                echo "<td>" . htmlspecialchars("1") . "</td>";
                echo "<td>" . htmlspecialchars($factura['Suma'] - $total) . " RON</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3">Total</th>
                <th><?= htmlspecialchars($factura['Suma']) ?> RON</th>
            </tr>
        </tfoot>
    </table>
    </div>
    <a href='/bd/dashboard/facturi.php'" class="btn btn-primary">Înapoi</a>
    <button onclick="printDiv('printableArea')" class="btn btn-secondary">Print</button>

      <div id="responseDiv" class="toast <?php echo $class; ?>" 
    style="display: <?php echo isset($message) ? 'block' : 'none'; ?>;" role="alert"
    aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
    <div class="toast-body flex-grow-1"><?php echo $message; ?></div>
    <button type="button" class="btn-close p-3" aria-label="Close"
                        onclick="hideToast()"></button></div>
</div>
</div>

    <script>
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
        function printDiv(divId) {
    var content = document.getElementById(divId).innerHTML;
    var originalContent = document.body.innerHTML;

    document.body.innerHTML = content;
    window.print();
    document.body.innerHTML = originalContent;
}
    </script>
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