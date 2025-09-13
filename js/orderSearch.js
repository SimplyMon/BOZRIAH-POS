function filterInvoices() {
  var input = document.getElementById("invoiceSearch");
  var filter = input.value.toLowerCase();
  var table = document.getElementById("orderTableBody");
  var rows = table.getElementsByTagName("tr");
  var rowsVisible = 0;

  var clearBtn = document.querySelector(".clear-btn");
  if (input.value.trim() !== "") {
    clearBtn.style.display = "inline";
  } else {
    clearBtn.style.display = "none";
  }

  var existingNoRecords = document.getElementById("noOrdersRow");
  if (existingNoRecords) {
    existingNoRecords.remove();
  }

  for (var i = 0; i < rows.length; i++) {
    var orderNumber = rows[i].getElementsByTagName("td")[0];
    if (orderNumber) {
      var osNumberText = orderNumber.textContent || orderNumber.innerText;
      if (osNumberText.toLowerCase().indexOf(filter) > -1) {
        rows[i].style.display = "";
        rowsVisible++;
      } else {
        rows[i].style.display = "none";
      }
    }
  }

  if (rowsVisible === 0 && input.value.trim() !== "") {
    var noRecordsRow = document.createElement("tr");
    noRecordsRow.id = "noOrdersRow";
    var noRecordsCell = document.createElement("td");
    noRecordsCell.setAttribute("colspan", 3);
    noRecordsCell.textContent = "No orders found...";
    noRecordsRow.appendChild(noRecordsCell);
    table.appendChild(noRecordsRow);
  }
}

function clearSearch() {
  var input = document.getElementById("invoiceSearch");
  input.value = "";
  filterInvoices();
}

document.getElementById("refreshBtn").addEventListener("click", function () {
  location.reload();
});
