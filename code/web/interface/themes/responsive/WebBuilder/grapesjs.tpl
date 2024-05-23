<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="ISO-8859-1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Grapes JS Page Editor</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/css/grapes.min.css" integrity="sha512-F+EUNfBQvAXDvJcKgbm5DgtsOcy+5uhbGuH8VtK0ru/N6S3VYM9OHkn9ACgDlkwoxesxgeaX/6BdrQItwbBQNQ==" crossorigin="anonymous" referrerpolicy="no-referrer">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/grapes.min.js" integrity="sha512-TavCuu5P1hn5roGNJSursS0xC7ex1qhRcbAG90OJYf5QEc4C/gQfFH/0MKSzkAFil/UBCTJCe/zmW5Ei091zvA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/css/grapes.min.css" integrity="sha512-F+EUNfBQvAXDvJcKgbm5DgtsOcy+5uhbGuH8VtK0ru/N6S3VYM9OHkn9ACgDlkwoxesxgeaX/6BdrQItwbBQNQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script src="https://cdn.jsdelivr.net/npm/grapesjs-blocks-basic@1.0.2/dist/index.min.js"></script>
  <script src="https://unpkg.com/grapesjs-script-editor"></script>
  <script src="/code/web/interface/themes/responsive/js/aspen/swiper.js"></script>

</head>
<body>
  <div id="gjs"></div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const templateId = urlParams.get('templateId'); 
    console.log('tempId: ',templateId);
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
        plugins: ['gjs-blocks-basic', 'grapesjs-script-editor', 'swiper'],
        pluginsOpts: {
          'gjs-blocks-basic': {},
          'grapesjs-script-editor': {},
          'swiper': {},
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
          let html = editor.getHtml();
          let css = editor.getCss();
          let gjsId = projectData.pages.id;
          let grapesPageData = {
            grapesPageId: grapesPageId,
            templateId: templateId . gjsId,
            projectData: projectData,
            html: html,
            css: css,
          };
          console.log(grapesPageData.templateId);
          localStorage.setItem('grapesPageData', JSON.stringify(grapesPageData));

          $.ajax({
            url: '/services/WebBuilder/Save.php',
            type: "POST",
            contentType: 'application/json',
            data: JSON.stringify({ projectData: projectData }),
            success: function (response) {
              console.log('Saved');
            },
            error: function (xhr, status, error) {
              console.error('Error saving template:', error);
            }
          });
        }
      });

    editor.on('load', () => {
      const urlParams = new URLSearchParams(window.location.search);
      const templateId = urlParams.get('templateId'); 
      const grapesPageId = urlParams.get('id');
      const pageDataString = localStorage.getItem('pageData');
      const pageData = JSON.parse(pageDataString);

      const grapesPageDataString = localStorage.getItem('grapesPageData');
      const grapesPageData = JSON.parse(grapesPageDataString);
      console.log('DATA: ', grapesPageData);

      if (pageData.templateId === templateId) {
                console.log('match');
                editor.setComponents(pageData.html)
                editor.setStyle(pageData.css)
      } else if (grapesPageData.templateId === templateId) {
        console.log('grapes pages match');
        editor.setComponents(grapesPageData.html);
        editor.setStyles(grapesPageData.css)
      }
    })
    
  </script>
</body>
</html>
