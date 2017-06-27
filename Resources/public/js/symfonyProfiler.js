/**
 * Clear the state field and remove the checkbox
 * @param key
 */
function clearState(key) {
    var row = document.getElementById(key);

    // disable the checkbox
    var inputs = row.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        if (inputs[i].type == "checkbox" && inputs[i].name == "translationKey") {
            inputs[i].checked = false;
            inputs[i].disabled = true;
        }
    }
}

function syncMessage(key) {
    Sfjs.request(
        translationSyncUrl,
        function(xhr) {
            // Success
            var el = document.getElementById(key).getElementsByClassName("translation");
            el[0].innerHTML = xhr.responseText;

            if (xhr.responseText !== "") {
                clearState(key);
            }
        },
        function(xhr) {
            // Error
            console.log("Syncing message "+key + " - Error");
        },
        serializeQueryString({message_id: key}),
        { method: 'POST' }
    );
}

function syncAll() {
    Sfjs.request(
        translationSyncAllUrl,
        function(xhr) {
            // Success
            var el = document.getElementById("top-result-area");
            el.innerHTML = xhr.responseText;
        },
        function(xhr) {
            // Error
            console.log("Syncing message "+key + " - Error");
        },
        {},
        { method: 'POST' }
    );
}

function getEditForm(key) {

    Sfjs.request(
        translationEditUrl + "?" + serializeQueryString({message_id: key}),
        function(xhr) {
            // Success
            var el = document.getElementById(key).getElementsByClassName("translation");
            el[0].innerHTML = xhr.responseText;
        },
        function(xhr) {
            // Error
            console.log("Getting edit form "+key+" - Error");
        },
        { method: 'GET' }
    );
}

function saveEditForm(key, translation) {

    Sfjs.request(
        translationEditUrl,
        function(xhr) {
            // Success
            var el = document.getElementById(key).getElementsByClassName("translation");
            el[0].innerHTML = xhr.responseText;

            if (xhr.responseText !== "") {
                clearState(key);
            }
        },
        function(xhr) {
            // Error
            console.log("Saving edit form "+key +" - Error");
        },
        serializeQueryString({message_id: key, translation:translation}),
        { method: 'POST' }
    );

    return false;
}

function cancelEditForm(key, orgMessage) {
    var el = document.getElementById(key).getElementsByClassName("translation");
    el[0].innerHTML = orgMessage;
}

function toggleCheckAll(controller) {
    var checkboxes = document.querySelectorAll('.translation-key-checkbox');

    for (var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = controller.checked;
    }
}

var serializeQueryString = function(obj, prefix) {
    var str = [];
    for(var p in obj) {
        if (obj.hasOwnProperty(p)) {
            var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
            str.push(typeof v == "object" ? serializeQueryString(v, k) : encodeURIComponent(k) + "=" + encodeURIComponent(v));
        }
    }
    return str.join("&");
};

// We need to hack a bit Sfjs.request because it does not support POST requests
// May not work for ActiveXObject('Microsoft.XMLHTTP'); :(
(function(open) {
    XMLHttpRequest.prototype.open = function(method, url, async, user, pass) {
        open.call(this, method, url, async, user, pass);
        if (method.toLowerCase() === 'post') {
            this.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        }
    };
})(XMLHttpRequest.prototype.open);

var saveTranslations = function(form) {
    "use strict";
    if (typeof(form.translationKey) === 'undefined') {
        return false;
    }
    var inputs = form.translationKey;
    var selected = [];
    if (!inputs.value) {
        for (var val in inputs) {
            if (inputs.hasOwnProperty(val) && inputs[val].value) {
                if (inputs[val].checked) {
                    selected.push(inputs[val].value);
                }
            }
        }
    } else if (inputs.checked) {
        selected.push(inputs.value);
    }
    Sfjs.request(
        form.action,
        function(xhr) {
            // Success
            document.getElementById('translationResult').innerHTML = xhr.responseText;
        },
        function(xhr) {
            // Error
            document.getElementById('translationResult').innerHTML = xhr.responseText;
        },
        serializeQueryString({selected: selected}),
        { method: 'POST' }
    );
    return false;
};
