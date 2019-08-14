/**
 * 2016 Favizone Solutions Ltd
 *
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
function FavizoneRenderer(url, post_data){

    this.url = url;
    this.post_data = post_data;
    if(this.post_data){
        var favizone_searcher = new FavizoneHelper();
        if(typeof (this.post_data.session)!="undefined" && this.post_data.session =='anonymous'){
            if(typeof (store_renderer_id) != "undefined")
                this.post_data.session = favizone_searcher.getCookie("favizone_connection_identifier_"+store_renderer_id);
        }
        if(typeof (this.post_data.event_params)!="undefined" && this.post_data.event_params.session =='anonymous' ){
            if(typeof (store_renderer_id) != "undefined")
                this.post_data.event_params.session = favizone_searcher.getCookie("favizone_connection_identifier_"+store_renderer_id);
        }
    }

    this.getRecs = function(){

        //to backup because of changing in context
        var request = new XMLHttpRequest();
        var scripts_to_eval = [] ;

        request.open('POST', this.url , true);
        request.onreadystatechange = function() {

            if (request.readyState == 4) {
                if ( request.status == 200){
                    var result = JSON.parse(request.responseText);
                    //render the content
                    for(var key in result){

                        if(document.getElementById(result[key].container)){
                            //appending data
                            document.getElementById(result[key].container).innerHTML += result[key].template;

                            //Eval scripts if exist
                            var favizone_scripts =  document.getElementById(result[key].container).getElementsByTagName('script')
                            for (var n = 0; n < favizone_scripts.length; n++)
                                scripts_to_eval.push(favizone_scripts[n].innerHTML);
                                //eval(favizone_scripts[n].innerHTML);

                            //Binding events
                            if(typeof(jQuery) != 'undefined'){
                                var $jq = jQuery.noConflict();
                                if( typeof ($jq(document).on)!="undefined"){

                                    $jq(document).on('click', "#"+key + " [data-context=favizone]", {"key":key} , function(event) {

                                        document.cookie = "favizone_id_recommendor="+event.data.key+ "; path=/";
                                        document.cookie = "favizone_id_product="+$jq(this).attr("favizone-ref")+ "; path=/";
                                        return true;
                                    });
                                }
                                else{
                                    //old version of jquery
                                    $jq(document).delegate("#"+key + " [data-context=favizone]", 'click', {"key":key}, function(event) {

                                        document.cookie = "favizone_id_recommendor="+event.data.key+ "; path=/";
                                        document.cookie = "favizone_id_product="+$jq(this).attr("favizone-ref")+ "; path=/";
                                        return true;
                                    });
                                }
                            }
                            else{

                                var  clik;
                                var elements = document.getElementById(key).querySelectorAll("[data-context=favizone]");
                                for(var i = 0; i<elements.length; i++){
                                    try{
                                        clik = document.createAttribute("onclick");
                                        clik.nodeValue = "setData(this, '"+key+"');";
                                        elements[i].setAttributeNode(clik);
                                    } catch(error){
                                        console.log(error);
                                    }
                                }
                            }
                        }
                    }
                    //evaluation of js scripts
                    for(var script in scripts_to_eval)
                        eval(scripts_to_eval[script]);
                }
            }
        };

        request.setRequestHeader("Content-type", "application/json");
        request.setRequestHeader("Accept", "*/*");
        request.setRequestHeader("Connection", "close");
        request.timeout = 4000;
        request.ontimeout = function () { console.log("Timed out!!!"); }
        var favizone_helper = new FavizoneHelper();
        if(favizone_helper.getCookie("favizone_preview"))
            this.post_data.favizone_preview = true;
        if(typeof (this.post_data.session)!="undefined" && this.post_data.session.length>0)
            request.send(JSON.stringify(this.post_data));
    }

}

function setData(param, key){
    
    document.cookie = "favizone_id_recommendor="+key+ "; path=/";
    document.cookie = "favizone_id_product="+param.getAttribute("favizone-ref")+ "; path=/";
    return true;
}