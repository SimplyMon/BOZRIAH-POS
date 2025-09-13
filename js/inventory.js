function toggleAvailability(checkbox) {
  let itemId = checkbox.getAttribute("data-id");
  let newStatus = checkbox.checked ? 1 : 0;

  fetch("../content/update_product_availability.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `item_id=${itemId}&status=${newStatus}`,
  })
    .then((response) => response.text())
    .then((result) => {
      if (result.trim() !== "success") {
        alert("Failed to update availability: " + result);
        checkbox.checked = !checkbox.checked;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    });
}

// fliter
function filterItems() {
  let input = document.getElementById("searchInput");
  let filter = input.value.toLowerCase();
  let items = document.querySelectorAll(".inventory-card");
  let clearIcon = document.getElementById("clearSearch");
  let noResultsMessage = document.getElementById("noResultsMessage");

  clearIcon.style.display = filter ? "block" : "none";

  let anyVisibleItems = false;

  items.forEach((card) => {
    let itemName = card
      .querySelector(".product-name")
      .textContent.toLowerCase();

    if (itemName.includes(filter)) {
      card.style.display = "flex";
      anyVisibleItems = true;
    } else {
      card.style.display = "none";
    }
  });

  noResultsMessage.style.display = anyVisibleItems ? "none" : "block";
}

function clearSearch() {
  let input = document.getElementById("searchInput");
  input.value = "";
  filterItems();
}

document.addEventListener("DOMContentLoaded", () => {
  let inventoryCards = document.querySelectorAll(".inventory-card");

  inventoryCards.forEach((card, index) => {
    setTimeout(() => {
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, index * 10);
  });
});
