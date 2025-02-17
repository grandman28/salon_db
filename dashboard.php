<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard</title>
    <link rel="stylesheet" href="/bd/styles.css" />
    <script src="dashboard.js"></script>
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
                  session_start();
                  if ($_SESSION['user'] == "admin") {
                     
                      echo '<a href="/bd/dashboard/angajati.php" class="list-group-item list-group-item-action">Angajati</a>';
                      echo '<a href="/bd/dashboard/salarii.php" class="list-group-item list-group-item-action">Salarii</a>';
                      echo '<a href="/bd/dashboard/rapoarte.php" class="list-group-item list-group-item-action">Rapoarte</a>';
                  }?>
              </div>
          </div>
          <div class="col-md-10">
              <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
                    <?php
                    
                    if(isset($_SESSION['user'])) {
                        echo "<h2>Bine ai venit, " . htmlspecialchars($_SESSION['user']) . "</h2>";
                    }
                    
                    ?>
                  <p>Aici puteți vizualiza și gestiona toate informațiile despre clienți, programări și facturi.</p>
              </main>
          </div>
      </div>
  </div>
  </body>
</html>
