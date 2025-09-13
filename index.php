<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BOZRIAH | LOGIN</title>
  <link rel="stylesheet" href="./styles/login.css">
  <link rel="icon" type="image/x-icon" href="./assets/products/logo.png">
</head>

<body>

  <div class="login-container">
    <div class="logo">
      <img src="./assets/products/logo.png" alt="Logo">
      <h1 class="sidebar-text">Bozriah</h1>
    </div>
    <div class="login-form">
      <h2>Login</h2>
      <form id="loginForm">
        <input type="text" id="userID" placeholder="User ID" required>

        <div class="password-container">
          <input type="password" id="password" placeholder="Password" required>
          <span class="toggle-password" onclick="togglePassword()">👁</span>
        </div>

        <button type="submit">Login</button>
      </form>
      <div id="errorMessage" class="error"></div>
    </div>
  </div>



  <script>
    document.getElementById("loginForm").addEventListener("submit", function(e) {
      e.preventDefault();
      let userID = document.getElementById("userID").value;
      let password = document.getElementById("password").value;

      fetch("./config/login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `userID=${encodeURIComponent(userID)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = data.redirect;
          } else {
            document.getElementById("errorMessage").textContent = data.error;
          }
        })
        .catch(error => console.error("Error:", error));
    });
  </script>

  <script>
    function togglePassword() {
      let passwordInput = document.getElementById("password");
      let toggleIcon = document.querySelector(".toggle-password");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.textContent = "👁"; 
      } else {
        passwordInput.type = "password";
        toggleIcon.textContent = "👁"; 
      }
    }

    document.getElementById("loginForm").addEventListener("submit", function(e) {
      e.preventDefault();
      let userID = document.getElementById("userID").value;
      let password = document.getElementById("password").value;

      fetch("./config/login.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `userID=${encodeURIComponent(userID)}&password=${encodeURIComponent(password)}`
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = data.redirect;
          } else {
            document.getElementById("errorMessage").textContent = data.error;
          }
        })
        .catch(error => console.error("Error:", error));
    });
  </script>

</body>

</html>