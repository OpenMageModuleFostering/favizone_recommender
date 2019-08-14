/**
 * 2016 Favizone Solutions Ltd
 *
 * @author    Favizone Solutions Ltd <contact@favizone.com>
 * @copyright 2015-2016 Favizone Solutions Ltd
 */
sendData= function (url, indicator, store){

    try {

        new Ajax.Request(url, {
            method: "post",
            parameters: setPostData(indicator, store),
            onLoading: function() {setVisibleElements(indicator , "begin");},
            onSuccess: function(response) {
               var  data = JSON.parse(response.responseText);
                if(data.success == false){
                    document.getElementById("submit-step-2").classList.add("hide");
                    document.getElementById("submit-step-final-error").classList.remove("hide");
                }else
                    setVisibleElements(indicator , "end");
            }
        });
        return false;
    }
    catch(err) {

        console.log(err);
        return false;
    }
}

setPostData = function(indicator, store){

    switch (indicator){
        case "submit-register":
            return "&&auth_key=" +  document.getElementById("submit-form").elements["auth_key"].value+"&&store="+store;

        case "submit-ab-test":
            var form_value = document.querySelector('input[name = "ab_test"]:checked').value;
            return "&&ab_test=" + form_value+"&&store="+store;

        case "category-sync":
            return   "indicator="+indicator+"&&store="+store;

    }
}
setVisibleElements = function(indicator , callStep){

    if(callStep == "begin"){
        switch (indicator){
            case "submit-register":

                document.getElementById("submit-step-1").classList.add("hide");
                document.getElementById("submit-step-2").classList.remove("hide");
                break;
            case "category-sync":
                document.getElementById("submit-step-2").classList.remove("hide");
                break;
            case "submit-ab-test":
                document.getElementById("favizone_success_update").classList.add("hide");
                break;


        }
    }
    else{
        //end
        switch (indicator){
            case "submit-register":

                document.getElementById("submit-step-2").classList.add("hide");
                document.getElementById("submit-step-final").classList.remove("hide");
                break;
            case "category-sync":
                document.getElementById("submit-step-2").classList.add("hide");
                document.getElementById("submit-step-final").classList.remove("hide");
                break;
            case "submit-ab-test":
                document.getElementById("favizone_success_update").classList.remove("hide");
                break;
        }
    }
}

sendResetRequest = function(url){
    try {

        new Ajax.Request(url, {
            method: "post",
            parameters: "test=1",
            onSuccess: function(response) {
               window.location.reload();
            }
        });
        return false;
    }
    catch(err) {

        console.log(err);
        return false;
    }
}

