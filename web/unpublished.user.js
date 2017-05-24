// ==UserScript==
// @name            Unpublished-Geocaches
// @namespace       https://www.geocaching.com
// @author          Surfoo
// @description     Fetch unpublished geocaches
// @include         https://www.geocaching.com/geocache/*
// @include         https://www.geocaching.com/seek/cache_details.aspx*
// @version         1.1.3
// @grant           GM_xmlhttpRequest
// ==/UserScript==


var d = document.getElementById('Download');
var m = d.children;
var last = m.item(m.length - 1);

var html = '<br /><input type="button" name="SendToUnpublishedGeocaches" style="margin-top: 10px;" value="Send to unpublished geocaches" id="SendToUnpublishedGeocaches" />';
last.innerHTML = last.innerHTML + html;

var button = document.getElementById("SendToUnpublishedGeocaches");

button.addEventListener('click', function() {
    GM_xmlhttpRequest({
        method: "POST",
        url: "https://unpublished.vaguelibre.net/gm.php",
        data: "content=" + encodeURIComponent(document.documentElement.innerHTML),
        headers: {
            "Content-Type": "application/x-www-form-urlencoded"
        },
        onload: function(response) {
            var data = JSON.parse(response.responseText);
            if (!data.success) {
                alert(data.message);
                return false;
            }
            alert('Geocache added to your list!');
            return true;
        }
    });
}, true);