/**
 * 2016 Favizone Solutions Ltd
 *
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
function Tracker(session, key, events, apiUrl){

    this.events = events;
    this.session = session;
    this.key = key;
    this.apiUrl = apiUrl;

    this.sendAction = function(){
        /** checks if session_identifier and version is well defined **/
        var favizone_searcher = new FavizoneHelper();
        var favizone_connection_identifier = favizone_searcher.getCookie("favizone_connection_identifier_"+store_id);
        var version = favizone_searcher.getCookie("favizone_AB_"+store_id);
        var current_event;
        if(favizone_connection_identifier){

            this.session = favizone_connection_identifier;
            for(var i = 0;i<this.events.length;i++){

                current_event = this.events[i];
                current_event = current_event.replace('anonymous', favizone_connection_identifier);
                current_event =  version+current_event.substring(1);    
                this.events[i] = current_event;

            }

            this.process_sending();
       }
    }


    this.process_sending = function(){

        /**  Preparing data **/
        var sending_data = {key:this.key,events:this.events, session:this.session};
        if (this.custom_event_key) {
            sending_data.custom_event_key = this.custom_event_key;
        }
        if (this.custom_event_value) {
            sending_data.custom_event_value = this.custom_event_value;
        }
        if (this.search_engine_value) {
            sending_data.search_engine_value = this.search_engine_value;
        }
        if (this.search_campaign_value) {
            sending_data.search_campaign_value = this.search_campaign_value;
        }
        if (this.user_data) {
            this.user_data.session = this.session
            sending_data.user_data = this.user_data;
        }

        if (this.favizone_facebook_profile) {
            sending_data.favizone_facebook_profile = this.favizone_facebook_profile;
        }

        if(typeof(this.product_data) != "undefined"){
            sending_data['product'] = this.product_data;
        }
        /** End Preparing data **/

        var request = new XMLHttpRequest();
        request.open('POST', this.apiUrl , true);
        request.onreadystatechange = function() {
            if (request.readyState == 4) {
                if ( request.status == 200){
                    //success
                }
                else{
                    //error
                }
            }
        };

        var params = JSON.stringify(sending_data);
        //request.setRequestHeader("Content-type", "application/json");
        //request.setRequestHeader("Content-length", params.length);
       // request.setRequestHeader("Connection", "close");
        request.timeout = 3000;
        request.ontimeout = function () {
            //timeout
        }
        /** Sending data **/
        request.send(params);
    }
}

