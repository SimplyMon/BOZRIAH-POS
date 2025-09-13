document.addEventListener("DOMContentLoaded", function () {
  const totalCustomersElement = document.getElementById("totalCustomers");
  const totalOrdersElement = document.getElementById("totalOrders");

  function fetchData(url, element) {
    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        if (
          data &&
          (data.total_customers !== undefined ||
            data.total_orders !== undefined)
        ) {
          element.textContent = `${(
            data.total_customers ?? data.total_orders
          ).toLocaleString()}`;
        } else {
          element.textContent = "0";
        }
      })
      .catch((error) =>
        console.error(`Error fetching data from ${url}:`, error)
      );
  }

  function updateData(filter) {
    fetchData(
      `../content/fetch_customer.php?filter=${filter}`,
      totalCustomersElement
    );
    fetchData(
      `../content/fetch_total_orders.php?filter=${filter}`,
      totalOrdersElement
    );
  }

  const activeFilter = document
    .querySelector(".filter-buttons .active")
    .textContent.toLowerCase()
    .replace(" ", "_");
  updateData(activeFilter);

  document.querySelectorAll(".filter-buttons button").forEach((button) => {
    button.addEventListener("click", function () {
      document
        .querySelectorAll(".filter-buttons button")
        .forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      let filter = this.textContent.toLowerCase().replace(" ", "_");
      updateData(filter);
    });
  });
});
