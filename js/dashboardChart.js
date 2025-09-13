document.addEventListener("DOMContentLoaded", function () {
  const totalIncomeElement = document.getElementById("totalIncome");
  const ctx = document.getElementById("incomeChart").getContext("2d");
  let incomeChart;

  function createChart(labels, incomeValues) {
    if (incomeChart) {
      incomeChart.destroy();
    }
    incomeChart = new Chart(ctx, {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Income",
            data: incomeValues,
            borderColor: "#f48734",
            backgroundColor: "rgba(244, 135, 52, 0.2)",
            fill: true,
            tension: 0.4,
            pointRadius: 6, // Dot size
            pointHoverRadius: 8,
            pointBackgroundColor: "#f48734",
            pointBorderColor: "#fff",
            pointBorderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
          tooltip: {
            enabled: true,
            backgroundColor: "rgba(50, 50, 50, 0.9)",
            titleColor: "#ffffff",
            bodyColor: "#ffcc00",
            bodyFont: {
              size: 16,
              weight: "bold",
            },
            padding: 10,
            borderRadius: 8,
            displayColors: false,
            shadowOffsetX: 2,
            shadowOffsetY: 2,
            shadowBlur: 4,
            shadowColor: "rgba(0, 0, 0, 0.3)",
            callbacks: {
              label: function (tooltipItem) {
                return `₱${tooltipItem.raw.toLocaleString()}`;
              },
            },
          },
        },
        scales: {
          x: {
            ticks: {
              font: {
                size: 16,
              },
            },
          },
          y: {
            ticks: {
              font: {
                size: 16,
              },
            },
          },
        },
      },
    });
  }

  function fetchIncome(filter = "today") {
    fetch(`../content/fetch_income.php?filter=${filter}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.length > 0) {
          let labels = data.map((entry) => entry.date);
          let incomeValues = data.map((entry) =>
            parseFloat(entry.total_income)
          );

          // Update Chart
          createChart(labels, incomeValues);

          // Update Total Income Card
          let totalIncome = incomeValues.reduce((sum, value) => sum + value, 0);
          totalIncomeElement.textContent = `₱${totalIncome.toLocaleString()}`;
        } else {
          totalIncomeElement.textContent = "₱0.00";
          createChart([], []);
        }
      })
      .catch((error) => console.error("Error fetching income data:", error));
  }

  // Fetch  Today
  const activeFilter = document
    .querySelector(".filter-buttons .active")
    .textContent.toLowerCase()
    .replace(" ", "_");
  fetchIncome(activeFilter);

  //  Filter Button
  document.querySelectorAll(".filter-buttons button").forEach((button) => {
    button.addEventListener("click", function () {
      document
        .querySelectorAll(".filter-buttons button")
        .forEach((btn) => btn.classList.remove("active"));
      this.classList.add("active");

      let filter = this.textContent.toLowerCase().replace(" ", "_");
      fetchIncome(filter);
    });
  });
});
