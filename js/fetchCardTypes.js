document.addEventListener("DOMContentLoaded", function () {
  function fetchCardTypes() {
    fetch("fetch_card_types.php")
      .then((response) => response.json())
      .then((data) => {
        let cardTypeSelect = document.getElementById("cardType");
        cardTypeSelect.innerHTML = '<option value="">Select Card Type</option>';
        data.forEach((card) => {
          cardTypeSelect.innerHTML += `<option value="${card.CardType}">${card.CreditCardName}</option>`;
        });
      })
      .catch((error) => console.error("Error fetching card types:", error));
  }

  // Fetch card t
  fetchCardTypes();

  document
    .getElementById("paymentMethod")
    .addEventListener("change", function () {
      let cardFields = document.getElementById("cardFields");

      if (this.value === "CARD") {
        cardFields.style.display = "block";
      } else {
        cardFields.style.display = "none";
      }
    });
});
