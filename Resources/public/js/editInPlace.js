/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
(function () {
    if (typeof customElements.define !== "undefined") {
        customElements.define("x-trans", HTMLElement);

        return;
    }

    document.registerElement("x-trans", {
        prototype: Object.create(HTMLElement.prototype)
    });
})();

/**
 * TranslationBundleEditInPlace boot the ContentTools editor and handle saves.
 *
 * @author Damien Alexandre <dalexandre@jolicode.com>
 * @param saveUrl The AJAX API Endpoint
 */
var TranslationBundleEditInPlace = function(saveUrl) {
    var editor, httpRequest;

    /* Tools for HTML blocks - no image or video support */
    var HTML_TOOLS = [
        [
            'bold',
            'italic',
            'link',
            'align-left',
            'align-center',
            'align-right'
        ], [
            'heading',
            'subheading',
            'paragraph',
            'unordered-list',
            'ordered-list',
            'table',
            'indent',
            'unindent',
            'line-break'
        ], [
            'undo',
            'redo',
            'remove'
        ]
    ];

    /* Tools for basic string, no HTML allowed */
    var STRING_TOOLS = [
        [
            'undo',
            'redo'
        ]
    ];

    // Set the default to SIMPLE
    ContentTools.DEFAULT_TOOLS = STRING_TOOLS;
    editor = ContentTools.EditorApp.get();
    editor.init('x-trans', 'data-key', function(domRegion) {
        // true = fixture (string of text)
        // false = classic HTML block
        return domRegion.dataset.key.split('.').pop() !== 'html';
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
                editor.busy(false);

                if (httpRequest.status === 200) {
                    new ContentTools.FlashUI('ok');
                } else if (httpRequest.status === 400) {
                    alert(httpRequest.responseText);
                    new ContentTools.FlashUI('no');
                } else {
                    alert('Error: we could not save the translations! Please retry.');
                    new ContentTools.FlashUI('no');
                }
            }
        };

        httpRequest.open('POST', saveUrl, true);
        httpRequest.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
        httpRequest.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        httpRequest.send(JSON.stringify(regions));
    }

    // On focus, change the tools
    ContentEdit.Root.get().bind('focus', function(element) {
        // @todo Display the translation key & placeholder somewhere in the UI?
        var tools;

        if (element.isFixed()) {
            tools = STRING_TOOLS;
        } else {
            tools = HTML_TOOLS;
        }

        if (editor.toolbox().tools() !== tools) {
            return editor.toolbox().tools(tools);
        }
    });

    // Any click on links / button... should prevent default if editing is on
    document.addEventListener('click', function(event) {
        event = event || window.event;
        var target = event.target || event.srcElement;

        while (target) {
            // Disable the default behavior on some active elements
            if (target instanceof HTMLAnchorElement || target instanceof HTMLButtonElement || target instanceof HTMLLabelElement) {
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
