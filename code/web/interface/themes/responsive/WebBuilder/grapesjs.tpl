<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="ISO-8859-1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grapes JS Page Editor</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/grapesjs@0.21.10/dist/css/grapes.min.css">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/grapes.min.js" integrity="sha512-TavCuu5P1hn5roGNJSursS0xC7ex1qhRcbAG90OJYf5QEc4C/gQfFH/0MKSzkAFil/UBCTJCe/zmW5Ei091zvA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/css/grapes.min.css" integrity="sha512-F+EUNfBQvAXDvJcKgbm5DgtsOcy+5uhbGuH8VtK0ru/N6S3VYM9OHkn9ACgDlkwoxesxgeaX/6BdrQItwbBQNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdn.jsdelivr.net/npm/grapesjs-blocks-basic@1.0.2/dist/index.min.js"></script>
  <script src="https://unpkg.com/grapesjs-script-editor"></script>

</head>
<body>
  <div id="gjs"></div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const templateId = urlParams.get('templateId'); 
    const grapesPageId = urlParams.get('id');

      const editor = grapesjs.init({
        container: "#gjs",
        fromElement: true,
        showOffsets: 1,
        noticeOnUnload: 0,
        storageManager: {
          type: 'remote',
          stepsBeforeSave: 1,
          contentTypeJson: true,
          storeComponents: true,
          storeStyles: true,
          storeHtml: true,
          storeCss: true,
          headers: { 'Content-Type': 'application/json' },
        },
        plugins: ['gjs-blocks-basic', 'grapesjs-script-editor'],
        pluginsOpts: {
          'gjs-blocks-basic': {},
          'grapesjs-script-editor': {},
        },
      });

      // Add a save button - save as page
      editor.Panels.addButton('options', [{
        id: 'save-as-page',
        className: 'fas fa-save',
        command: 'save-as-page',
        attributes: { title: 'Save as Page' }
      }]);

      editor.Commands.add('save-as-page', {
        run: function (editor, sender) {
                sender && sender.set('active', 0);
                let projectData = editor.getProjectData();
                let grapesGenId = projectData.pages[0].id;
                let html = editor.getHtml();
                let css = editor.getCss();
                let pageData = {
                    templateId: templateId,
                    grapesPageId: grapesPageId,
                    projectData: projectData,
                    html: html,
                    css: css,
                };  

                // localStorage.setItem('pageData', JSON.stringify(pageData));
                // console.log(projectData);

                $.ajax({
                    url: '/services/WebBuilder/SavePage.php',
                    action: 'saveAsPage',
                    type: "POST",
                    dataType: "json",
                    data: JSON.stringify({
                        "templateId": templateId,
                        "grapesPageId": grapesPageId,
                        "grapesGenId": grapesGenId,
                        "projectData": projectData,
                        "html": html,
                        "css": css,
                    }),
                    contentType: "application/json",
                    success: function (response) {
                        console.log('Saved as Grapes Page');
                    },
                    error: function (xhr, status, error) {
                        console.error('Error saving page: ', error);
                    }
                });
            }
      })

    editor.on('load', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const templateId = urlParams.get('templateId'); 
      const grapesPageId = urlParams.get('id');
      console.log('LOADING');
  
      $.get('/services/WebBuilder/LoadPage.php?id=' + grapesPageId, function(data) {
        console.log('GET');
        if (data.success) {
          console.log('DATA')
          editor.loadPojectData(data.projectData);
        } else {
          console.log('ERROR');
          console.error("Error loading page:", data.message);
        }
      });
    })
    
  </script>
</body>
</html>
