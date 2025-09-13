document.addEventListener("DOMContentLoaded", function () {
  const userProfile = document.getElementById("userProfile");
  const logoutMenu = document.getElementById("logoutMenu");

  function showMenu(event) {
    event.preventDefault();

    const screenWidth = window.innerWidth;
    const screenHeight = window.innerHeight;
    const menuWidth = logoutMenu.offsetWidth;
    const menuHeight = logoutMenu.offsetHeight;

    let posX = event.pageX;
    let posY = event.pageY;

    if (posX + menuWidth > screenWidth) {
      posX -= menuWidth;
    }
    if (posY + menuHeight > screenHeight) {
      posY -= menuHeight;
    }

    logoutMenu.style.top = `${posY}px`;
    logoutMenu.style.left = `${posX}px`;
    logoutMenu.style.display = "block";
  }

  userProfile.addEventListener("contextmenu", showMenu);
  userProfile.addEventListener("click", showMenu);

  document.addEventListener("click", function (event) {
    if (
      !logoutMenu.contains(event.target) &&
      !userProfile.contains(event.target)
    ) {
      logoutMenu.style.display = "none";
    }
  });
});

function logoutUser() {
  window.location.href = "../config/logout.php";
}
