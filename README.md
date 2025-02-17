# Salon Management System

## Description
This project is a **Salon Management System** designed to facilitate the management of a beauty salon, including appointment scheduling, client and employee management, product inventory, and invoicing. The system utilizes a **Microsoft SQL database** with a web-based interface built using **HTML, CSS, and JavaScript**. The **Bootstrap framework** is employed for a responsive and user-friendly design. The backend functionalities and database connections are handled using **PHP**.

## Technologies Used
- **Frontend**: HTML, CSS, JavaScript (Bootstrap framework)
- **Backend**: PHP
- **Database**: Microsoft SQL Server

## Features
### Database Structure
The system consists of multiple tables to store relevant information:
- **Programări**: Stores all appointment records, including client, service, employee, invoice, date, and duration.
- **Clienți**: Stores client details.
- **Angajați**: Stores employee details.
- **Salarii**: Contains salary information.
- **Login**: Stores login credentials.
- **Produse**: Stores available salon products, linked to **Programări** via an **Inventar** table (many-to-many relationship).
- **Servicii**: Stores details about available services.
- **Facturi**: Stores invoice details.

### Relationships Between Tables
- **One-to-Many Relationships**:
  - Clienți → Programări
  - Facturi → Programări
  - Angajați → Programări
  - Servicii → Programări
  - Facturi → Clienți
  - Angajați → Salarii
  - Angajați → Login
- **Many-to-Many Relationship**:
  - Produse ↔ Programări (via **Inventar** table)

## Implementation
The application consists of a web-based frontend interacting with a backend that handles business logic and database communication.

### Database Connection (PHP)
The connection to the **Microsoft SQL Server** database is established using the following PHP script:
```php
$serverName = "[machine-name]\\sqlexpress";
$connectionInfo = array("Database" => "Programari", "UID" => "user1", "PWD" => "12345");
$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}
```

### CRUD Operations
#### Insert Data
```sql
INSERT INTO Facturi (Id_Client, Data, Suma) VALUES ($id_client, $data, $suma);
INSERT INTO Clienti (Nume, Prenume, Telefon, Email) VALUES ($nume, $prenume, $telefon, $email);
```

#### Delete Data
```sql
DELETE FROM Clienti WHERE ID_Client = $id_client;
DELETE FROM Facturi WHERE ID_Factura = ?;
```

#### Update Data
```sql
UPDATE Clienti SET Nume = $nume, Prenume = $prenume WHERE ID_Client = $id_client;
UPDATE Servicii SET Nume = $nume, Pret = $pret WHERE ID_Serviciu = $id_serviciu;
```

#### Select Queries
```sql
SELECT TOP 50 F.ID_Factura, F.Data, F.Suma, C.Nume FROM Facturi F
INNER JOIN Clienti C ON F.ID_Client = C.ID_Client;
```

## How to Run the Project
1. Install **XAMPP** or a similar PHP environment.
2. Set up a **Microsoft SQL Server** database and import the provided SQL schema - **Programari.bak**.
3. Configure the database connection in the PHP files.
4. Run the project on a local server.

## Author
Gândraman Iulian

