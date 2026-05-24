
let monthlyCanvas  = document.getElementById('monthlyChart');
let categoryCanvas = document.getElementById('categoryChart');

if (monthlyCanvas !== null && categoryCanvas !== null) {

    // Přečte data z hidden inputů v php jako json a parsne je na pole
    let categoryLabels  = JSON.parse(document.getElementById('data-category-labels').value);
    let categoryTotals  = JSON.parse(document.getElementById('data-category-totals').value);
    let monthlyLabels   = JSON.parse(document.getElementById('data-monthly-labels').value);
    let monthlyIncome   = JSON.parse(document.getElementById('data-monthly-income').value);
    let monthlyExpenses = JSON.parse(document.getElementById('data-monthly-expenses').value);

    // sloupcový graf příjmy vs výdaje 
    let monthlyCtx = monthlyCanvas.getContext('2d');

    new Chart(monthlyCtx, {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [
                {
                    label: 'Příjmy',
                    data: monthlyIncome,
                    backgroundColor: 'rgba(46, 204, 113, 0.7)',
                    borderColor: '#27ae60',
                    borderWidth: 1
                },
                {
                    label: 'Výdaje',
                    data: monthlyExpenses,
                    backgroundColor: 'rgba(231, 76, 60, 0.7)',
                    borderColor: '#c0392b',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString('cs-CZ') + ' Kč';
                        }
                    }
                }
            }
        }
    });

    // koláčový graf - druhy výdajů
    let categoryCtx = categoryCanvas.getContext('2d');

    let pieColors = [
        '#4e9af1', '#e74c3c', '#2ecc71','#9b59b6', '#1abc9c', '#e67e22', '#34495e','#e91e63', '#00bcd4'
    ];

    new Chart(categoryCtx, {
        type: 'pie',
        data: {
            labels: categoryLabels,
            datasets: [
                {
                    data: categoryTotals,
                    backgroundColor: pieColors,
                    borderWidth: 2,
                    borderColor: '#fff'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed;
                            return ' ' + value.toLocaleString('cs-CZ') + ' Kč';
                        }
                    }
                }
            }
        }
    });
}

// editace v historii

function startEdit(id) {
    // Schování normálních buněk
    let viewCells = document.querySelectorAll('.view-' + id);
    for (let i = 0; i < viewCells.length; i++) {
        viewCells[i].style.display = 'none';
    }

    // Zobrazení editovacích buněk
    let editCells = document.querySelectorAll('.edit-' + id);
    for (let i = 0; i < editCells.length; i++) {
        editCells[i].style.display = '';
    }

    // Vyměnění tlačítek - schování Edit a Delete, zobrazení Save a Cancel
    document.querySelector('#row-' + id + ' .btn-edit').style.display   = 'none';
    document.querySelector('#row-' + id + ' .btn-save').style.display   = '';
    document.querySelector('#row-' + id + ' .btn-cancel').style.display = '';
    document.querySelector('#row-' + id + ' .btn-delete').style.display = 'none';
}

function cancelEdit(id) {
    // Zobrazení normálních buněk
    let viewCells = document.querySelectorAll('.view-' + id);
    for (let i = 0; i < viewCells.length; i++) {
        viewCells[i].style.display = '';
    }

    // Schování editovacích buněk
    let editCells = document.querySelectorAll('.edit-' + id);
    for (let i = 0; i < editCells.length; i++) {
        editCells[i].style.display = 'none';
    }

    // Vyměnění tlačítek do normálu - zobrazení Edit a Delete, schování Save a Cance
    document.querySelector('#row-' + id + ' .btn-edit').style.display   = '';
    document.querySelector('#row-' + id + ' .btn-save').style.display   = 'none';
    document.querySelector('#row-' + id + ' .btn-cancel').style.display = 'none';
    document.querySelector('#row-' + id + ' .btn-delete').style.display = '';
}

function saveEdit(id) {

    let date = document.getElementById('edit-date-' + id).value;
    if (date === '') {
        alert("Datum není nastavené");
        return; 
    }


    // získá všechna data z inline inputu
    let data = {
        action:      'update',
        id:          id,
        date:        document.getElementById('edit-date-'   + id).value,
        description: document.getElementById('edit-desc-'   + id).value,
        category:    document.getElementById('edit-cat-'    + id).value,
        type:        document.getElementById('edit-type-'   + id).value,
        amount:      document.getElementById('edit-amount-' + id).value
    };

    
    let body = new URLSearchParams(data).toString();

    
    fetch('/sprava_rozpoctu/historie.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: body
    })
    .then(function(response) {
        return response.json();
    })
    .then(function(json) {
        if (json.success) {
            // Reloadne stránku aby se zobrazily data
            window.location.reload();
        }
    })
    .catch(function(error) {
        alert('Chyba při ukládání, zkuste to znovu.');
    });
}


function switchCategories(type) {

    let incomeSelect = document.getElementById("category-income");
    let expenseSelect = document.getElementById("category-expense");

    if (!incomeSelect || !expenseSelect) {return;}

    let isIncome = type === "income";

    incomeSelect.style.display = isIncome ? "" : "none";
    expenseSelect.style.display = isIncome ? "none" : "";
    incomeSelect.disabled = !isIncome;
    expenseSelect.disabled = isIncome;
}


document.querySelectorAll("input[name='type']").forEach(function(radio) {
    radio.addEventListener("change", function() {
        switchCategories(this.value);
    });
});

let checkedRadio = document.querySelector("input[name='type']:checked");
if (checkedRadio) {
    switchCategories(checkedRadio.value);
}