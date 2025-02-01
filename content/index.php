<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '../config/dbconn.php';

session_start();
if (!isset($_SESSION['user'])) {
  header("Location: ../index.php");
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">


<!-- TEST -->

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>BOZRIAH Dashboard</title>
  <link rel="icon" type="image/x-icon" href="../assets/products/logo.png">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../styles/index.css">
  <link rel="stylesheet" href="../styles/sidebar.css">
</head>

<body>
  <!-- Sidebar -->
  <div id="sidebar">
    <div class="logo">
      <img src="../assets/products/logo.png" alt="Logo">
      <h1 class="sidebar-text">Bozriah</h1>
      <button id="toggleBtn">☰</button>

    </div>
    <!-- nav linksd -->
    <a href="./" class="sidebar-link active" style="margin-top: 45px; background: #f48734;color: white;">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
      </svg>
      <span class="sidebar-text">Dashboard</span>
    </a>

    <!-- Orders -->
    <a href="./orders.php" class="sidebar-link">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M9 12l2 2 4-4M9 6h6m-3-3h3a2 2 0 012 2v14a2 2 0 01-2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h3" />
      </svg>
      <span class="sidebar-text">Orders</span>
    </a>

    <!-- Inventory -->
    <a href="./inventory.php" class="sidebar-link">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 10l1.5-4.5a2 2 0 011.9-1.5h11.2a2 2 0 011.9 1.5L21 10m-2 0H5m14 0v7a2 2 0 01-2 2H7a2 2 0 01-2-2v-7m5 0V5h4v5m-4 0h4" />
      </svg>
      <span class="sidebar-text">Items Available</span>
    </a>

    <!-- Accounts -->
    <a href="./accounts.php" class="sidebar-link">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="8" r="4"></circle>
        <path d="M6 20c0-4 3-6 6-6s6 2 6 6"></path>
      </svg>
      <span class="sidebar-text">Accounts</span>
    </a>

    <!-- History -->
    <a href="./history.php" class="sidebar-link">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M12 8v4l3 3m6-3a9 9 0 11-6.219-8.731m-.281-1.269V4m0-4h4m-4 0h-4" />
      </svg>
      <span class="sidebar-text">History</span>
    </a>

    <!-- Sales Report -->
    <a href="./sales-report.php" class="sidebar-link">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M3 3v18h18M9 17v-6m4 6v-4m4 4v-8" />
      </svg>
      <span class="sidebar-text">Sales Report</span>
    </a>


    <!-- User Profile -->
    <div class="user-profile" id="userProfile">
      <img src="../assets/products/default.webp" alt="Profile Picture" class="profile-img">
      <div class="user-info">
        <p class="user-name">
          <?php echo htmlspecialchars($_SESSION['user']['FirstName'] . ' ' . $_SESSION['user']['LastName']); ?>
        </p>
        <p class="user-role">
          <?php echo htmlspecialchars($_SESSION['user']['Role']); ?>
        </p>
      </div>
    </div>

    <!-- Custom Logout Context Menu -->
    <div id="logoutMenu" class="logout-menu">
      <ul>
        <li onclick="logoutUser()">Logout</li>
      </ul>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="dashboard-header">
      <div class="dashboard-subheader">
        <h1 style="font-size: 24px;">BOZRIAH Dashboard</h1>
      </div>


      <div class="filter-buttons">
        <button>Today</button>
        <button>This Week</button>
        <button>This Month</button>
        <button class="active">This Year</button>
      </div>
    </div>

    <div class="stats-container">
      <div class="card">
        <h3>Total Income:</h3>
        <span id="totalIncome">₱0.00</span>
        <div class="card-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 4v16" />
            <path d="M6 4h6a4 4 0 0 1 0 8H6" />
            <path d="M3 6.5h15.5" />
            <path d="M3 9h15.5" />
          </svg>



        </div>
      </div>
      <div class="card">
        <h3>Customers:</h3>
        <span id="totalCustomers">0</span>
        <div class="card-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <!-- Center Person -->
            <circle cx="12" cy="7" r="4"></circle>
            <path d="M8 21v-2a4 4 0 0 1 8 0v2"></path>

            <!-- Left Person -->
            <circle cx="5" cy="10" r="3"></circle>
            <path d="M2 21v-2a4 4 0 0 1 6-3"></path>

            <!-- Right Person -->
            <circle cx="19" cy="10" r="3"></circle>
            <path d="M16 21v-2a4 4 0 0 1 6-3"></path>
          </svg>

        </div>
      </div>

      <div class="card">
        <h3>Total Orders:</h3>
        <span id="totalOrders">0</span>
        <div class="card-icon">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2L3 7v13a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7l-3-5z"></path>
            <path d="M3 7h18"></path>
            <path d="M16 10a4 4 0 0 1-8 0"></path>
          </svg>

        </div>
      </div>
    </div>

    <div class="chart-section">
      <div class="chart-container">
        <canvas id="incomeChart"></canvas>
      </div>
      <div class="most-bought">
        <h3>Most Bought Products</h3>
        <div class="time-period">
        </div>
      </div>

    </div>

  </div>

  <script src="../js/sidebar.js"></script>
  <script src="../js/dashboardChart.js"></script>
  <script src="../js/fetchTotalOrders.js"></script>
  <script src="../js/fetchMostBought.js"></script>
  <script src="../js/logout.js"></script>

</body>

</html>