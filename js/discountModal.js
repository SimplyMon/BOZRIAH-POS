// discount_logic.js

document.addEventListener("DOMContentLoaded", function () {
  const discountModal = document.getElementById("discountModal");
  const applyDiscountBtn = document.getElementById("applyDiscountBtn");
  const closeDiscountBtn = document.querySelector(".close-discount-btn");
  const discountOptionsTableBody = document.getElementById("discountOptions");
  const orderModal = document.getElementById("orderModal");
  const orderSummary = document.getElementById("orderSummary");

  let currentSeqNoForDiscount = null;

  // Store discount details globally
  window.appliedDiscountDetails = null;

  applyDiscountBtn.addEventListener("click", function () {
    const seqNo = orderModal.getAttribute("data-seqno");

    if (!seqNo) {
      alert("Please select an order first to apply a discount.");
      return;
    }

    currentSeqNoForDiscount = seqNo;
    fetchDiscountOptions();
    discountModal.style.display = "block";
  });

  closeDiscountBtn.addEventListener("click", function () {
    discountModal.style.display = "none";
  });

  function fetchDiscountOptions() {
    fetch("fetch_discounts.php")
      .then((response) => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then((data) => populateDiscountOptions(data))
      .catch((error) => {
        console.error("Error fetching discount options:", error);
        alert("Failed to fetch discount options.");
      });
  }

  function populateDiscountOptions(discounts) {
    discountOptionsTableBody.innerHTML = "";

    discounts.forEach((discount) => {
      const row = discountOptionsTableBody.insertRow();
      // const codeCell = row.insertCell();
      const typeCell = row.insertCell();
      const percentageCell = row.insertCell();
      const maxDiscountCell = row.insertCell();
      const actionCell = row.insertCell();

      // codeCell.textContent = discount.DiscCode;
      typeCell.textContent = discount.DiscType;
      percentageCell.textContent = discount.Percentage.includes("%")
        ? parseFloat(discount.Percentage).toFixed(0) + "%"
        : parseFloat(discount.Percentage).toFixed(0);
      maxDiscountCell.textContent =
        discount.MaxDiscount !== null
          ? parseFloat(discount.MaxDiscount).toFixed(2)
          : "N/A";

      const applyButton = document.createElement("button");
      applyButton.textContent = "Apply";
      applyButton.className = "action-btn";
      applyButton.addEventListener("click", function () {
        applySelectedDiscount(discount);
      });

      actionCell.appendChild(applyButton);
    });
  }

  function applySelectedDiscount(discount) {
    const grandTotalElement = document.getElementById("grandTotal");
    const vatableAmountElement = document.getElementById("vatableAmount");
    const vatAmountElement = document.getElementById("vatAmount");
    const totalAmountElement = document.getElementById("totalAmount");

    let currentGrandTotal = parseFloat(grandTotalElement.textContent);
    let discountedGrandTotal = currentGrandTotal;
    let discountAmount = 0;
    let maxDiscount = parseFloat(discount.MaxDiscount);
    const hasMaxDiscount = !isNaN(maxDiscount);

    if (discount.Percentage) {
      const percentage = parseFloat(discount.Percentage);
      discountAmount = currentGrandTotal * (percentage / 100);

      // Apply max discount
      if (hasMaxDiscount && maxDiscount > 0 && discountAmount > maxDiscount) {
        discountAmount = maxDiscount;
      }

      discountedGrandTotal -= discountAmount;
    } else if (
      discount.DiscountAmount !== undefined &&
      discount.DiscountAmount !== null
    ) {
      discountAmount = parseFloat(discount.DiscountAmount);

      // Apply max discount
      if (hasMaxDiscount && maxDiscount > 0 && discountAmount > maxDiscount) {
        discountAmount = maxDiscount;
      }

      discountedGrandTotal -= discountAmount;
    }

    grandTotalElement.textContent = discountedGrandTotal.toFixed(2);
    const newVatableAmount = discountedGrandTotal / 1.12;
    const newVatAmount = (discountedGrandTotal * 0.12) / 1.12;

    vatableAmountElement.textContent = newVatableAmount.toFixed(2);
    vatAmountElement.textContent = newVatAmount.toFixed(2);
    totalAmountElement.textContent = discountedGrandTotal.toFixed(2);

    discountModal.style.display = "none";

    // Store discount details globally
    window.appliedDiscountDetails = {
      code: discount.DiscCode,
      type: discount.DiscType,
      percentage: discount.Percentage,
      amount: discountAmount.toFixed(2),
    };

    updateOrderSummaryDisplay();
  }

  function updateOrderSummaryDisplay() {
    const discountDisplayRow = document.getElementById("appliedDiscountRow");
    const summaryTable = orderSummary.querySelector("table");

    if (!window.appliedDiscountDetails) {
      if (discountDisplayRow) {
        discountDisplayRow.remove();
      }
      return;
    }

    if (!discountDisplayRow) {
      const newRow = summaryTable.insertRow(0);
      newRow.id = "appliedDiscountRow";
      const discountLabelCell = newRow.insertCell();
      const discountAmountCell = newRow.insertCell();

      discountLabelCell.classList.add("right-align");
      discountAmountCell.classList.add("right-align");

      discountLabelCell.innerHTML = `<button class="remove-discount-btn">✖</button> ${
        window.appliedDiscountDetails.type
      } (${parseFloat(window.appliedDiscountDetails.percentage ?? "0").toFixed(
        0
      )}%)`;
      discountAmountCell.innerHTML =
        '<span id="appliedDiscount">-' +
        window.appliedDiscountDetails.amount +
        "</span>";

      const removeButton = newRow.querySelector(".remove-discount-btn");
      removeButton.addEventListener("click", removeAppliedDiscount);
    } else {
      const discountLabelCell = discountDisplayRow.cells[0];
      const discountAmountCell = discountDisplayRow.cells[1];
      discountLabelCell.innerHTML = `<button class="remove-discount-btn">✖</button> ${
        window.appliedDiscountDetails.type
      } (${parseFloat(window.appliedDiscountDetails.percentage ?? "0").toFixed(
        0
      )}%)`;
      discountAmountCell.innerHTML =
        '<span id="appliedDiscount">-' +
        window.appliedDiscountDetails.amount +
        "</span>";
      const removeButton = discountDisplayRow.querySelector(
        ".remove-discount-btn"
      );
      removeButton.addEventListener("click", removeAppliedDiscount);
    }

    const vatableAmountElement = document.getElementById("vatableAmount");
    const vatAmountElement = document.getElementById("vatAmount");
    const totalAmountElement = document.getElementById("totalAmount");
    const grandTotalElement = document.getElementById("grandTotal");

    let currentGrandTotalBeforeDiscount =
      parseFloat(grandTotalElement.getAttribute("data-original-total")) ||
      parseFloat(grandTotalElement.textContent) +
        parseFloat(window.appliedDiscountDetails.amount);

    let discountedGrandTotal =
      currentGrandTotalBeforeDiscount -
      parseFloat(window.appliedDiscountDetails.amount);

    grandTotalElement.textContent = discountedGrandTotal.toFixed(2);

    const newVatableAmount = discountedGrandTotal / 1.12;
    const newVatAmount = (discountedGrandTotal * 0.12) / 1.12;

    vatableAmountElement.textContent = newVatableAmount.toFixed(2);
    vatAmountElement.textContent = newVatAmount.toFixed(2);
    totalAmountElement.textContent = discountedGrandTotal.toFixed(2);
  }

  function removeAppliedDiscount() {
    const grandTotalElement = document.getElementById("grandTotal");
    const vatableAmountElement = document.getElementById("vatableAmount");
    const vatAmountElement = document.getElementById("vatAmount");
    const totalAmountElement = document.getElementById("totalAmount");
    const discountDisplayRow = document.getElementById("appliedDiscountRow");

    if (window.appliedDiscountDetails) {
      const previouslyDiscountedAmount = parseFloat(
        window.appliedDiscountDetails.amount
      );
      let currentGrandTotal = parseFloat(grandTotalElement.textContent);
      const originalTotal =
        parseFloat(grandTotalElement.getAttribute("data-original-total")) ||
        currentGrandTotal + previouslyDiscountedAmount;

      grandTotalElement.textContent = originalTotal.toFixed(2);
      vatableAmountElement.textContent = (originalTotal / 1.12).toFixed(2);
      vatAmountElement.textContent = ((originalTotal * 0.12) / 1.12).toFixed(2);
      totalAmountElement.textContent = originalTotal.toFixed(2);

      window.appliedDiscountDetails = null;
      if (discountDisplayRow) {
        discountDisplayRow.remove();
      }
    }
  }
});
