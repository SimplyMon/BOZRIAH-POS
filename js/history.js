function openTransactionModal(invoiceNo, seqNo) {
  const modal = document.getElementById("transactionModal");
  modal.style.display = "flex";

  fetch(`fetch_transaction_details.php?seqNo=${seqNo}`)
    .then((response) => response.text())
    .then((data) => {
      document.getElementById("modalContent").innerHTML = data;
    })
    .catch((error) => {
      console.error("Error fetching transaction details:", error);
      document.getElementById("modalContent").innerHTML =
        "<p>Error loading details.</p>";
    });

  modal.addEventListener("click", function (event) {
    if (event.target === modal) {
      closeTransactionModal();
    }
  });
}

function closeTransactionModal() {
  const modal = document.getElementById("transactionModal");
  modal.style.display = "none";
}

// FILTEr
function filterInvoices() {
  let input = document.getElementById("invoiceSearch");
  let filter = input.value.toUpperCase();
  let table = document.getElementById("transactionTable");
  let tr = table.getElementsByTagName("tr");
  let noOrdersMessage = document.querySelector(".no-orders-message");
  let found = false;

  for (let i = 1; i < tr.length; i++) {
    let td = tr[i].getElementsByTagName("td")[0];
    if (td) {
      let txtValue = td.textContent || td.innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
        found = true;
      } else {
        tr[i].style.display = "none";
      }
    }
  }

  if (!found) {
    noOrdersMessage.style.display = "block";
  } else {
    noOrdersMessage.style.display = "none";
  }
}

function clearSearch() {
  var input = document.getElementById("invoiceSearch");
  input.value = "";
  filterInvoices();
}
