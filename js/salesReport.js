function printReceipt() {
  const selectedDate = document.querySelector(
    'input[name="report_date"]'
  ).value;
  const url =
    "generate_sales_print.php?report_date=" + encodeURIComponent(selectedDate);

  const printWindow = window.open(url, "_blank", "width=800,height=600");

  printWindow.addEventListener(
    "load",
    function () {
      printWindow.focus();
      printWindow.print();

      setTimeout(() => {
        printWindow.close();
      }, 500);
    },
    true
  );
}
