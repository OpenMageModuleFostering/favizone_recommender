/**
 * 2016 Favizone Solutions Ltd
 *
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
document.getElementById("favizone_preview_close").onclick = function() {

  var url = window.location.href;
  if (url.indexOf("favizone_preview=true") > -1) {

    url = url.replace("favizone_preview=true", "favizone_preview=false");
  } else {

    favizone_helper = new FavizoneHelper();
    url = favizone_helper.insertParam("favizone_preview", "false");
  }

  window.location.href = url;
};

var selected = null, // Object of the element to be moved
  x_pos = 0,
  y_pos = 0, // Stores x & y coordinates of the mouse pointer
  x_elem = 0,
  y_elem = 0; // Stores top, left values (edge) of the element

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
document.getElementById('favizone_preview_section').onmousedown = function() {
  _drag_init(this);
  return false;
};

window.parent.document.getElementById('favizone_preview_setting').addEventListener('click', function(e) {
  toggleSlide(window.parent.document.getElementById('favizone_header'));
}, false);

/** Toggle slide effect**/
var toggleSlide = function(el) {
  var el_max_height = 0;
  if (el.getAttribute('data-max-height')) {
    // we've already used this before, so everything is setup
    if (el.style.maxHeight.replace('px', '').replace('%', '') === '0') {
      el.style.maxHeight = el.getAttribute('data-max-height');
    } else {
      el.style.maxHeight = '0';
    }
  } else {
    el_max_height = getHeight(el) + 'px';
    el.style['transition'] = 'max-height 0.5s ease-in-out';
    el.style.overflowY = 'scroll';
    el.style.maxHeight = '0';
    el.setAttribute('data-max-height', el_max_height);
    el.style.display = 'block';

    // we use setTimeout to modify maxHeight later than display (to we have the transition effect)
    setTimeout(function() {
      // el.style.maxHeight = el_max_height;
    }, 10);
  }
};
var getHeight = function(el) {
  var el_style = window.parent.getComputedStyle(el),
    el_display = el_style.display,
    el_position = el_style.position,
    el_visibility = el_style.visibility,
    el_max_height = el_style.maxHeight.replace('px', '').replace('%', ''),
    wanted_height = 0;

  // if its not hidden we just return normal height
  if (el_display !== 'none' && el_max_height !== '0') {
    return el.offsetHeight;
  }
  // the element is hidden so:
  // making the el block so we can meassure its height but still be hidden
  el.style.position = 'absolute';
  el.style.visibility = 'hidden';
  el.style.display = 'block';

  wanted_height = el.offsetHeight;

  // reverting to the original values
  el.style.display = el_display;
  el.style.position = el_position;
  el.style.visibility = el_visibility;
  return wanted_height;
};

document.onmousemove = _move_elem;
document.onmouseup = _destroy;