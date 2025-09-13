document.addEventListener("DOMContentLoaded", function () {
  // Store payment amounts globally
  let paymentAmounts = {};
  let currentPaymentMethod = document.getElementById("paymentMethod").value;

  // === ORDER SELECTION FUNCTIONALITY ===
  document.querySelectorAll(".order-row").forEach((row) => {
    row.addEventListener("click", function () {
      let seqNo = this.getAttribute("data-seqno");

      if (!seqNo) {
        alert("Error: Missing order sequence number.");
        return;
      }

      fetch(`fetch_order_details.php?seqno=${encodeURIComponent(seqNo)}`)
        .then((response) => {
          if (!response.ok) throw new Error("Failed to fetch order details.");
          return response.json();
        })
        .then((data) => updateOrderDetails(data, seqNo))
        .catch((error) => console.error("Fetch error:", error));
    });
  });

  function updateOrderDetails(data, seqNo) {
    let orderDetails = document.getElementById("orderDetails");
    let grandTotal = 0;
    orderDetails.innerHTML = "";

    if (data.length > 0) {
      let isTakeout = data[0].isTakeout || 0;
      data.forEach((order) => {
        let amount = parseFloat(order.Amount);
        let quantity = parseFloat(order.Quantity);
        grandTotal += amount * quantity;

        orderDetails.innerHTML += `
          <tr>
            <td class="left-align">${order.ItemDesc}</td>
            <td class="center-align">${quantity}</td>
            <td class="right-align">${(amount * quantity).toFixed(2)}</td>
          </tr>`;
      });

      let vatableAmount = grandTotal / 1.12;
      let vatAmount = (grandTotal * 0.12) / 1.12;

      document.getElementById("grandTotal").textContent = grandTotal.toFixed(2);
      document
        .getElementById("grandTotal")
        .setAttribute("data-original-total", grandTotal.toFixed(2));
      document.getElementById("vatableAmount").textContent =
        vatableAmount.toFixed(2);
      document.getElementById("vatAmount").textContent = vatAmount.toFixed(2);
      document.getElementById("totalAmount").textContent =
        grandTotal.toFixed(2);

      let orderModal = document.getElementById("orderModal");
      orderModal.setAttribute("data-seqno", seqNo);
      orderModal.setAttribute("data-istakeout", isTakeout);
    } else {
      orderDetails.innerHTML = "<tr><td colspan='3'>No details found</td></tr>";
    }

    document.getElementById("orderModal").style.display = "block";
  }

  // === MODAL FUNCTIONALITY ===
  function closeModal(modalId) {
    document.getElementById(modalId).style.display = "none";
    if (modalId === "paymentModal" && overlay) {
      overlay.style.display = "none";
    }
  }

  document
    .querySelector(".close-btn")
    .addEventListener("click", () => closeModal("orderModal"));
  document
    .querySelector(".close-payment-btn")
    .addEventListener("click", () => closeModal("paymentModal"));

  window.addEventListener("click", function (event) {
    let orderModal = document.getElementById("orderModal");
    if (event.target === orderModal) {
      closeModal("orderModal");
    }
  });

  // === RECEIVE PAYMENT FUNCTIONALITY ===
  let overlay = createOverlay();
  let paymentModal = document.getElementById("paymentModal");
  let errorModal = document.getElementById("errorModal");

  function createOverlay() {
    let overlay = document.createElement("div");
    overlay.id = "paymentOverlay";
    overlay.className = "modal-overlay";
    overlay.style.display = "none";
    document.body.appendChild(overlay);
    return overlay;
  }

  document.getElementById("confirmBtn").addEventListener("click", function () {
    let seqNo = document
      .getElementById("orderModal")
      .getAttribute("data-seqno");

    if (!seqNo) {
      alert("Please select an order first.");
      return;
    }

    closeModal("orderModal");
    paymentModal.style.display = "block";
    overlay.style.display = "block";

    let originalGrandTotal =
      parseFloat(
        document
          .getElementById("grandTotal")
          .getAttribute("data-original-total")
      ) || 0;
    document.getElementById("grandTotalPayment").value =
      originalGrandTotal.toFixed(2);
    document.getElementById("grandTotal").textContent = parseFloat(
      document.getElementById("grandTotalPayment").value
    ).toFixed(2);

    updateTotalPaid();

    let defaultCashBtn = document.querySelector(
      ".payment-method-btn[data-method='CASH']"
    );
    if (defaultCashBtn) defaultCashBtn.click();
  });

  document.querySelectorAll(".payment-method-btn").forEach((button) => {
    button.addEventListener("click", function () {
      const currentAmountInput = document.getElementById("customerPayment");
      paymentAmounts[currentPaymentMethod] =
        parseFloat(currentAmountInput.value) || 0;

      let selectedMethod = this.getAttribute("data-method");
      document.getElementById("paymentMethod").value = selectedMethod;
      currentPaymentMethod = selectedMethod;
      document.getElementById("customerPayment").value =
        paymentAmounts[selectedMethod] || "";
      document.getElementById("customerPayment").focus();

      showHidePaymentFields(selectedMethod);
      updateTotalPaid();
    });
  });

  document
    .getElementById("customerPayment")
    .addEventListener("input", function () {
      paymentAmounts[currentPaymentMethod] = parseFloat(this.value) || 0;
      updateTotalPaid();
    });

  function calculateFinalTotal() {
    let total =
      parseFloat(document.getElementById("grandTotalPayment").value) || 0;
    let finalTotal = total;

    if (appliedDiscountDetails) {
      if (appliedDiscountDetails.percentage) {
        const discountRate =
          parseFloat(appliedDiscountDetails.percentage) / 100;
        finalTotal -= total * discountRate;
      } else if (appliedDiscountDetails.amount) {
        finalTotal -= parseFloat(appliedDiscountDetails.amount);
      }
      finalTotal = Math.max(0, finalTotal);
    }
    return finalTotal;
  }

  function updateTotalPaid() {
    let grandTotal = calculateFinalTotal();
    let totalPaid = Object.values(paymentAmounts).reduce(
      (sum, val) => sum + (parseFloat(val) || 0),
      0
    );
    let remainingBalance = Math.max(grandTotal - totalPaid, 0);
    document.getElementById("remainingBalance").value =
      remainingBalance.toFixed(2);

    document.getElementById("grandTotal1").value = grandTotal.toFixed(2);

    let changeAmount = totalPaid > grandTotal ? totalPaid - grandTotal : 0;
    document.getElementById("changeAmount").value = changeAmount.toFixed(2);
  }

  function showHidePaymentFields(method) {
    document.getElementById("gcashFields").style.display =
      method === "GCASH" ? "block" : "none";
    document.getElementById("cardFields").style.display =
      method === "CARD" ? "block" : "none";
    document.getElementById("paymentFields").style.display =
      method === "PAYPAL" || method === "MAYA" ? "block" : "none";
    document.getElementById("otherPaymentMethods").style.display =
      method === "PAYPAL" || method === "MAYA" ? "block" : "none";
  }

  document
    .getElementById("confirmPaymentBtn")
    .addEventListener("click", function () {
      let seqNo = document
        .getElementById("orderModal")
        .getAttribute("data-seqno");
      let total =
        parseFloat(document.getElementById("grandTotalPayment").value) || 0;
      let finalTotal = total;

      if (appliedDiscountDetails) {
        if (appliedDiscountDetails.percentage) {
          const discountRate =
            parseFloat(appliedDiscountDetails.percentage) / 100;
          finalTotal -= total * discountRate;
        } else if (appliedDiscountDetails.amount) {
          finalTotal -= parseFloat(appliedDiscountDetails.amount);
        }
        finalTotal = Math.max(0, finalTotal);
      }

      let totalPaid = Object.values(paymentAmounts).reduce(
        (sum, val) => sum + (parseFloat(val) || 0),
        0
      );
      let change =
        parseFloat(document.getElementById("changeAmount").value) || 0;

      if (totalPaid < finalTotal) {
        document.getElementById("errorModal").style.display = "block";
        document.getElementById("errorMessage").textContent =
          "Insufficient payment.";
        return;
      }

      let paymentDetails = [];

      for (const method in paymentAmounts) {
        if (paymentAmounts[method] > 0) {
          let details = { method: method, amount: paymentAmounts[method] };

          if (method === "GCASH") {
            details.mobileNumber =
              document.getElementById("gcashMobileNumber").value;
            details.referenceNo =
              document.getElementById("gcashReferenceNo").value;

            if (!details.mobileNumber.match(/^\d{11}$/)) {
              alert(
                "Invalid GCash Mobile Number. It should be 11 digits for the GCash payment."
              );
              return;
            }

            if (details.referenceNo.trim() === "") {
              alert(
                "Please enter a valid GCash Reference Number for the GCash payment."
              );
              return;
            }
          } else if (method === "CARD") {
            details.cardType = document.getElementById("cardType").value;
            details.cardNumber = document.getElementById("cardNumber").value;
            details.cardExpDate = document.getElementById("cardExpDate").value;

            if (details.cardType === "") {
              alert("Please select a Card Type for the Card payment.");
              return;
            }

            if (
              details.cardExpDate.trim() === "" ||
              !/^\d{4}-\d{2}-\d{2}$/.test(details.cardExpDate)
            ) {
              alert(
                "Invalid Expiration Date. Use format YYYY-MM-DD for the Card payment."
              );
              return;
            }
          }

          paymentDetails.push(details);
        }
      }

      console.log("paymentDetails:", paymentDetails);

      fetch("confirm_payment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          osehSeqNo: seqNo,
          grandtotal: finalTotal.toFixed(2),
          originalGrandtotal: total.toFixed(2),
          payments: JSON.stringify(paymentDetails),
          customerChange: change.toFixed(2),
          DiscCode: appliedDiscountDetails ? appliedDiscountDetails.code : null,
          Percentage: appliedDiscountDetails
            ? appliedDiscountDetails.percentage
            : null,
          Discountamount: appliedDiscountDetails
            ? appliedDiscountDetails.amount
            : null,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.status === "success") {
            document.getElementById("paymentModal").style.display = "none";
            document.getElementById("paymentOverlay").style.display = "none";
            document.getElementById("successModal").style.display = "block";

            // Trigger receipt printing
            if (data.InvoiceNo) {
              sessionStorage.setItem("invoiceToPrint", data.InvoiceNo);
            } else {
              console.error("Invoice number not received for printing.");
            }
          } else {
            alert("Error: " + data.message);
          }
        })
        .catch((error) => console.error("Fetch Error:", error));
    });

  document
    .getElementById("closeSuccessModal")
    .addEventListener("click", function () {
      document.getElementById("successModal").style.display = "none";
      const printFrame = document.createElement("iframe");
      printFrame.style.display = "none";
      document.body.appendChild(printFrame);

      printFrame.src = `generate_receipt.php?invoiceNo=${encodeURIComponent(
        sessionStorage.getItem("invoiceToPrint")
      )}`;

      printFrame.onload = function () {
        printFrame.contentWindow.print();

        setTimeout(() => {
          document.body.removeChild(printFrame);
          sessionStorage.removeItem("invoiceToPrint");
          location.reload();
        }, 1000);
      };
    });

  document
    .querySelector(".close-error-btn")
    .addEventListener("click", function () {
      errorModal.style.display = "none";
    });

  document
    .getElementById("otherPaymentBtn")
    .addEventListener("click", function () {
      const otherPaymentMethodsDiv = document.getElementById(
        "otherPaymentMethods"
      );
      otherPaymentMethodsDiv.style.display =
        otherPaymentMethodsDiv.style.display === "none" ? "block" : "none";
    });

  showHidePaymentFields(document.getElementById("paymentMethod").value);
});
