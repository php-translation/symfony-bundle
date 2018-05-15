/**
 * Make an ajax call to edit element
 * @param el
 */
function editTranslation(el) {
    var xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == XMLHttpRequest.DONE ) {

            var resultDiv = el.parentElement.getElementsByClassName("ajax-result")[0];
            if (xmlhttp.status == 200) {
                resultDiv.className += ' success';
                resultDiv.innerHTML = xmlhttp.responseText;
            }
            else if (xmlhttp.status == 400) {
                resultDiv.className += ' error';
                resultDiv.innerHTML = xmlhttp.responseText;
            }
            else {
                resultDiv.className += ' error';
                resultDiv.innerHTML = "Unknown error";
            }

            setTimeout(function() {removeResultElement(resultDiv);}, 6000);
        }
    };

    xmlhttp.open("POST", editUrl, true);
    xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xmlhttp.send(JSON.stringify({message: el.value, key: el.getAttribute("data-key")}));
}

/**
 * Create a new translation
 * @param el
 * @param url
 * @returns {boolean}
 */
function createTranslation(el, url) {
    var xmlhttp = new XMLHttpRequest();
    var messageInput = document.getElementById('create-message');
    var keyInput = document.getElementById('create-key');

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
            var errorDiv = el.getElementsByClassName("ajax-result")[0];

            if (xmlhttp.status == 200) {
                messageInput.value = "";
                keyInput.value = "";

                var resultDiv = document.getElementById("new-translations");
                resultDiv.innerHTML = xmlhttp.responseText + resultDiv.innerHTML;
            }
            else if (xmlhttp.status == 400) {
                errorDiv.className += ' error';
                errorDiv.innerHTML = xmlhttp.responseText;
            }
            else {
                errorDiv.className += ' error';
                errorDiv.innerHTML = "Unknown error";
            }

            setTimeout(function() {removeResultElement(errorDiv);}, 6000);
        }
    };

    xmlhttp.open("POST", url, true);
    xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xmlhttp.send(JSON.stringify({message: messageInput.value, key: keyInput.value}));

    return false;
}


/**
 * Delete a translation.
 * @param el
 */
function deleteTranslation(el) {
    var xmlhttp = new XMLHttpRequest();
    var messageKey = el.getAttribute("data-key");

    xmlhttp.onreadystatechange = function() {
        if (xmlhttp.readyState == XMLHttpRequest.DONE ) {
            var row = document.getElementById(messageKey);
            var errorDiv = row.getElementsByClassName("ajax-result")[0];

            if (xmlhttp.status == 200) {
                row.parentNode.removeChild(row);
            }
            else if (xmlhttp.status == 400) {
                errorDiv.className += ' error';
                errorDiv.innerHTML = xmlhttp.responseText;
            }
            else {
                errorDiv.className += ' error';
                errorDiv.innerHTML = "Unknown error";
            }

            setTimeout(function() {removeResultElement(errorDiv);}, 6000);
        }
    };

    xmlhttp.open("DELETE", editUrl, true);
    xmlhttp.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
    xmlhttp.send(JSON.stringify({key: messageKey}));
}

/**
 * Remove the result element
 *
 * @param el
 */
function removeResultElement(el) {
    el.innerHTML = '';
    el.className = "ajax-result";
}

/**
 * Toggle visibility of an element
 * @param id
 */
function toggleElement(id) {
    var el = document.getElementById(id);
    if (el.offsetParent === null) {
        el.classList.add("show");
    } else {
        el.classList.remove("show");
    }
}

