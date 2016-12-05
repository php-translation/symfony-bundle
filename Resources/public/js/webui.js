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
 * Remove the result element
 *
 * @param el
 */
function removeResultElement(el) {
    el.innerHTML = '';
    el.className = "ajax-result";
}
