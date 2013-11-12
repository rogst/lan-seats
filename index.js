var lastSeat = null;
function select_seat(seat) {
    if (lastSeat != null) {
        lastSeat.style.backgroundColor = "";
    }
    seat.style.backgroundColor = "#00FFFF";
    lastSeat = seat;
    var infospan = document.getElementById('selected_seat_info');
    infospan.innerHTML = 'Vald plats:<br>Plats: ' + seat.cellIndex + '<br>Rad: ' + seat.parentNode.rowIndex;
    var seatnumber = document.getElementById('seat_number');
    var seatrow = document.getElementById('seat_row');
    seatnumber.value = seat.cellIndex;
    seatrow.value = seat.parentNode.rowIndex;
    var button = document.getElementById('book_seat_btn');
    button.style.display = "inline";
}

function view_seat(seat)  {
    if (lastSeat != null) {
        lastSeat.style.backgroundColor = "";
    }
    seat.style.backgroundColor = "#00FFFF";
    lastSeat = seat;
    var button = document.getElementById('book_seat_btn');
    button.style.display = "none";
    var url = "index.php?get=seat&x=" + seat.cellIndex + "&y=" + seat.parentNode.rowIndex;
    $.get(url,function(data,status){
        var infospan = document.getElementById('selected_seat_info');
        infospan.innerHTML = 'Platsen är bokad av:<br>' + data + '<br>';
    });
}

function book_selected_seat() {
    var form = document.getElementById('selected_seat_form');
    form.submit();
}

