var lastSeat = null;
function select_seat(seat) {
    if (lastSeat != null) {
        lastSeat.style.backgroundColor = "";
    }
    seat.style.backgroundColor = "#00FFFF";
    lastSeat = seat;
    var seatnumber = document.getElementById('seat_number');
    var seatrow = document.getElementById('seat_row');
    seatnumber.value = seat.cellIndex;
    seatrow.value = seat.parentNode.rowIndex;
    var button = document.getElementById('book_seat_btn');
    button.style.display = "inline";
    
    var url = "index.php?action=getseat&x=" + seat.cellIndex + "&y=" + seat.parentNode.rowIndex;
    $.getJSON(url,function(data,status){
        var infospan = document.getElementById('selected_seat_info');
        infospan.innerHTML = 'Vald plats: ' + data[1] + data[0];
    });
}

function view_seat(seat)  {
    if (lastSeat != null) {
        lastSeat.style.backgroundColor = "";
    }
    seat.style.backgroundColor = "#00FFFF";
    lastSeat = seat;
    var button = document.getElementById('book_seat_btn');
    button.style.display = "none";
    var url = "index.php?action=getholdername&x=" + seat.cellIndex + "&y=" + seat.parentNode.rowIndex;
    $.getJSON(url,function(data,status){
        var infospan = document.getElementById('selected_seat_info');
        infospan.innerHTML = 'Platsen är bokad av:<br>' + data + '<br>';
    });
}

function book_selected_seat() {
    var seatnumber = document.getElementById('seat_number');
    var seatrow = document.getElementById('seat_row');
    var url = "index.php?action=bookseat&x=" + seatnumber.value + "&y=" + seatrow.value;
    $.getJSON(url,function(data,status){
        var infospan = document.getElementById('selected_seat_info');
        if (data == 'success') {
            window.location = '/';
        } else {
            infospan.style.color = '#FF0000';
            infospan.style.fontWeight = 'bold';
            infospan.innerHTML = 'Platsen du valde har redan bokats, vänligen ladda om sidan och välj en annan plats';
        }
    });
    //var form = document.getElementById('selected_seat_form');
    //form.submit();
}

