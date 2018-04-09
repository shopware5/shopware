function myFunction() {
    var checkBox = document.getElementById("swagagb");
    var text = document.getElementById("swagagbbtn");

    if (checkBox.checked == true){
        text.style.display = "block";
    } else {
        text.style.display = "none";
    }
}
