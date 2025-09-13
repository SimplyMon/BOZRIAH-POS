// FOR CARD EXPIRATION DATE
document.getElementById("cardExpDate").addEventListener("input", function (e) {
  let value = e.target.value.replace(/\D/g, "");
  if (value.length > 4) {
    value = value.substring(0, 4) + "-" + value.substring(4);
  }
  if (value.length > 7) {
    value = value.substring(0, 7) + "-" + value.substring(7);
  }
  e.target.value = value.substring(0, 10);
});

// FOR CARD NUMBER
document.getElementById("cardNumber").addEventListener("input", function (e) {
  let value = e.target.value.replace(/\D/g, "");

  if (value.length > 5) {
    value = value.substring(0, 5) + "-" + value.substring(5);
  }

  e.target.value = value.substring(0, 11);
});
