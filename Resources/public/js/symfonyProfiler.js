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

    fetch(translationSyncUrl, {
        method: 'POST',
        body: serializeQueryString({message_id: key}),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        }
    }).then(res => res.text()).then((text) => {
        el[0].innerHTML = text;

        if (text !== "") {
            clearState(key);
        }
    }).catch(() => {
        el[0].innerHTML = "<span style='color:red;'>Error - Syncing message " + key + "</span>";
    });
}

function syncAll() {
    var el = document.getElementById("top-result-area");
    el.innerHTML = getLoaderHTML();

    fetch(translationSyncAllUrl, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    }).then(res => res.text()).then(text => {
        el.innerHTML = text;
    }).catch(() => {
        el[0].innerHTML = "<span style='color:red;'>Error - Syncing all messages</span>";
    });
}

function getEditForm(key) {
    var el = document.getElementById(key).getElementsByClassName("translation");
    el[0].innerHTML = getLoaderHTML();

    fetch(translationEditUrl + "?" + serializeQueryString({message_id: key}), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
        },
    }).then(res => res.text()).then(text => {
        el[0].innerHTML = text;
    }).catch(() => {
        el[0].innerHTML = "<span style='color:red;'>Error - Getting edit form " + key + "</span>";
    });
}

function saveEditForm(key, translation) {
    var el = document.getElementById(key).getElementsByClassName("translation");
    el[0].innerHTML = getLoaderHTML();

    fetch(translationEditUrl, {
        method: 'POST',
        body: serializeQueryString({message_id: key, translation:translation}),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    }).then(res => res.text()).then(text => {
        el[0].innerHTML = text;

        if (text !== "") {
            clearState(key);
        }
    }).catch(() => {
        el[0].innerHTML = "<span style='color:red;'>Error - Saving edit form " + key + "</span>";
    })

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

    fetch(form.action, {
        method: 'POST',
        body: serializeQueryString({selected: selected}),
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/x-www-form-urlencoded',
        },
    }).then(res => res.text()).then(text => {
        el.classList.add('label');
        el.classList.add('status-success');
        el.innerHTML = text;
    }).catch(error => {
        el.classList.add('label');
        el.classList.add('status-error');
        el.innerHTML = error;
    })

    return false;
};
