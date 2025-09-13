document.addEventListener("DOMContentLoaded", function() {
    let gcashDropdown = document.getElementById("gcashMobileNumber");

    if (!gcashDropdown) return;

    fetch("fetch_gcash_account.php")
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data)) return;

            gcashDropdown.innerHTML = "";

            let defaultOption = document.createElement("option");
            defaultOption.value = "";
            defaultOption.textContent = "Select GCash Account";
            gcashDropdown.appendChild(defaultOption);

            data.forEach(account => {
                let option = document.createElement("option");
                option.value = account.AccountNo;
                option.textContent = `${account.AccountName} (${account.AccountNo})`;
                gcashDropdown.appendChild(option);
            });
        })
        .catch(error => console.error("Fetch Error:", error));
});