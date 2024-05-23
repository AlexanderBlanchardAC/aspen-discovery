<?xml version="1.0" encoding="iso-8859-1"?>
{* <!DOCTYPE html> *}
    
<head>
  <title>Create Template</title>
  <meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1'/>
  <link rel="stylesheet" href="/code/web/interface/themes/responsive/css/home.css" />
  <link rel="stylesheet" href="/code/web/interface/themes/responsive/css/home.less" />
  <link rel="stylesheet" href="/code/web/interface/themes/responsive/css/main.less" />
  <link rel="stylesheet" href="/code/web/interface/themes/responsive/css/main.css" />

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/css/grapes.min.css" integrity="sha512-F+EUNfBQvAXDvJcKgbm5DgtsOcy+5uhbGuH8VtK0ru/N6S3VYM9OHkn9ACgDlkwoxesxgeaX/6BdrQItwbBQNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />


    {* <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/grapesjs-preset-webpage-ca@0.1.25/dist/grapesjs-preset-webpage.min.css"/> *}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/grapesjs/0.21.10/grapes.min.js" integrity="sha512-TavCuu5P1hn5roGNJSursS0xC7ex1qhRcbAG90OJYf5QEc4C/gQfFH/0MKSzkAFil/UBCTJCe/zmW5Ei091zvA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {* <script src="https://cdn.jsdelivr.net/npm/grapesjs-preset-webpage-ca@0.1.25/dist/grapesjs-preset-webpage.min.js"></script> *}
    <script src="https://cdn.jsdelivr.net/npm/grapesjs-blocks-basic@1.0.2/dist/index.min.js"></script>
    <script src="https://unpkg.com/grapesjs-script-editor"></script>
    <script src="https://unpkg.com/grapesjs-plugin-forms"></script>
    <script src="https://unpkg.com/grapesjs-preset-webpage@1.0.2"></script>
    <script src="https://unpkg.com/grapesjs-tabs@1.0.6"></script>
    <script src="https://unpkg.com/grapesjs-custom-code@1.0.1"></script>
    <script src="https://unpkg.com/grapesjs-parser-postcss@1.0.1"></script>
    <script src="https://unpkg.com/grapesjs-tooltip@0.1.7"></script>
    <script src="https://unpkg.com/grapesjs-tui-image-editor@0.1.3"></script>
    <script src="https://unpkg.com/grapesjs-typed@1.0.5"></script>
    <script src="https://unpkg.com/grapesjs-style-bg@2.0.1"></script>
</head>
<body>
    <div id="gjs">
    </div>
    <div id="template-data" style="display: none;">
       
    </div>
    
    <script>
    const urlParams = new URLSearchParams(window.location.search);
    const templateId = urlParams.get('id');
   

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
                'grapesjs-plugin-forms',
                'grapesjs-preset-webpage',
                'grapesjs-tabs',
                'grapesjs-custom-code',
                'grapesjs-parser-postcss',
                'grapesjs-tooltip',
                'grapesjs-tui-image-editor',
                'grapesjs-style-bg',
                'grapesjs-typed',
            ],
            pluginsOpts: {
             'gjs-blocks-basic': {

             },
             'grapesjs-script-editor': {

             },
             'grapesjs-plugin-forms': {

             },
             'grapesjs-preset-webpage': {

             },
             'grapesjs-tabs': {

             },
             'grapesjs-custom-code': {

             },
             'grapesjs-parser-postcss': {

             },
             'grapesjs-tooltip': {

             },
             'grapesjs-tui-image-editor':{

             },
             'grapesjs-style-bg': {

             },
             'grapesjs-typed': {

             }
            },

        })

        let newComps = editor.DomComponents;
    $.get('/services/WebBuilder/Templates.php?method=getTemplateById&id={$templateId}', 
    function (data) {
        const components = JSON.parse(data);
        console.log(data);

        editor.DomComponents.setComponents(components);
        window.editor.setComponents(editor.DomComponents);
    });


     
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

     

//         const createImageSlider = function() {
//             function createImageCard(){
//                 const slider = document.querySelector('.slider');
//                 const sliderWrapper = document.querySelector('.slider-wrapper');
//                 const slides = document.querySelectorAll('.slide');
//                 const imageInupt = document.getElementById('imageInput');

//                 let currentIndex = 0;

//                 imageInput.addEventListener('change', function(event) {
//                     const files = event.target.files;
//                     if (files && files.length > 0) {
//                         imageCount += files.length;
//                         // if (imageCount >= 3) {
//                         //     imageInput.style.display = 'none';
//                         // }
//                         for (let i = 0; i < slides.length && i < files.length; i++) {
//                             const reader = new FileReader();
//                             reader.onload = function(e) {
//                                 slides[i].querySelector('img').src = e.target.result;
//                             };
//                             reader.readAsDataURL(files[i]);
//                         }
//                     }
//                 });

//                 function goToSlide() {
//                     const slideWidth = slides[0].offsetWidth; // Get the width of a single slide
//                     const newPosition = -currentIndex * slideWidth; // Calculate the new position based on the currentIndex
//                     sliderWrapper.style.transform = 'translateX(' + newPosition + 'px)'; // Set the new transform style to move the slider

//                 }




//                 function nextSlide() {
//                     currentIndex = (currentIndex + 1) % slides.length;
//                     goToSlide(currentIndex);
//                 }                  

//                 function prevSlide() {
//                     currentIndex = (currentIndex - 1 + slides.length) % slides.length;
//                     goToSlide(currentIndex);
//                 }

//                 setInterval(nextSlide, 3000);
//         }
            
//             createImageCard();
//         };

//         editor.on('component:mount', function(component) {
//     if (component.get('type') === 'image-slider') {
//         createImageSlider();
//     }
// });

        // editor.Components.addType('image-slider', {
        //     model: {
        //         defaults: {
        //             script: createImageSlider,
        //             content: `<div class="slider" style="width:100%"; overflow: hidden;"><div class="slider-wrapper" style="display:flex; transition: transform 0.5s ease;"><div class="slide" style="flex: 0 0 100%; max-width: 100%;"><img alt="placeholder1"/></div><div class="slide" style="flex: 0 0 100%; max-width: 100%;"><img alt="placeholdertwo" /></div><div class="slide" style="flex: 0 0 100%; max-width: 100%;"><img alt="placeholderthree"/></div></div><input type="file" id="imageInput" accept="image/*" multiple>`,
        //             style: {
        //                 'border': '1px solid #ccc',
        //                 'border-radius': '5px',
        //                 'overflow': 'hidden',
        //             }
        //         }
        //     }
        // })

        // editor.Blocks.add('test-block', {
        //     label: 'Test Block',
        //     category: 'Aspen',
        //     attributes: { class: 'fa fa-text'},
        //     content: {
        //          type: 'image-slider'
        //     },
        // })

        editor.Commands.add('save-as-template', {
            run: function(editor, sender) {
                console.log('id: ',templateId);
                sender && sender.set('active', 0);
                let projectData = editor.getProjectData();
                let html = editor.getHtml();
                console.log(html);
                let css = editor.getCss();
                let pageData = {
                    templateId: templateId,
                    projectData: projectData,
                    html: html,
                    css: css,
                };
                localStorage.setItem('pageData', JSON.stringify(pageData));
                console.log(projectData);
               
   
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

        


        editor.on('load', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const templateId = urlParams.get('id');
      
            const pageDataString = localStorage.getItem('pageData');
            const pageData = JSON.parse(pageDataString);
            console.log('from local: ', pageData.templateId);
            if (pageData.templateId === templateId) {
                console.log('match');
                editor.setComponents(pageData.html)
                editor.setStyle(pageData.css)
            }
    
    });

    </script>
</body>
</html>