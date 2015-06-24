// Silence is golden.
<!DOCTYPE HTML>
<html>
<head>
    <!-- support for mobile touch devices -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1, minimal-ui">
    <link href="bootstrap.min.css" rel="stylesheet">
    <link href="css/font-awesome.min.css" rel="stylesheet">
    <link href="cornerstone.min.css" rel="stylesheet">
</head>
<style>

    /* prevent 'bounce' in scrolling */
    html {
      height: 100%;
      width: 100%;
      overflow: hidden;
    }

    body {
      height: 100%;
      width: 100%;
      overflow: auto;
    }

    .hidden {
        display: none;
    }

    /* images are displayed in viewports */
    .viewport {
        width:100%;
        height:100%;
        top:0px;
        left:0px;
        position:absolute
    }

    .overlay {
        position: absolute;
        color: #e4ad00;
    }

    .imageViewer {
    }

    .myNav {
        margin: 0;
        border:0;
    }

    .viewportWrapper {

    }
    .renderTime {}
    .fps {}

    .csthumbnail {
        color: white;
        background-color:black;
        width:100px;
        height:100px;
        border: 0px;
        padding: 0px;
    }

    .viewer {
        position: absolute;
        width: 100%;
        left: 110px;
    }

    .thumbnailSelector {
        width:106px;
        float:left;
        margin-left:0px;
    }
    body, html {height:100%}

    #wrap {height:100%}

    .studyContainer {
        margin-top: 2px;
        margin-left:0px;
        margin-right:0px;
        padding: 0px;
    }
    .container {
    }

    .thumbnails {
        margin:0px;
        margin-bottom: 0px;
        overflow-y:scroll;
        overflow-x:hidden;
    }
    .studyRow {
        margin-left: 0px;
        margin-rigth: 0px;
        height:100%;
    }
    .row {
        margin:0;
    }

    a.list-group-item {
        background-color: black;
        padding: 2px;
    }
    a.list-group-item.active, a.list-group-item.active:hover, a.list-group-item.active:focus {
        background-color: #424242;
        border-color: #4e4e4e;
    }
    .nav-tabs>li.active>a, .nav-tabs>li.active>a:hover, .nav-tabs>li.active>a:focus {
        background-color: #424242;
        border-color: #4e4e4e;
    }

</style>
<body>
<div id="wrap">
    <nav class="myNav navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand">DICOM Database</a>
            </div>
            <ul class="nav navbar-nav navbar-right">
                <li><a id="help" href="#" class="button hidden-xs">Ayuda</a></li>
                <li><a id="about" href="#" class="button hidden-xs">Acerca</a></li>
            </ul>

        </div>
    </nav>
    <div class='main'>
        <ul id="tabs" class="nav nav-tabs" >
            <li class="active"><a href="#studyList" data-toggle="tab">Casos de estudio</a></li>
        </ul>

        <div id="tabContent" class="tab-content">
            <div id="studyList" class="tab-pane active">
                <div class="row">
                    <table  class="col-md-12 table table-striped">
                        <thead>
                        <tr>
                            <th>Nombre del paciente</th>
                            <th>ID del paciente</th>
                            <th>Fecha del estudio</th>
                            <th>Modalidad</th>
                            <th>Estudio</th>
                            <th># Imagen</th>
                        </tr>
                        </thead>
                        <tbody id="studyListData">

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div id="studyViewerTemplate" class="tab-pane active hidden" style="height:100%">
            <div class="studyContainer" style="height:100%">
                <div class="studyRow row" style="height:100%">
                    <div class="thumbnailSelector">
                        <div class="thumbnails list-group">
                        </div>
                    </div>
                    <div class="viewer">
                        <div class="text-center" >
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="WW/WC"><span class="fa fa-sun-o"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Invert"><span class="fa fa-adjust"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Zoom"><span class="fa fa-search"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Pan"><span class="fa fa-arrows"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Stack Scroll"><span class="fa fa-bars"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Length Measurement"><span class="fa fa-arrows-v"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Pixel Probe"><span class="fa fa-dot-circle-o"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Elliptical ROI"><span class="fa fa-circle-o"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Rectangle ROI"><span class="fa fa-square-o"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Play Clip"><span class="fa fa-play"></span></button>
                                <button type="button" class="btn btn-sm btn-default" data-container='body' data-toggle="tooltip" data-placement="bottom" title="Stop Clip"><span class="fa fa-stop"></span></button>
                            </div>
                        </div>
                        <div class="imageViewer">
                            <div class="viewportWrapper" style="width:100%;height:100%;position:relative;color: white;display:inline-block;background-color:black;"
                                 oncontextmenu="return false"
                                 class='cornerstone-enabled-image'
                                 unselectable='on'
                                 onselectstart='return false;'
                                 onmousedown='return false;'>
                                <div class="viewport">
                                </div>
                                <div class="overlay" style="top:0px; left:0px">
                                    <div>Nombre del paciente</div>
                                    <div>ID del paciente</div>
                                </div>
                                <div class="overlay" style="top:0px; right:0px">
                                    <div>Descripción del estudio</div>
                                    <div>Fecha del estudio</div>
                                </div>

                                <div class="overlay" style="bottom:0px; left:0px">
                                    <div class="fps">FPS:</div>
                                    <div class="renderTime">Render Time:</div>
                                    <div class="currentImageAndTotalImages">Imagen #:</div>
                                </div>
                                <div class="overlay" style="bottom:0px; right:0px">
                                    <div>Zoom:</div>
                                    <div>WW/WC:</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
        </div>
    </div>
</div>

<div class="modal fade" id="helpModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Ayuda</h4>
            </div>
            <div class="modal-body">
                <ol>
                    <li>Seleccione un caso de estudio para ver la imagen DICOM asociada</li>
                    <li>Puede interactuar con la imagen usando su mouse. El click derecho, izquierdo y scroll tienen funciones asociadas</li>
                    <li>Puede seleccionar distintas herramientas en el panel central</li>
                </ol>
                <br>
                Se requiere un navegador capaz de utilizar HTML5.
                <br>
             </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="aboutModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Acerca</h4>
            </div>
            <div class="modal-body">
                <br>
                Este sitio web hace uso de Cornerstone, visor open Source.  El renderizado se hace en el lado del cliente
				utilizando HTML5 y Javascript.
                <br>
                <br>
                <a href="https://github.com/chafey/cornerstone" target="_blank">Cornerstone</a>
            
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<script src="jquery-2.1.0.min.js"></script>

<!-- include the hammer.js library for touch gestures-->
<script src="jquery.hammer-full.js"></script>

<script src="bootstrap.min.js" ></script>

<!-- include the cornerstone library -->
<script src="cornerstone.min.js"></script>

<!-- include the cornerstone library -->
<script src="cornerstoneMath.min.js"></script>

<!-- include the cornerstone tools library -->
<script src="cornerstoneTools.min.js"></script>

<!-- include the cornerstoneWADOImageLoader library -->
<script src="cornerstoneWADOImageLoader.min.js"></script>

<script src="cornerstoneWebImageLoader.min.js"></script>

<!-- include the dicomParser library -->
<script src="dicomParser.min.js"></script>

    <script>

    $('#tabs a').click(function (e) {
        e.preventDefault()
        $(this).tab('show')
    })

    function loadStudyJson(studyViewer, studyId)
    {
        $.getJSON('studies/' + studyId, function(data) {
            // Load the first series into the viewport
            $('#wadoURL').val();

            var stacks = [];
            var currentStackIndex = 0;
            var seriesIndex = 0;
            data.seriesList.forEach(function(series) {
                var stack = {
                    seriesDescription: series.seriesDescription,
                    stackId : series.seriesNumber,
                    imageIds: [],
                    seriesIndex : seriesIndex,
                    currentImageIdIndex: 0,
                    frameRate: series.frameRate
                }
                if(series.numberOfFrames !== undefined) {
                    var numberOfFrames = series.numberOfFrames;
                    for(var i=0; i < numberOfFrames; i++) {
                        var imageId = series.instanceList[0].imageId + "?frame=" + i;
                        if(imageId.substr(0, 4) !== 'http') {
                            //imageId = "dicomweb:http://localhost:8042/instances" + imageId;
							//imageId = "dicomweb://cornerstonetech.org/images/ClearCanvas/" + imageId;
							imageId=imageId;
                        }
                        stack.imageIds.push(imageId);
                    }
                } else {
                    series.instanceList.forEach(function(image) {
                        var imageId = image.imageId;
                        if(image.imageId.substr(0, 4) !== 'http') {
                            //imageId = "dicomweb:http://localhost:8042/instances" + image.imageId;
							//imageId = "dicomweb://cornerstonetech.org/images/ClearCanvas/" + imageId;
							imageId=image.imageId;
                        }
                        stack.imageIds.push(imageId);
                    });

                }
                seriesIndex++;
                stacks.push(stack);
            });

            // resize the parent div of the viewport to fit the screen
            var imageViewer = $(studyViewer).find('.imageViewer')[0];
            var viewportWrapper = $(imageViewer).find('.viewportWrapper')[0];
            var parentDiv = $(studyViewer).find('.viewer')[0];
            viewportWrapper.style.width = (parentDiv.style.width - 10) + "px";
            viewportWrapper.style.height= (window.innerHeight - 150) + "px";

            var studyRow = $(studyViewer).find('.studyRow')[0];
            var width = $(studyRow).width();
            $(parentDiv).width(width - 170);
            viewportWrapper.style.width = (parentDiv.style.width - 10) + "px";
            viewportWrapper.style.height= (window.innerHeight - 150) + "px";

            // image enable the dicomImage element and activate a few tools
            var element = $(studyViewer).find('.viewport')[0];
            var parent = $(element).parent();
            var childDivs = $(parent).find('.overlay');
            var topLeft = $(childDivs[0]).find('div');
            $(topLeft[0]).text(data.patientName);
            $(topLeft[1]).text(data.patientId);
            var topRight= $(childDivs[1]).find('div');
            $(topRight[0]).text(data.studyDescription);
            $(topRight[1]).text(data.studyDate);
            var bottomLeft = $(childDivs[2]).find('div');
            var bottomRight = $(childDivs[3]).find('div');

            function onNewImage(e) {
                // if we are currently playing a clip then update the FPS
                var playClipToolData = cornerstoneTools.getToolState(element, 'playClip');
                if(playClipToolData !== undefined && playClipToolData.data.length > 0 && playClipToolData.data[0].intervalId !== undefined && e.detail.frameRate !== undefined) {
                    $(bottomLeft[0]).text("FPS: " + Math.round(e.detail.frameRate));
                    //console.log('frameRate: ' + e.detail.frameRate);
                } else {
                    if($(bottomLeft[0]).text().length > 0) {
                        $(bottomLeft[0]).text("");
                    }
                }
                $(bottomLeft[2]).text("Image #" + (stacks[currentStackIndex].currentImageIdIndex + 1) + "/" + stacks[currentStackIndex].imageIds.length);
            }
            element.addEventListener("CornerstoneNewImage", onNewImage, false);

            function onImageRendered(e) {
                $(bottomRight[0]).text("Zoom:" + e.detail.viewport.scale.toFixed(2));
                $(bottomRight[1]).text("WW/WL:" + Math.round(e.detail.viewport.voi.windowWidth) + "/" + Math.round(e.detail.viewport.voi.windowCenter));
                $(bottomLeft[1]).text("Render Time:" + e.detail.renderTimeInMs + " ms");
            }
            element.addEventListener("CornerstoneImageRendered", onImageRendered, false);


            var imageId = stacks[currentStackIndex].imageIds[0];

            // image enable the dicomImage element
            cornerstone.enable(element);
                        cornerstone.loadAndCacheImage(imageId).then(function(image) {
                cornerstone.displayImage(element, image);
                if(stacks[0].frameRate !== undefined) {
                    cornerstone.playClip(element, stacks[0].frameRate);
                }

                cornerstoneTools.mouseInput.enable(element);
                cornerstoneTools.mouseWheelInput.enable(element);
                cornerstoneTools.touchInput.enable(element);

                // Enable all tools we want to use with this element
                cornerstoneTools.wwwc.activate(element, 1); // ww/wc is the default tool for left mouse button
                cornerstoneTools.pan.activate(element, 2); // pan is the default tool for middle mouse button
                cornerstoneTools.zoom.activate(element, 4); // zoom is the default tool for right mouse button
                cornerstoneTools.probe.enable(element);
                cornerstoneTools.length.enable(element);
                cornerstoneTools.ellipticalRoi.enable(element);
                cornerstoneTools.rectangleRoi.enable(element);
                cornerstoneTools.wwwcTouchDrag.activate(element);
                cornerstoneTools.zoomTouchPinch.activate(element);


                // stack tools
                cornerstoneTools.addStackStateManager(element, ['playClip']);
                cornerstoneTools.addToolState(element, 'stack', stacks[0]);
                cornerstoneTools.stackScrollWheel.activate(element);
                cornerstoneTools.stackPrefetch.enable(element);


                function disableAllTools()
                {
                    cornerstoneTools.wwwc.disable(element);
                    cornerstoneTools.pan.activate(element, 2); // 2 is middle mouse button
                    cornerstoneTools.zoom.activate(element, 4); // 4 is right mouse button
                    cornerstoneTools.probe.deactivate(element, 1);
                    cornerstoneTools.length.deactivate(element, 1);
                    cornerstoneTools.ellipticalRoi.deactivate(element, 1);
                    cornerstoneTools.rectangleRoi.deactivate(element, 1);
                    cornerstoneTools.stackScroll.deactivate(element, 1);
                    cornerstoneTools.wwwcTouchDrag.deactivate(element);
                    cornerstoneTools.zoomTouchDrag.deactivate(element);
                    cornerstoneTools.panTouchDrag.deactivate(element);
                    cornerstoneTools.stackScrollTouchDrag.deactivate(element);
                }

                var buttons = $(studyViewer).find('button');
                // Tool button event handlers that set the new active tool
                $(buttons[0]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.wwwc.activate(element, 1);
                    cornerstoneTools.wwwcTouchDrag.activate(element);
                });
                $(buttons[1]).on('click touchstart',function() {
                    disableAllTools();
                    var viewport = cornerstone.getViewport(element);
                    if(viewport.invert === true) {
                        viewport.invert = false;
                    }
                    else {
                        viewport.invert = true;
                    }
                    cornerstone.setViewport(element, viewport);
                });
                $(buttons[2]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.zoom.activate(element, 5); // 5 is right mouse button and left mouse button
                    cornerstoneTools.zoomTouchDrag.activate(element);
                });
                $(buttons[3]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.pan.activate(element, 3); // 3 is middle mouse button and left mouse button
                    cornerstoneTools.panTouchDrag.activate(element);
                });
                $(buttons[4]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.stackScroll.activate(element, 1);
                    cornerstoneTools.stackScrollTouchDrag.activate(element);
                });
                $(buttons[5]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.length.activate(element, 1);
                });
                $(buttons[6]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.probe.activate(element, 1);
                });
                $(buttons[7]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.ellipticalRoi.activate(element, 1);
                });
                $(buttons[8]).on('click touchstart',function() {
                    disableAllTools();
                    cornerstoneTools.rectangleRoi.activate(element, 1);
                });
                $(buttons[9]).on('click touchstart',function() {
                    var frameRate = stacks[currentStackIndex].frameRate;
                    if(frameRate === undefined) {
                        frameRate = 10;
                    }
                    cornerstoneTools.playClip(element, 31);
                });
                $(buttons[10]).on('click touchstart',function() {
                    cornerstoneTools.stopClip(element);
                });
                $(buttons[0]).tooltip();
                $(buttons[1]).tooltip();
                $(buttons[2]).tooltip();
                $(buttons[3]).tooltip();
                $(buttons[4]).tooltip();
                $(buttons[5]).tooltip();
                $(buttons[6]).tooltip();
                $(buttons[7]).tooltip();
                $(buttons[8]).tooltip();
                $(buttons[9]).tooltip();

                var seriesList = $(studyViewer).find('.thumbnails')[0];
                stacks.forEach(function(stack) {
                    var seriesEntry = '<a class="list-group-item" + ' +
                            'oncontextmenu="return false"' +
                        'unselectable="on"' +
                        'onselectstart="return false;"' +
                        'onmousedown="return false;">' +
                                '<div class="csthumbnail"' +
                            'oncontextmenu="return false"' +
                            'unselectable="on"' +
                            'onselectstart="return false;"' +
                            'onmousedown="return false;"></div>' +
                            "<div class='text-center small'>" + stack.seriesDescription + '</div></a>';
                    var seriesElement = $(seriesEntry).appendTo(seriesList);
                    var thumbnail = $(seriesElement).find('div')[0];
                    cornerstone.enable(thumbnail);
                    cornerstone.loadAndCacheImage(stacks[stack.seriesIndex].imageIds[0]).then(function(image) {
                        if(stack.seriesIndex === 0) {
                            $(seriesElement).addClass('active');
                        }
                        cornerstone.displayImage(thumbnail, image);

                    });
                    $(seriesElement).on('click touchstart', function () {
                        // make this series visible
                        var activeThumbnails = $(seriesList).find('a').each(function() {
                            $(this).removeClass('active');
                        });
                        $(seriesElement).addClass('active');

                        cornerstoneTools.stopClip(element);
                        cornerstoneTools.stackScroll.disable(element);
                        cornerstoneTools.stackScroll.enable(element, stacks[stack.seriesIndex], 0);
                        cornerstone.loadAndCacheImage(stacks[stack.seriesIndex].imageIds[0]).then(function(image) {
                            var defViewport = cornerstone.getDefaultViewport(element, image);
                            currentStackIndex = stack.seriesIndex;
                            cornerstone.displayImage(element, image, defViewport);
                            cornerstone.fitToWindow(element);
                            var stackState = cornerstoneTools.getToolState(element, 'stack');
                            stackState.data[0] = stacks[stack.seriesIndex];
                            stackState.data[0].currentImageIdIndex = 0;
                            cornerstoneTools.stackPrefetch.enable(element);
                            $(bottomLeft[1]).text("# Images: " + stacks[stack.seriesIndex].imageIds.length);

                            if(stacks[stack.seriesIndex].frameRate !== undefined) {
                                cornerstoneTools.playClip(element, stacks[stack.seriesIndex].frameRate);
                            }
                        });
                    });


                });

                function resizeStudyViewer() {
                    var studyRow = $(studyViewer).find('.studyRow')[0];
                    var height = $(studyRow).height();
                    var width = $(studyRow).width();
                    $(seriesList).height(height - 40);
                    $(parentDiv).width(width - 170);
                    viewportWrapper.style.width = (parentDiv.style.width - 10) + "px";
                    viewportWrapper.style.height= (window.innerHeight - 150) + "px";
                    cornerstone.resize(element, true);
                }

                $(window).resize(function() {
                    resizeStudyViewer();
                });
                resizeStudyViewer();

            });

        });
    }

    function resizeMain() {
        var height = $(window).height();
        $('#main').height(height - 50);
        $('#tabContent').height(height - 50 -42);
    }

    $(window).resize(function() {
        resizeMain();
    });
    resizeMain();


    $.getJSON('studyList.json', function(data)
    {
        data.studyList.forEach(function(study) {
            var studyRow = '<tr><td>' +
                    study.patientName + '</td><td>' +
                    study.patientId + '</td><td>' +
                    study.studyDate + '</td><td>' +
                    study.modality + '</td><td>' +
                    study.studyDescription + '</td><td>' +
                    study.numImages + '</td><td>' +
                    '</tr>';
             var studyRowElement = $(studyRow).appendTo('#studyListData');
            $(studyRowElement).click(function() {
                // Add new tab for this study and switch to it
                var studyTab = '<li><a href="#x' + study.patientId + '" data-toggle="tab">' + study.patientName + '</a></li>';
                $('#tabs').append(studyTab);

                // add tab content by making a copy of the studyViewerTemplate element
                var studyViewerCopy = $('#studyViewerTemplate').clone();
                studyViewerCopy.attr("id", 'x' + study.patientId);
                studyViewerCopy.removeClass('hidden');
                studyViewerCopy.appendTo('#tabContent');

                // show the new tab (which will be the last one since it was just added
                $('#tabs a:last').tab('show');

                $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
                    $(window).trigger('resize');
                });

                // Now load the study.json
                loadStudyJson(studyViewerCopy, study.studyId + ".json");
            });
        });



    });

    $("#help").click(function() {
        $("#helpModal").modal();
    });

    $("#about").click(function() {
        $("#aboutModal").modal();
    });


    // prevent scrolling on ios
    document.body.addEventListener('touchmove', function(e){ e.preventDefault(); });


</script>

</body>
</html>