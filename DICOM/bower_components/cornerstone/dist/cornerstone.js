/*! cornerstone - v0.3.0 - 2014-04-17 | (c) 2014 Chris Hafey | https://github.com/chafey/cornerstone */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    // Turn off jshint warnings about new Number() in borrowed code below
    /*jshint -W053 */

    // Taken from : http://stackoverflow.com/questions/17907445/how-to-detect-ie11
    function ie_ver(){
        var iev=0;
        var ieold = (/MSIE (\d+\.\d+);/.test(navigator.userAgent));
        var trident = !!navigator.userAgent.match(/Trident\/7.0/);
        var rv=navigator.userAgent.indexOf("rv:11.0");

        if (ieold) iev=new Number(RegExp.$1);
        if (navigator.appVersion.indexOf("MSIE 10") != -1) iev=10;
        if (trident&&rv!=-1) iev=11;

        return iev;
    }

    // module/private exports
    cornerstone.ieVersion = ie_ver;

    return cornerstone;
}(cornerstone));
/**
 * This module handles event dispatching
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    var ieVersion = cornerstone.ieVersion();

    function CustomEventIe ( event, params ) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent( 'CustomEvent' );
        evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
        return evt;
    }

    CustomEventIe.prototype = window.Event.prototype;

    function cornerstoneEvent(enabledElement, eventName, obj) {
        if(enabledElement === undefined) {
            throw "cornerstoneEvent: parameter enabledElement cannot be undefined";
        }
        if(eventName === undefined) {
            throw "cornerstoneEvent: parameter eventName cannot be undefined";
        }

        if(obj === undefined) {
            obj = {};
        }

        obj.viewport = enabledElement.viewport;
        obj.element = enabledElement.element;
        obj.image = enabledElement.image;
        obj.enabledElement = enabledElement;

        var event;
        if(ieVersion <= 11) {
            event = new CustomEventIe(
                eventName,
                {
                    detail: obj,
                    bubbles: false,
                    cancelable: false
                }
            );
        } else {
            event = new CustomEvent(
                eventName,
                {
                    detail: obj,
                    bubbles: false,
                    cancelable: false
                }
            );
        }
        enabledElement.element.dispatchEvent(event);
    }

    // module/private exports
    cornerstone.event = cornerstoneEvent;

    return cornerstone;
}(cornerstone));
/**
 * This module is responsible for enabling an element to display images with cornerstone
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * sets a new image object for a given element
     * @param element
     * @param image
     */
    function displayImage(element, image, viewport) {
        if(element === undefined) {
            throw "displayImage: parameter element cannot be undefined";
        }
        if(image === undefined) {
            throw "displayImage: parameter image cannot be undefined";
        }

        var enabledElement = cornerstone.getEnabledElement(element);

        enabledElement.image = image;

        if(enabledElement.viewport === undefined) {
            enabledElement.viewport = cornerstone.getDefaultViewport(enabledElement.canvas, image);
        }

        // merge viewport
        if(viewport) {
            for(var attrname in viewport)
            {
                if(viewport[attrname] !== null) {
                    enabledElement.viewport[attrname] = viewport[attrname];
                }
            }
        }

        cornerstone.updateImage(element);
        cornerstone.event(enabledElement, "CornerstoneViewportUpdated");
        cornerstone.event(enabledElement, "CornerstoneNewImage");
    }

    // module/private exports
    cornerstone.displayImage = displayImage;

    return cornerstone;
}(cornerstone));
/**
 * This module is responsible for drawing an image to an enabled elements canvas element
 */

var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    var grayscaleRenderCanvas = document.createElement('canvas');
    var grayscaleRenderCanvasContext;
    var grayscaleRenderCanvasData;

    var colorRenderCanvas = document.createElement('canvas');
    var colorRenderCanvasContext;
    var colorRenderCanvasData;

    var lastRenderedImageId;
    var lastRenderedViewport = {};

    function initializeGrayscaleRenderCanvas(image)
    {
        // Resize the canvas
        grayscaleRenderCanvas.width = image.width;
        grayscaleRenderCanvas.height = image.height;

        // NOTE - we need to fill the render canvas with white pixels since we control the luminance
        // using the alpha channel to improve rendering performance.
        grayscaleRenderCanvasContext = grayscaleRenderCanvas.getContext('2d');
        grayscaleRenderCanvasContext.fillStyle = 'white';
        grayscaleRenderCanvasContext.fillRect(0,0, grayscaleRenderCanvas.width, grayscaleRenderCanvas.height);
        grayscaleRenderCanvasData = grayscaleRenderCanvasContext.getImageData(0,0,image.width, image.height);
    }

    function initializeColorRenderCanvas(image)
    {
        // Resize the canvas
        colorRenderCanvas.width = image.width;
        colorRenderCanvas.height = image.height;

        // get the canvas data so we can write to it directly
        colorRenderCanvasContext = colorRenderCanvas.getContext('2d');
        colorRenderCanvasContext.fillStyle = 'white';
        colorRenderCanvasContext.fillRect(0,0, colorRenderCanvas.width, colorRenderCanvas.height);
        colorRenderCanvasData = colorRenderCanvasContext.getImageData(0,0,image.width, image.height);
    }


    function getLut(image, viewport)
    {
        // if we have a cached lut and it has the right values, return it immediately
        if(image.lut !== undefined &&
           image.lut.windowCenter === viewport.voi.windowCenter &&
            image.lut.windowWidth === viewport.voi.windowWidth &&
            image.lut.invert === viewport.invert) {
            return image.lut;
        }

        // lut is invalid or not present, regenerate it and cache it
        cornerstone.generateLut(image, viewport.voi.windowWidth, viewport.voi.windowCenter, viewport.invert);
        image.lut.windowWidth = viewport.voi.windowWidth;
        image.lut.windowCenter = viewport.voi.windowCenter;
        image.lut.invert = viewport.invert;
        return image.lut;
    }

    function doesImageNeedToBeRendered(enabledElement, image)
    {
        if(image.imageId !== lastRenderedImageId ||
           lastRenderedViewport.windowCenter !== enabledElement.viewport.voi.windowCenter ||
           lastRenderedViewport.windowWidth !== enabledElement.viewport.voi.windowWidth ||
           lastRenderedViewport.invert !== enabledElement.viewport.invert)
        {
            return true;
        }

        return false;
    }

    function getRenderCanvas(enabledElement, image)
    {
        // apply the lut to the stored pixel data onto the render canvas
        if(image.color) {

            if(enabledElement.viewport.voi.windowWidth === 256 &&
                enabledElement.viewport.voi.windowCenter === 127 &&
                enabledElement.viewport.invert === false)
            {
                // the color image voi/invert has not been modified, request the canvas that contains
                // it so we can draw it directly to the display canvas
                return image.getCanvas();
            }
            else
            {
                if(doesImageNeedToBeRendered(enabledElement, image) === false) {
                    return colorRenderCanvas;
                }

                // If our render canvas does not match the size of this image reset it
                // NOTE: This might be inefficient if we are updating multiple images of different
                // sizes frequently.
                if(colorRenderCanvas.width !== image.width || colorRenderCanvas.height != image.height) {
                    initializeColorRenderCanvas(image);
                }

                // get the lut to use
                var colorLut = getLut(image, enabledElement.viewport);

                // the color image voi/invert has been modified - apply the lut to the underlying
                // pixel data and put it into the renderCanvas
                cornerstone.storedColorPixelDataToCanvasImageData(image, colorLut, colorRenderCanvasData.data);
                colorRenderCanvasContext.putImageData(colorRenderCanvasData, 0, 0);
                return colorRenderCanvas;
            }
        } else {

            if(doesImageNeedToBeRendered(enabledElement, image) === false) {
                return grayscaleRenderCanvas;
            }


            // If our render canvas does not match the size of this image reset it
            // NOTE: This might be inefficient if we are updating multiple images of different
            // sizes frequently.
            if(grayscaleRenderCanvas.width !== image.width || grayscaleRenderCanvas.height != image.height) {
                initializeGrayscaleRenderCanvas(image);
            }

            // get the lut to use
            var lut = getLut(image, enabledElement.viewport);

            // gray scale image - apply the lut and put the resulting image onto the render canvas
            cornerstone.storedPixelDataToCanvasImageData(image, lut, grayscaleRenderCanvasData.data);
            grayscaleRenderCanvasContext.putImageData(grayscaleRenderCanvasData, 0, 0);
            return grayscaleRenderCanvas;
        }
    }


    /**
     * Internal API function to draw an image to a given enabled element
     * @param enabledElement
     * @param image
     */
    function drawImage(enabledElement) {
        if(enabledElement === undefined) {
            throw "drawImage: enabledElement parameter must not be undefined";
        }
        var image = enabledElement.image;
        if(image === undefined) {
            throw "drawImage: image must be loaded before it can be drawn";
        }

        var start = new Date();

        // get the canvas context and reset the transform
        var context = enabledElement.canvas.getContext('2d');
        context.setTransform(1, 0, 0, 1, 0, 0);

        // clear the canvas
        context.fillStyle = 'black';
        context.fillRect(0,0, enabledElement.canvas.width, enabledElement.canvas.height);

        // save the canvas context state and apply the viewport properties
        context.save();
        cornerstone.setToPixelCoordinateSystem(enabledElement, context);

        var renderCanvas = getRenderCanvas(enabledElement, image);

        lastRenderedImageId = image.imageId;
        lastRenderedViewport.windowCenter = enabledElement.viewport.voi.windowCenter;
        lastRenderedViewport.windowWidth = enabledElement.viewport.voi.windowWidth;
        lastRenderedViewport.invert = enabledElement.viewport.invert;

        // turn off image smooth/interpolation if pixelReplication is set in the viewport
        if(enabledElement.viewport.pixelReplication === true) {
            context.imageSmoothingEnabled = false;
            context.mozImageSmoothingEnabled = false; // firefox doesn't support imageSmoothingEnabled yet
        }
        else {
            context.imageSmoothingEnabled = true;
            context.mozImageSmoothingEnabled = true;
        }

        // Draw the render canvas half the image size (because we set origin to the middle of the canvas above)
        context.drawImage(renderCanvas, 0,0, image.width, image.height, 0, 0, image.width, image.height);

        context.restore();

        var end = new Date();
        var diff = end - start;
        cornerstone.lastRenderTimeInMs = diff;

        cornerstone.event(enabledElement, "CornerstoneImageRendered", {canvasContext: context});
    }

    // Module exports
    cornerstone.drawImage = drawImage;

    return cornerstone;
}(cornerstone));
/**
 * This module is responsible for enabling an element to display images with cornerstone
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    function enable(element) {
        if(element === undefined) {
            throw "enable: parameter element cannot be undefined";
        }

        var canvas = document.createElement('canvas');
        element.appendChild(canvas);

        var el = {
            element: element,
            canvas: canvas,
            image : undefined, // will be set once image is loaded
            data : {}
        };
        cornerstone.addEnabledElement(el);

        cornerstone.resize(element, true);

        return element;
    }

    // module/private exports
    cornerstone.enable = enable;

    return cornerstone;
}(cornerstone));
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    function getElementData(el, dataType) {
        var ee = cornerstone.getEnabledElement(el);
        if(ee.data.hasOwnProperty(dataType) === false)
        {
            ee.data[dataType] = {};
        }
        return ee.data[dataType];
    }

    function removeElementData(el, dataType) {
        var ee = cornerstone.getEnabledElement(el);
        delete ee.data[dataType];
    }

    // module/private exports
    cornerstone.getElementData = getElementData;
    cornerstone.removeElementData = removeElementData;

    return cornerstone;
}(cornerstone));
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    var enabledElements = [];

    function getEnabledElement(element) {
        if(element === undefined) {
            throw "getEnabledElement: parameter element must not be undefined";
        }
        for(var i=0; i < enabledElements.length; i++) {
            if(enabledElements[i].element == element) {
                return enabledElements[i];
            }
        }

        throw "element not enabled";
    }

    function addEnabledElement(enabledElement) {
        if(enabledElement === undefined) {
            throw "getEnabledElement: enabledElement element must not be undefined";
        }

        enabledElements.push(enabledElement);
    }

    function removeEnabledElement(element) {
        if(element === undefined) {
            throw "getEnabledElement: element element must not be undefined";
        }

        for(var i=0; i < enabledElements.length; i++) {
            if(enabledElements[i].element === element) {
                enabledElements[i].element.removeChild(enabledElements[i].canvas);
                enabledElements.splice(i, 1);
                return;
            }
        }
    }

    // module/private exports
    cornerstone.getEnabledElement = getEnabledElement;
    cornerstone.addEnabledElement = addEnabledElement;
    cornerstone.removeEnabledElement = removeEnabledElement ;

    return cornerstone;
}(cornerstone));
/**
 * This module will fit an image to fit inside the canvas displaying it such that all pixels
 * in the image are viewable
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * Adjusts an images scale and center so the image is centered and completely visible
     * @param element
     */
    function fitToWindow(element)
    {
        var enabledElement = cornerstone.getEnabledElement(element);
        var defaultViewport = cornerstone.getDefaultViewport(enabledElement.canvas, enabledElement.image);
        enabledElement.viewport.scale = defaultViewport.scale;
        enabledElement.viewport.translation.x = defaultViewport.translation.x;
        enabledElement.viewport.translation.y = defaultViewport.translation.y;
        cornerstone.updateImage(element);
    }

    cornerstone.fitToWindow = fitToWindow;

    return cornerstone;
}(cornerstone));
/**
 * This module generates a lut for an image
 */

var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * Creates a LUT used while rendering to convert stored pixel values to
     * display pixels
     *
     * @param image
     * @returns {Array}
     */
    function generateLut(image, windowWidth, windowCenter, invert)
    {
        if(image.lut === undefined) {
            image.lut = [];
        }
        var lut = image.lut;

        var maxPixelValue = image.maxPixelValue;
        var slope = image.slope;
        var intercept = image.intercept;
        var localWindowWidth = windowWidth;
        var localWindowCenter = windowCenter;

        var modalityLutValue;
        var voiLutValue;
        var clampedValue;
        var storedValue;

        if(invert === true) {
            for(storedValue = image.minPixelValue; storedValue <= maxPixelValue; storedValue++)
            {
                modalityLutValue = storedValue * slope + intercept;
                voiLutValue = (((modalityLutValue - (localWindowCenter)) / (localWindowWidth) + 0.5) * 255.0);
                clampedValue = Math.min(Math.max(voiLutValue, 0), 255);
                lut[storedValue] = Math.round(255 - clampedValue);
            }
        }
        else {
            for(storedValue = image.minPixelValue; storedValue <= maxPixelValue; storedValue++)
            {
                modalityLutValue = storedValue * slope + intercept;
                voiLutValue = (((modalityLutValue - (localWindowCenter)) / (localWindowWidth) + 0.5) * 255.0);
                clampedValue = Math.min(Math.max(voiLutValue, 0), 255);
                lut[storedValue] = Math.round(clampedValue);
            }
        }
    }


    // Module exports
    cornerstone.generateLut = generateLut;

    return cornerstone;
}(cornerstone));
/**
 * This module contains a function to get a default viewport for an image given
 * a canvas element to display it in
 *
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * Creates a new viewport object containing default values for the image and canvas
     * @param canvas
     * @param image
     * @returns viewport object
     */
    function getDefaultViewport(canvas, image) {
        if(canvas === undefined) {
            throw "getDefaultViewport: parameter canvas must not be undefined";
        }
        if(image === undefined) {
            throw "getDefaultViewport: parameter image must not be undefined";
        }
        var viewport = {
            scale : 1.0,
            translation : {
                x : 0,
                y : 0
            },
            voi : {
                windowWidth: image.windowWidth,
                windowCenter: image.windowCenter,
            },
            invert: image.invert,
            pixelReplication: false
        };

        // fit image to window
        var verticalScale = canvas.height / image.rows;
        var horizontalScale= canvas.width / image.columns;
        if(horizontalScale < verticalScale) {
            viewport.scale = horizontalScale;
        }
        else {
            viewport.scale = verticalScale;
        }
        return viewport;
    }

    // module/private exports
    cornerstone.getDefaultViewport = getDefaultViewport;

    return cornerstone;
}(cornerstone));
/**
 * This module returns a subset of the stored pixels of an image
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * Returns an array of stored pixels given a rectangle in the image
     * @param element
     * @param x
     * @param y
     * @param width
     * @param height
     * @returns {Array}
     */
    function getStoredPixels(element, x, y, width, height) {
        if(element === undefined) {
            throw "getStoredPixels: parameter element must not be undefined";
        }

        x = Math.round(x);
        y = Math.round(y);
        var ee = cornerstone.getEnabledElement(element);
        var storedPixels = [];
        var index = 0;
        var pixelData = ee.image.getPixelData();
        for(var row=0; row < height; row++) {
            for(var column=0; column < width; column++) {
                var spIndex = ((row + y) * ee.image.columns) + (column + x);
                storedPixels[index++] = pixelData[spIndex];
            }
        }
        return storedPixels;
    }

    // module exports
    cornerstone.getStoredPixels = getStoredPixels;

    return cornerstone;
}(cornerstone));
/**
 * This module deals with caching images
 */

var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    var imageCache = {
    };

    var cachedImages = [];

    var maximumSizeInBytes = 1024 * 1024 * 1024; // 1 GB
    var cacheSizeInBytes = 0;

    function setMaximumSizeBytes(numBytes)
    {
        if(numBytes === undefined) {
            throw "setMaximumSizeBytes: parameter numBytes must not be undefined";
        }
        if(numBytes.toFixed === undefined) {
            throw "setMaximumSizeBytes: parameter numBytes must be a number";
        }

        maximumSizeInBytes = numBytes;
        purgeCacheIfNecessary();
    }

    function purgeCacheIfNecessary()
    {
        // if max cache size has not been exceded, do nothing
        if(cacheSizeInBytes <= maximumSizeInBytes)
        {
            return;
        }

        // cache size has been exceeded, create list of images sorted by timeStamp
        // so we can purge the least recently used image
        function compare(a,b) {
            if(a.timeStamp > b.timeStamp) {
                return -1;
            }
            if(a.timeStamp < b.timeStamp) {
                return 1;
            }
            return 0;
        }
        cachedImages.sort(compare);

        // remove images as necessary
        while(cacheSizeInBytes > maximumSizeInBytes)
        {
            var lastCachedImage = cachedImages[cachedImages.length - 1];
            cacheSizeInBytes -= lastCachedImage.sizeInBytes;
            delete imageCache[lastCachedImage.imageId];
            cachedImages.pop();
        }
    }

    function putImagePromise(imageId, imagePromise) {
        if(imageId === undefined)
        {
            throw "getImagePromise: imageId must not be undefined";
        }
        if(imagePromise === undefined)
        {
            throw "getImagePromise: imagePromise must not be undefined";
        }

        if(imageCache.hasOwnProperty(imageId) === true) {
            throw "putImagePromise: imageId already in cache";
        }

        var cachedImage = {
            imageId : imageId,
            imagePromise : imagePromise,
            timeStamp : new Date(),
            sizeInBytes: 0
        };

        imageCache[imageId] = cachedImage;
        cachedImages.push(cachedImage);

        imagePromise.then(function(image) {
            cachedImage.loaded = true;

            if(image.sizeInBytes === undefined)
            {
                throw "putImagePromise: image does not have sizeInBytes property or";
            }
            if(image.sizeInBytes.toFixed === undefined) {
                throw "putImagePromise: image.sizeInBytes is not a number";
            }
            cachedImage.sizeInBytes = image.sizeInBytes;
            cacheSizeInBytes += cachedImage.sizeInBytes;
            purgeCacheIfNecessary();
        });
    }

    function getImagePromise(imageId) {
        if(imageId === undefined)
        {
            throw "getImagePromise: imageId must not be undefined";
        }
        var cachedImage = imageCache[imageId];
        if(cachedImage === undefined) {
            return undefined;
        }

        // bump time stamp for cached image
        cachedImage.timeStamp = new Date();
        return cachedImage.imagePromise;
    }

    function getCacheInfo() {
        return {
            maximumSizeInBytes : maximumSizeInBytes,
            cacheSizeInBytes : cacheSizeInBytes,
            numberOfImagesCached: cachedImages.length
        };
    }

    function purgeCache() {
        var oldMaximumSizeInBytes = maximumSizeInBytes;
        maximumSizeInBytes = 0;
        purgeCacheIfNecessary();
        maximumSizeInBytes = oldMaximumSizeInBytes;
    }

    // module exports

    cornerstone.imageCache = {
        putImagePromise : putImagePromise,
        getImagePromise: getImagePromise,
        setMaximumSizeBytes: setMaximumSizeBytes,
        getCacheInfo : getCacheInfo,
        purgeCache: purgeCache
    };

    return cornerstone;
}(cornerstone));
/**
 * This module deals with ImageLoaders, loading images and caching images
 */

var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    var imageLoaders = {};

    var unknownImageLoader;

    function loadImageFromImageLoader(imageId) {
        var colonIndex = imageId.indexOf(":");
        var scheme = imageId.substring(0, colonIndex);
        var loader = imageLoaders[scheme];
        var imagePromise;
        if(loader === undefined || loader === null) {
            if(unknownImageLoader !== undefined) {
                imagePromise = unknownImageLoader(imageId);
                return imagePromise;
            }
            else {
                return undefined;
            }
        }
        imagePromise = loader(imageId);
        return imagePromise;
    }

    // Loads an image given an imageId and returns a promise which will resolve
    // to the loaded image object or fail if an error occurred
    function loadImage(imageId) {
        if(imageId === undefined) {
            throw "loadImage: parameter imageId must not be undefined";
        }

        var imagePromise = cornerstone.imageCache.getImagePromise(imageId);
        if(imagePromise !== undefined) {
            return imagePromise;
        }

        imagePromise = loadImageFromImageLoader(imageId);
        if(imagePromise === undefined) {
            throw "loadImage: no image loader for imageId";
        }

        cornerstone.imageCache.putImagePromise(imageId, imagePromise);
        return imagePromise;
    }

    // registers an imageLoader plugin with cornerstone for the specified scheme
    function registerImageLoader(scheme, imageLoader) {
        imageLoaders[scheme] = imageLoader;
    }

    // Registers a new unknownImageLoader and returns the previous one (if it exists)
    function registerUnknownImageLoader(imageLoader) {
        var oldImageLoader = unknownImageLoader;
        unknownImageLoader = imageLoader;
        return oldImageLoader;
    }

    // module exports

    cornerstone.loadImage = loadImage;
    cornerstone.registerImageLoader = registerImageLoader;
    cornerstone.registerUnknownImageLoader = registerUnknownImageLoader;

    return cornerstone;
}(cornerstone));
/**
 * This module contains a helper function to covert page coordinates to pixel coordinates
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * Converts a point in the page coordinate system to the pixel coordinate
     * system
     * @param element
     * @param pageX
     * @param pageY
     * @returns {{x: number, y: number}}
     */

    function pageToPixel(element, pageX, pageY) {
        var ee = cornerstone.getEnabledElement(element);

        if(ee.image === undefined) {
            throw "image has not been loaded yet";
        }

        // TODO: replace this with a transformation matrix

        // convert the pageX and pageY to the canvas client coordinates
        var rect = element.getBoundingClientRect();
        var clientX = pageX - rect.left - window.scrollX;
        var clientY = pageY - rect.top - window.scrollY;

        // translate the client relative to the middle of the canvas
        var middleX = clientX - rect.width / 2.0;
        var middleY = clientY - rect.height / 2.0;

        // scale to image coordinates middleX/middleY
        var viewport = ee.viewport;

        // apply the scale
        var widthScale = ee.viewport.scale;
        var heightScale = ee.viewport.scale;
        if(ee.image.rowPixelSpacing < ee.image.columnPixelSpacing) {
            widthScale = widthScale * (ee.image.columnPixelSpacing / ee.image.rowPixelSpacing);
        }
        else if(ee.image.columnPixelSpacing < ee.image.rowPixelSpacing) {
            heightScale = heightScale * (ee.image.rowPixelSpacing / ee.image.columnPixelSpacing);
        }

        var scaledMiddleX = middleX / widthScale;
        var scaledMiddleY = middleY / heightScale;

        // apply pan offset
        var imageX = scaledMiddleX - viewport.translation.x;
        var imageY = scaledMiddleY - viewport.translation.y;

        // translate to image top left
        imageX += ee.image.columns / 2;
        imageY += ee.image.rows / 2;

        return {
            x: imageX,
            y: imageY
        };
    }

    // module/private exports
    cornerstone.pageToPixel = pageToPixel;

    return cornerstone;
}(cornerstone));
/**
 * This module is responsible for enabling an element to display images with cornerstone
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    function setCanvasSize(element, canvas)
    {
        // the device pixel ratio is 1.0 for normal displays and > 1.0
        // for high DPI displays like Retina
        var devicePixelRatio = window.devicePixelRatio;
        if(devicePixelRatio === undefined) {
            devicePixelRatio = 1.0;
        }

        canvas.width = element.clientWidth * devicePixelRatio;
        canvas.height = element.clientHeight * devicePixelRatio;
        canvas.style.width = element.clientWidth + "px";
        canvas.style.height = element.clientHeight + "px";
    }

    /**
     * resizes an enabled element and optionally fits the image to window
     * @param element
     * @param fitToWindow true to refit, false to leave viewport parameters as they are
     */
    function resize(element, fitToWindow) {

        var enabledElement = cornerstone.getEnabledElement(element);

        setCanvasSize(element, enabledElement.canvas);

        if(enabledElement.image === undefined ) {
            return;
        }

        if(fitToWindow === true) {
            cornerstone.fitToWindow(element);
        }
        else {
            cornerstone.updateImage(element);
        }
    }

    // module/private exports
    cornerstone.resize = resize;

    return cornerstone;
}(cornerstone));
/**
 * This module contains a function that will set the canvas context to the pixel coordinates system
 * making it easy to draw geometry on the image
 */

var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * Sets the canvas context transformation matrix to the pixel coordinate system.  This allows
     * geometry to be driven using the canvas context using coordinates in the pixel coordinate system
     * @param ee
     * @param context
     * @param scale optional scaler to apply
     */
    function setToPixelCoordinateSystem(enabledElement, context, scale)
    {
        if(enabledElement === undefined) {
            throw "setToPixelCoordinateSystem: parameter enabledElement must not be undefined";
        }
        if(context === undefined) {
            throw "setToPixelCoordinateSystem: parameter context must not be undefined";
        }

        // reset the transformation matrix
        context.setTransform(1, 0, 0, 1, 0, 0);
        // move origin to center of canvas
        context.translate(enabledElement.canvas.width/2, enabledElement.canvas.height / 2);

        // apply the scale
        var widthScale = enabledElement.viewport.scale;
        var heightScale = enabledElement.viewport.scale;
        if(enabledElement.image.rowPixelSpacing < enabledElement.image.columnPixelSpacing) {
            widthScale = widthScale * (enabledElement.image.columnPixelSpacing / enabledElement.image.rowPixelSpacing);
        }
        else if(enabledElement.image.columnPixelSpacing < enabledElement.image.rowPixelSpacing) {
            heightScale = heightScale * (enabledElement.image.rowPixelSpacing / enabledElement.image.columnPixelSpacing);
        }
        context.scale(widthScale, heightScale);

        // apply the pan offset
        context.translate(enabledElement.viewport.translation.x, enabledElement.viewport.translation.y);

        if(scale === undefined) {
            scale = 1.0;
        } else {
            // apply the font scale
            context.scale(scale, scale);
        }

        // translate the origin back to the corner of the image so the event handlers can draw in image coordinate system
        context.translate(-enabledElement.image.width / 2 / scale, -enabledElement.image.height/ 2 / scale);
    }

    // Module exports
    cornerstone.setToPixelCoordinateSystem = setToPixelCoordinateSystem;

    return cornerstone;
}(cornerstone));
/**
 * This module contains a function to convert stored pixel values to display pixel values using a LUT
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * This function transforms stored pixel values into a canvas image data buffer
     * by using a LUT.  This is the most performance sensitive code in cornerstone and
     * we use a special trick to make this go as fast as possible.  Specifically we
     * use the alpha channel only to control the luminance rather than the red, green and
     * blue channels which makes it over 3x faster.  The canvasImageDataData buffer needs
     * to be previously filled with white pixels.
     *
     * NOTE: Attribution would be appreciated if you use this technique!
     *
     * @param image the image object
     * @param lut the lut
     * @param canvasImageDataData a canvasImgageData.data buffer filled with white pixels
     */
    function storedPixelDataToCanvasImageData(image, lut, canvasImageDataData)
    {
        var canvasImageDataIndex = 3;
        var storedPixelDataIndex = 0;
        var numPixels = image.width * image.height;
        var storedPixelData = image.getPixelData();
        var localLut = lut;
        var localCanvasImageDataData = canvasImageDataData;
        while(storedPixelDataIndex < numPixels) {
            localCanvasImageDataData[canvasImageDataIndex] = localLut[storedPixelData[storedPixelDataIndex++]]; // alpha
            canvasImageDataIndex += 4;
        }
    }

    function storedColorPixelDataToCanvasImageData(image, lut, canvasImageDataData)
    {
        var canvasImageDataIndex = 0;
        var storedPixelDataIndex = 0;
        var numPixels = image.width * image.height * 4;
        var storedPixelData = image.getPixelData();
        var localLut = lut;
        var localCanvasImageDataData = canvasImageDataData;
        while(storedPixelDataIndex < numPixels) {
            localCanvasImageDataData[canvasImageDataIndex++] = localLut[storedPixelData[storedPixelDataIndex++]]; // red
            localCanvasImageDataData[canvasImageDataIndex++] = localLut[storedPixelData[storedPixelDataIndex++]]; // green
            localCanvasImageDataData[canvasImageDataIndex] = localLut[storedPixelData[storedPixelDataIndex]]; // blue
            storedPixelDataIndex+=2;
            canvasImageDataIndex+=2;
        }
    }


    // Module exports
    cornerstone.storedPixelDataToCanvasImageData = storedPixelDataToCanvasImageData;
    cornerstone.storedColorPixelDataToCanvasImageData = storedColorPixelDataToCanvasImageData;

   return cornerstone;
}(cornerstone));
/**
 * This module contains a function to immediately redraw an image
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    /**
     * Forces the image to be updated/redrawn for the specified enabled element
     * @param element
     */
    function updateImage(element) {
        var enabledElement = cornerstone.getEnabledElement(element);

        if(enabledElement.image === undefined) {
            throw "updateImage: image has not been loaded yet";
        }

        cornerstone.drawImage(enabledElement);
    }

    // module exports
    cornerstone.updateImage = updateImage;

    return cornerstone;
}(cornerstone));
/**
 * This module contains functions to deal with getting and setting the viewport for an enabled element
 */
var cornerstone = (function (cornerstone) {

    "use strict";

    if(cornerstone === undefined) {
        cornerstone = {};
    }

    function setViewport(element, viewport) {

        var enabledElement = cornerstone.getEnabledElement(element);

        enabledElement.viewport.scale = viewport.scale;
        enabledElement.viewport.translation.x = viewport.translation.x;
        enabledElement.viewport.translation.y = viewport.translation.y;
        enabledElement.viewport.voi.windowWidth = viewport.voi.windowWidth;
        enabledElement.viewport.voi.windowCenter = viewport.voi.windowCenter;
        enabledElement.viewport.invert = viewport.invert;
        enabledElement.viewport.pixelReplication = viewport.pixelReplication;

        // prevent window width from being < 1
        if(enabledElement.viewport.voi.windowWidth < 1) {
            enabledElement.viewport.voi.windowWidth = 1;
        }
        // prevent scale from getting too small
        if(enabledElement.viewport.scale < 0.0001) {
            enabledElement.viewport.scale = 0.25;
        }

        // Force the image to be updated since the viewport has been modified
        cornerstone.updateImage(element);

        cornerstone.event(enabledElement, "CornerstoneViewportUpdated");
    }

    /**
     * Returns the viewport for the specified enabled element
     * @param element
     * @returns {*}
     */
    function getViewport(element) {
        var enabledElement = cornerstone.getEnabledElement(element);

        var viewport = enabledElement.viewport;
        if(viewport === undefined) {
            return undefined;
        }
        return {
            scale : viewport.scale,
            translation : {
                x : viewport.translation.x,
                y : viewport.translation.y
            },
            voi : {
                windowWidth: viewport.voi.windowWidth,
                windowCenter : viewport.voi.windowCenter
            },
            invert : viewport.invert,
            pixelReplication: viewport.pixelReplication
        };
    }


    // module/private exports
    cornerstone.getViewport = getViewport;
    cornerstone.setViewport=setViewport;

    return cornerstone;
}(cornerstone));