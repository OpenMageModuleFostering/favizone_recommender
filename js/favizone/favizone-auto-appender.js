/**
 * 2016 Favizone Solutions Ltd
 *
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
function FavizoneAppender(element_gender, element_identifier){

    this.element_gender = element_gender;
    this.element_identifier = element_identifier;
    /*
    * Adding data for custom areas
    */
    this.appendFavizoneElement = function(){

        var section = document.getElementById(this.element_identifier);
        if(section){
            var element = document.createElement("div");

            element.setAttribute("id","favizone_"+this.element_gender+"_top_element");
            section.insertAdjacentHTML('afterbegin', '<div class="clearfix"></div>'+element.outerHTML);

            element.setAttribute("id","favizone_"+this.element_gender+"_bottom_element");
            section.insertAdjacentHTML('beforeend', '<div class="clearfix"></div>'+element.outerHTML);
        }
    }
}