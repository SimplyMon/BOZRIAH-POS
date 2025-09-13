const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleBtn");
const textElements = document.querySelectorAll(".sidebar-text");
const links = document.querySelectorAll(".sidebar-link");
const logo = document.querySelector(".logo");
const mainContent = document.querySelector(".main-content");

toggleBtn.addEventListener("click", () => {
  // Toggle Sidebar
  sidebar.classList.toggle("collapsed");

  if (sidebar.classList.contains("collapsed")) {
    mainContent.style.marginLeft = "100px";
    mainContent.style.width = "calc(100% - 100px)";
  } else {
    mainContent.style.marginLeft = "216px";
    mainContent.style.width = "calc(100% - 216px)";
  }

  if (sidebar.classList.contains("collapsed")) {
    sidebar.insertBefore(toggleBtn, logo.nextSibling);
  } else {
    logo.appendChild(toggleBtn);
  }

  textElements.forEach((el) => {
    el.classList.toggle("hidden");
  });

  links.forEach((link) => {
    link.classList.toggle("centered");
  });
});
