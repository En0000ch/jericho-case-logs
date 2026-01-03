=== WP 3D Thingviewer Lite ===
Contributors: accolore
Donate link: https://paypal.me/accolore
Tags: 3d,model,thingviewer,thing,viewer,stl,fbx,obj,glb,draco,drc,printer
Requires at least: 4.8
Tested up to: 6.7.2
Stable tag: trunk
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin provide a 3D model files viewer to embed your 3d models on your Wordpress pages or posts.

== Description ==

This plugin provide a 3D model files viewer to embed your 3d models on your Wordpress pages or posts.
You can load models into Wordpress media into multiple format (STL, OBJ, FBX, GLB) and then, by using the plugin shortcode generator, you can embed the thingviewer into you pages or posts.
In the thingviewer you can move, rotate and zoom the camera (with mouse or with keyboard). A set of option buttons are provided to let you access some useful information.
- Help window button to display the mouse and keyboard commands to operate the thingviewer.
- Info window button display some useful information about the loaded model, like surface are, volume, bounding box.
- Fullscreen button let you switch to fullscreen mode. You can go back to the "windowed" mode by clicking on the "close" button.
- Bounding box button display around the loaded model a wireframe bounding box.

Theese are the features of the plugin:

1. Gutenberg block editor support
1. Ease of use via shortcode button generator
1. Display a preview image for the 3d model
1. Support multiple 3d model file types (STL, FBX, OBJ, GLB)
1. Support textured model in GLB file format
1. Multiple thingviewers per page or post
1. Info window information visilibity
	* model filename
    * model size in KBytes
    * model triangles number
    * model surface area in cm2
	* model volume in cm3
	* model bounding box size in cm
1. Fully translatable via .PO files
1. Built on Wordpress Plugin Boilerplate by Tom McFarlin.
1. Uses Three.js library to render the viewer.

There is available a paid version of the plugin that have more customization options here:

https://codecanyon.net/item/wp-3d-thingviewer/23631168

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload `wp-3d-thingviewer-lite` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the `Plugins` menu in WordPress

== Frequently Asked Questions ==

= How can I display the thingviewer? =

Before all you have to load into the media of your Wordpress the STL file of the 3D model you want to display.
After that you can use the shortcode to display the thingviewer into your pages or posts.

The plugin provide a shortcode generator button into Classic Editor and Gutenberg.

This is the shortcode manual use:

`[wp-3dtvl model_file="http://www.mywebsite.com/wp-content/uploads/2019/01/case.stl"][/wp-3dtvl]`

The parameters are the following

* model_file: specify the model file URL to display.

The plugin display a thingviewer that load the selected STL file and let you interact with this.
The thingviewer is always resized at the width of the parent element.
The height will be calculated to display the thingviewer in a 16:9 ratio.

You can display a preview image instead of the Thingviewer at page load.

This is the shortcode with the preview image:

`[wp-3dtvl model_file="http://www.mywebsite.com/wp-content/uploads/2019/01/case.stl" model_preview="http://www.mywebsite.com/wp-content/uploads/2019/01/preview_image.jpg"][/wp-3dtvl]`

If your 3d model is displayed in a incorrect rotation, you can rotate the model on loading, by specifying the rotation values on X, Y, and Z axis in the shortcode.
This is the usage:

`[wp-3dtvl model_file="http://www.mywebsite.com/wp-content/uploads/2019/01/case.stl" rotation_x=20 rotation_y=30][/wp-3dtvl]`

In this example the model will be rotated by 20 degrees on the X axis, by 30 degrees on the Y axis and by 0 degrees on the Z axis.

= What can I do in the thingviewer? =

You can display a preview image when the Thingviewer is loaded. 
You can switch from the prewie image to the Thingviewer by clicking the "toggle" button.
You can move and rotate the camera with mouse or with keyboard.
You can display an help window which display the available commands list.
You can display the bounding box of the model.
You can display an information window with some informations abount the loaded model (size, volume, surface area,...)

= Why the model I loaded is completely black? =

Probably because there are some problems with your model: holes, flipped normals.

= Can I insert multiple thingviewers in the same page? =

Yes, it is possible to embed multiple thingviewers in the same page.

= Can I change the colors of the model, grid and fog? =

The plugin does not have a configuration page that allow you to do this, howerver you can customize the colors by modify the thingviewer javascripts.
This feature is available in the full version.

= Can I decide what button display into the thingviewer? =

No. The button bar cannot be customized.
This feature is available in the full version.

= Where I can find the full version of this plugin? =

The full version of this plugin is available here:

https://codecanyon.net/item/wp-3d-thingviewer/23631168

== Screenshots ==

1. This is a shortcode example.
2. The thingviewer in action.
3. The the help window.
4. The bounding box.
5. The information window.

== Changelog ==

= 3.2 =
* Added support for DRACO model files
* Added automatic demo page creation ad plugin activation

= 3.1 =
* Fixed bug for missing files
* Deleted unused files

= 3.0 =
* Added Gutenberg block editor support
* Added model preview image with thingviewer activation button
* Added 3D model rotation on loading
* Added support for Popup Anything plugin (allow to load the thingviewer into a Popup Anything modal)
* Added support for model with texture (in GLB file format)
* Fixed thingviewer appearance on page load problem
* Fixed model volume and area calculation problem
* Bugfix for GLB model loading
* Changed three.js library to r136

= 2.1 =
* Bug fix for i18 class

= 2.0 =
* Added FBX, OBJ and GLB 3d model file support
* Added multiple thingviewer in the same page

= 1.2.5 =
* Bug fix

= 1.2.4 =
* Bug fix

= 1.2.3 =
* Bug fix

= 1.2.2 =
* Bug fix

= 1.2.1 =
* Bug fix

= 1.2 =
* Bug fix

= 1.1 =
* Bug fix

= 1.0 =
* First release.

== Upgrade Notice ==

=3.0=
* Added Gutenberg block editor support
* Added model preview image with thingviewer activation button
* Added 3D model rotation on loading
* Added support for Popup Anything plugin (allow to load the thingviewer into a Popup Anything modal)
* Added support for model with texture (in GLB file format)
* Fixed thingviewer appearance on page load problem
* Fixed model volume and area calculation problem
* Bugfix for GLB model loading
* Changed three.js library to r136

= 2.0 =
* Added FBX, OBJ and GLB 3d model file support
* Added multiple thingviewer in the same page

= 1.2.5 =
* Bug fix

= 1.2.4 =
* Bug fix

= 1.2.3 =
* Bug fix

= 1.2.2 =
* Bug fix

= 1.2.1 =
* Bug fix

= 1.2 =
* Bug fix

= 1.1 =
Bug fix

= 1.0 =
First release