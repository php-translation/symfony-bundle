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
    var el = document.getElementById(key).getElementsByClassName("translation");
    el[0].innerHTML = getLoaderHTML();

    Sfjs.request(
        translationSyncUrl,
        function(xhr) {
            // Success
            el[0].innerHTML = xhr.responseText;

            if (xhr.responseText !== "") {
                clearState(key);
            }
        },
        function(xhr) {
            // Error
            el[0].innerHTML = "<span style='color:red;'>Error - Syncing message " + key + "</span>";
        },
        serializeQueryString({message_id: key}),
        { method: 'POST' }
    );
}

function syncAll() {
    var el = document.getElementById("top-result-area");
    el.innerHTML = getLoaderHTML();

    Sfjs.request(
        translationSyncAllUrl,
        function(xhr) {
            // Success
            el.innerHTML = xhr.responseText;
        },
        function(xhr) {
            // Error
            el[0].innerHTML = "<span style='color:red;'>Error - Syncing all messages</span>";
        },
        {},
        { method: 'POST' }
    );
}

function getEditForm(key) {
    var el = document.getElementById(key).getElementsByClassName("translation");
    el[0].innerHTML = getLoaderHTML();

    Sfjs.request(
        translationEditUrl + "?" + serializeQueryString({message_id: key}),
        function(xhr) {
            // Success
            el[0].innerHTML = xhr.responseText;
        },
        function(xhr) {
            // Error
            el[0].innerHTML = "<span style='color:red;'>Error - Getting edit form " + key + "</span>";
        },
        { method: 'GET' }
    );
}

function saveEditForm(key, translation) {
    var el = document.getElementById(key).getElementsByClassName("translation");
    el[0].innerHTML = getLoaderHTML();

    Sfjs.request(
        translationEditUrl,
        function(xhr) {
            // Success
            el[0].innerHTML = xhr.responseText;

            if (xhr.responseText !== "") {
                clearState(key);
            }
        },
        function(xhr) {
            // Error
            el[0].innerHTML = "<span style='color:red;'>Error - Saving edit form " + key + "</span>";
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

function getLoaderHTML() {
    var loader = document.getElementById('svg-loader');

    return loader.outerHTML;
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

    var el = document.getElementById('translationResult');
    el.innerHTML = getLoaderHTML();
    el.classList.remove('label');
    el.classList.remove('status-error');
    el.classList.remove('status-success');

    Sfjs.request(
        form.action,
        function(xhr) {
            // Success
            el.classList.add('label');
            el.classList.add('status-success');
            el.innerHTML = xhr.responseText;
        },
        function(xhr) {
            // Error
            el.classList.add('label');
            el.classList.add('status-error');
            el.innerHTML = xhr.responseText;
        },
        serializeQueryString({selected: selected}),
        { method: 'POST' }
    );
    return false;
};
