# Event Photos

EventPhotosModule generated by ModuleStudio 1.3.0. This module is intended for being used with Zikula 1.5.4 and later.

The idea of this module: Create a very simple album to share some nice photo collections

## INSTALLATION INSTRUCTIONS

1. Copy RKEventPhotosModule into your `modules` directory. Afterwards you should have a folder named `modules/RK/EventPhotosModule/Resources` (where `Resources`the code of the module is).
2. Initialize and activate RKEventPhotosModule in the extensions administration.
3. Move or copy the directory `Resources/userdata/RKEventPhotosModule/` to `/userdata/RKEventPhotosModule/`.
   Note this step is optional as the install process can create these folders, too.
4. Make the directory `/userdata/RKEventPhotosModule/` writable including all sub folders.

## Module capabilities

### Hooks

EventPhotosModule do have Album ui hooks provider. With this you can hook any album to any other module item e.g. NewsModule.

### Categories

You can map the album entity or the album item entity to the category system of Zikula.

### Scribite

There is the standard Scribite implementation of Modulestudio available.

### Content

There are the standard Content plugins of Modulestudio implemented.

## Configuration

### Special Album settings

The album is shown by the script flexImages. The maximum hight of the rows can be managed here.

### List Views
Here you can configure parameters for list views.
### Images
Image settings for album items image like shrinking or the different sizes for the images in different views.
### Integration
These options allow you to configure integration aspects. The selection will be used by the Scribite Plugin.
### Workflows
Here you can inspect and amend the existing workflows. This should be used by very experienced users only.

## Third Party Scripts
### fancyBox
 
 FancyBox is the used lightbox for showing the images. They are touch enabled and responsive.
 The configuration of that plugin is located in ``Resources/public/js/RKEventPhotosModule.fancyBox.js``
 Normally there is no need to change anything here.
 
 https://github.com/fancyapps/fancybox

### flexImages

FlexImges are used for creating fluid galleries in the album display.

https://github.com/Pixabay/jQuery-flexImages

## Bug reports, issues and improvements

If you find any bug or you have some issues or improvements please do not hesitate to add an issue in the [Github issue tracker] (https://github.com/rallek/EventPhotosModule/issues) of this module. Any pull request is wellcome. 

## Many Thanks

Without [modulestudio] (https://modulestudio.de) this module would never existing. I am not a programmer. My own skills are very limited. But I do not have to thank Axel for his module but also for his patience and additional help during the development of this module. His support and his modulestudio are a big help. Thank You!
