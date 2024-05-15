<?xml version="1.0" encoding="iso-8859-1"?>
{* <!DOCTYPE html> *}
    
<head>
  <title>Create Template</title>
  <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/css/grapes.min.css" integrity="sha512-F+EUNfBQvAXDvJcKgbm5DgtsOcy+5uhbGuH8VtK0ru/N6S3VYM9OHkn9ACgDlkwoxesxgeaX/6BdrQItwbBQNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    {* <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/grapesjs-preset-webpage-ca@0.1.25/dist/grapesjs-preset-webpage.min.css"/> *}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/grapes.min.js" integrity="sha512-TavCuu5P1hn5roGNJSursS0xC7ex1qhRcbAG90OJYf5QEc4C/gQfFH/0MKSzkAFil/UBCTJCe/zmW5Ei091zvA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {* <script src="https://cdn.jsdelivr.net/npm/grapesjs-preset-webpage-ca@0.1.25/dist/grapesjs-preset-webpage.min.js"></script> *}
    <script src="https://cdn.jsdelivr.net/npm/grapesjs-blocks-basic@1.0.2/dist/index.min.js"></script>
    <script src="https://unpkg.com/grapesjs-script-editor"></script>
</head>
<body>
<div>{$createTemplate->title}</div>
    <div id="gjs">
    </div>
    <div id="template-data" style="display: none;">
       
    </div>
    
    <script>
    const urlParams = new URLSearchParams(window.location.search);
    const templateId = urlParams.get('id');
    console.log(templateId);
        const editor = grapesjs.init({
            container: "#gjs",
            fromElement: true,
            showOffsets: 1,
            noticeOnUnload: 0,
            storageManager: { autoload: 0 },
            storageManager: {
                type: 'remote',
                stepsbeforeSave: 1,
                contentTypeJson: true,
                storeComponents: true,
                storeStyles: true,
                storeHtml: true,
                storeCss: true,
                headers: {
                    'Content-Type': 'application/json',
                },
                // id: '',
                // urlStore: '/services/WebBuilder/Save.php',
                // urlLoad: '/services/WebBuilder/Load{}',
            },
            plugins: [
                'gjs-blocks-basic',
                'grapesjs-script-editor',
            ],
            pluginsOpts: {
             'gjs-blocks-basic': {

             },
             'grapesjs-script-editor': {

             },

            },
        })
     
        //add a save button - save as template
        editor.Panels.addButton('options', 
        [{
            id: 'save-as-template',
            className: 'fas fa-save',
            command: 'save-as-template',
            attributes: {
                title: 'Save as Template'
            }
        }]);

        editor.Commands.add('save-as-template', {
            run: function(editor, sender) {
                console.log(templateId);
                sender && sender.set('active', 0);
                let projectData = editor.getProjectData();
                console.log(projectData);
                let html = editor.getHtml();
                console.log(html);
                let css = editor.getCss();
   
                $.ajax({
                    url: '/services/WebBuilder/SaveTemplate.php',
                    type: "post",
                    data: JSON.stringify({
                        "templateId" : templateId,
                        "projectData": projectData,
                        "html": html,
                        "css": css,
                    }),
                    contentType: "application/json",
                    success: function (response) {
                        console.log('Saved as Template');
                    },
                    error: function (xhr, status, error) {
                        console.error('Error saving template: ', error);
                    }
                });
            }
        });
    </script>
</body>
</html>