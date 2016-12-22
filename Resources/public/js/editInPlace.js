/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
document.registerElement('x-trans', { prototype: Object.create(HTMLElement.prototype) });

/**
 * TranslationBundleEditInPlace boot the ContentTools editor and handle saves
 *
 * @author dalexandre@jolicode.com
 * @param saveUrl The AJAX API Endpoint
 */
var TranslationBundleEditInPlace = function(saveUrl) {
    var editor, httpRequest;

    // @todo Maybe improve this to switch back to the default tools for HTML
    ContentTools.DEFAULT_TOOLS = [['undo', 'redo']];

    editor = ContentTools.EditorApp.get();
    editor.init('x-trans', 'data-key', function() {
        // @todo If there is "html" in the key, return false?
        return true;
    });

    // Treat x-trans tags as Text
    ContentEdit.TagNames.get().register(ContentEdit.Text, 'x-trans');

    // Save to backend
    editor.addEventListener('saved', function(ev) {
        if (Object.keys(ev.detail().regions).length === 0) {
            return;
        }

        doSave(ev.detail().regions);
    });

    function doSave(regions) {
        editor.busy(true);

        httpRequest = new XMLHttpRequest();
        if (!httpRequest) {
            alert('Giving up :( Cannot create an XMLHTTP instance');
            return false;
        }

        httpRequest.onreadystatechange = function() {
            if (httpRequest.readyState === XMLHttpRequest.DONE) {
                if (httpRequest.status === 200) {
                    editor.busy(false);
                    new ContentTools.FlashUI('ok');
                } else {
                    editor.busy(false);
                    alert('Error: we could not save the translations! Please retry.');
                    new ContentTools.FlashUI('no');
                }
            }
        };

        httpRequest.open('POST', saveUrl, true);
        httpRequest.send(JSON.stringify(regions));
    }

    // On focus, change the tools
    ContentEdit.Root.get().bind('focus', function(element) {
        // @todo Display the translation key & placeholder somewhere in the UI?
        // @todo Change the tools between element.isFixed() and not (editor.toolbox().tools(tools))
    });

    // Any click on links / button... should prevent default if editing is on
    document.addEventListener('click', function(event) {
        event = event || window.event;
        var target = event.target || event.srcElement;

        while (target) {
            if (target instanceof HTMLAnchorElement || target instanceof HTMLButtonElement) {
                if(ContentTools.EditorApp.get().isEditing()) {
                    // Link or button found, prevent default!
                    event.preventDefault();
                    event.stopPropagation();
                }
                break;
            }

            target = target.parentNode;
        }
    }, true);
};
