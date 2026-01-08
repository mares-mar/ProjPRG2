let users = [];
let nextUserId = 0;
let currentUser = null;
let transactions = [];
let nextTransactionId = 0;

function showUsers() {
    let userList = document.getElementById("userList");
    
    userList.innerHTML = "";

    if(users.length == 0){
        userList.innerHTML = "Zatím žádní uživatelé";
        return;
    }

    for(let user of users){
        userList.innerHTML += "<button onclick='selectUser(" + user.id + ")'>" + user.name +"</button>";
        
    }
}

function createUser(){

    let username = document.getElementById("newUsername").value;

    if(username == "")
    {
        alert("Není zadáno jméno uživatele");
        return;
    }

    for(let user of users){
        if(user.name == username){
            alert("Toto jméno již existuje!");
            return;
        }
    }
    let newUser = {
        id: nextUserId,
        name: username
    };

    users.push(newUser);
    nextUserId++;

    document.getElementById("newUsername").value = "";

    showUsers();

}

function selectUser(userID){
    currentUser = users[userID];
    //console.log("Prih uzivatel: " + currentUser.name);

    document.getElementById("currentUserName").innerHTML = currentUser.name;

    showTransactions();
    showPage("page_Transactions");
}

function showPage(pageId) {
    
    document.getElementById("page_UserSelection").style.display = "none";
    
    document.getElementById("page_Transactions").style.display = "none";
    
    document.getElementById(pageId).style.display = "block";
}

function addTransaction(event) {
    event.preventDefault();  // Aby se neobnovila stránka
    
    
    let datum = document.getElementById("trans_Date").value;
    let typ = document.getElementById("trans_Type").value;
    let popis = document.getElementById("trans_Popis").value;
    let castka = document.getElementById("trans_Amount").value;
    let mena = document.getElementById("trans_Currency").value;
    
    let newTransaction = {
        id: nextTransactionId,
        userId: currentUser.id,
        date: datum,
        type: typ,
        desc: popis,
        amount: castka,
        currency: mena
    };

    transactions.push(newTransaction);
    nextTransactionId++;
 
    
    document.getElementById("trans_Date").value = "";
    
    document.getElementById("trans_Popis").value = "";
    document.getElementById("trans_Amount").value = "";

    showTransactions();
}

function showTransactions() {
    let tab_radky = document.getElementById("tabulka_transakce_radky");
    tab_radky.innerHTML = "";  //Smaže tabulku
    
    
    for (let trans of transactions) {
        
        if (trans.userId == currentUser.id) { //pouze přihl uzivatel
            tab_radky.innerHTML += "<tr>"+
            "<td>" + trans.date + "</td>"+
            "<td>" + trans.type + "</td>"+
            "<td>" + trans.desc + "</td>"+
            "<td>" + trans.amount +  " " + trans.currency + "</td>"+
            "</tr>"
        }
    }
}
function Odhlaseni(){
    currentUser = null;
    showPage("page_UserSelection");

}


showUsers();

document.getElementById("btn_createUser").addEventListener("click",createUser);
document.getElementById("formular").addEventListener("submit", addTransaction);
document.getElementById("btn_logout").addEventListener("click",Odhlaseni);