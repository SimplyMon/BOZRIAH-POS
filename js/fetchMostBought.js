document.addEventListener("DOMContentLoaded", function () {
  function fetchMostBoughtProducts(filter = "today") {
    fetch(`../content/fetch_most_bought.php?filter=${filter}`)
      .then((response) => response.json())
      .then((data) => {
        const mostBoughtContainer = document.querySelector(".time-period");
        mostBoughtContainer.innerHTML = "";

        if (data.length > 0) {
          let maxQuantity = Math.max(...data.map((item) => item.TotalQuantity));

          data.forEach((item) => {
            let percentage = ((item.TotalQuantity / maxQuantity) * 100).toFixed(
              1
            );
            mostBoughtContainer.innerHTML += `
          <div class="product-row">
            <span class="product-name" style ="font-size: 16px;">${item.Description}</span>
            <div class="product-bar"><span style="width: ${percentage}%;"></span></div>
            <span class="product-percentage" style ="font-size: 16px;">${percentage}%</span>
          </div>
        `;
          });
        } else {
          mostBoughtContainer.innerHTML = "<p>No data available.</p>";
        }
      })
      .catch((error) =>
        console.error("Error fetching most bought products:", error)
      );
  }

  fetchMostBoughtProducts();

  document.querySelectorAll(".filter-buttons button").forEach((button) => {
    button.addEventListener("click", function () {
      document
        .querySelectorAll(".filter-buttons button")
        .forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      let filter = this.textContent.toLowerCase().replace(" ", "_");
      fetchMostBoughtProducts(filter);
    });
  });
});
