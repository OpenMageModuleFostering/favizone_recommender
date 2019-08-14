/**
 * 2016 Favizone Solutions Ltd
 *
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
document.getElementById("favizone_preview_close").onclick = function(){

    var url = window.location.href ;
    favizone_helper = new FavizoneHelper();
    if(url.indexOf("favizone_preview=true")>-1){

        url = url.replace("favizone_preview=true","favizone_preview=false");
    }else{

        
        url = favizone_helper.insertParam("favizone_preview","false");
    }

    favizone_helper.setCookie("favizone_preview", "");
    window.location.href = url;
};

var selected = null, // Object of the element to be moved
    x_pos = 0, y_pos = 0, // Stores x & y coordinates of the mouse pointer
    x_elem = 0, y_elem = 0; // Stores top, left values (edge) of the element

// Will be called when user starts dragging an element
function _drag_init(elem) {
    // Store the object of the element which needs to be moved
    selected = elem;
    x_elem = x_pos - selected.offsetLeft;
}

// Will be called when user dragging an element
function _move_elem(e) {
    x_pos = document.all ? window.event.clientX : e.pageX;
    if (selected !== null) {
        selected.style.left = (x_pos - x_elem) + 'px';
    }
}

// Destroy the object when we are done
function _destroy() {
    selected = null;
}

// Bind the functions...
document.getElementById('favizone_preview_section').onmousedown = function () {
    _drag_init(this);
    return false;
};

document.onmousemove = _move_elem;
document.onmouseup = _destroy;