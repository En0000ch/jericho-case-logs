'use strict';

class Thingviewer {
	constructor ( dom_element, params ) {
	
		var parent_element = document.getElementById(dom_element);

		// set defaults
		var tv_defaults = {
			model_color            : tv_options.model_color,                  //0x2776C8,
			fog_display            : tv_options.fog_display,                  //true,
			fog_color              : tv_options.fog_color,                    //0xCCCCCC,
			plane_wire_display     : tv_options.plane_wire_display,           //false
			plane_color            : tv_options.plane_color,                  //0xCCCCCC,
			wire_color             : tv_options.wire_color,                   //0xFFFFFF,
			light_color            : tv_options.light_color,                  //0xFFFFFF,
			bbox_color             : tv_options.bounding_box_color,           //0xFF0000,
			rotation_speed         : tv_options.camera_rotation_value / 5.,
			zoom_factor            : tv_options.zoom_factor,                  //1
			ambient_light_intensity: tv_options.ambient_light_intensity,      //0.7
			container_prefix       : tv_options.container_prefix,
			help_template          : tv_options.help_template,
			info_template          : tv_options.info_template,
			base_url               : tv_options.base_url,
			axis_up                : tv_options.axis_up,
		};
		
		var desired_width  = 0;
		var desired_height = 0;
		if ( typeof parent_element === 'undefined' ) {
			parent_element  = document.body;
			desired_width   = window.innerWidth;
			desired_height  = window.innerHeight;
		} else {
			desired_width  = parent_element.offsetWidth;
			desired_height = this._getHeightWidthRatio(desired_width);
		}
	
		this.tv_settings = [];
		this.tv_world    = [];
	
		// initialize settings
	
		this.tv_settings                     = this.extend(tv_defaults, params);
		this.tv_settings.desired_width       = desired_width;
		this.tv_settings.desired_height      = desired_height;
		this.tv_settings.parent_element      = parent_element;
		this.tv_settings.is_fullscreen       = false;
		this.tv_settings.is_info_visible     = false;
		this.tv_settings.is_help_visible     = false;
		this.tv_settings.is_bbox_visible     = false;
		this.tv_settings.is_visible          = false;
		this.tv_settings.is_object_loaded    = false;
		this.tv_settings.is_rotation_enabled = false;
	
		this.tv_settings.is_woocommerce      = this.tv_settings.parent_element.getAttribute('data-wc');

		this.tv_settings.rotation_x = this.tv_settings.parent_element.getAttribute('data-rot-x');
		this.tv_settings.rotation_y = this.tv_settings.parent_element.getAttribute('data-rot-y');
		this.tv_settings.rotation_z = this.tv_settings.parent_element.getAttribute('data-rot-z');

		this.tv_settings.tv_file     = this.tv_settings.parent_element.getAttribute('data-model');
		this.tv_settings.tv_file_url = this.tv_settings.parent_element.getAttribute('data-url');
		if (this.tv_settings.tv_file_url == '') this.tv_settings.tv_file_url = this.tv_settings.tv_file;

		this.tv_settings.tv_preview_url    = this.tv_settings.parent_element.getAttribute('data-preview');
		this.tv_settings.tv_background_url = this.tv_settings.parent_element.getAttribute('data-background');

		// model loaders
		this.tv_settings.stl_loader   = new THREE.STLLoader();
		this.tv_settings.fbx_loader   = new THREE.FBXLoader();
		this.tv_settings.obj_loader   = new THREE.OBJLoader();
		this.tv_settings.draco_loader = new THREE.DRACOLoader();
		this.tv_settings.gltf_loader  = new THREE.GLTFLoader();
		this.tv_settings.draco_loader.setDecoderPath( this.tv_settings.base_url + '/wp-content/plugins/wp-3d-thingviewer-lite/public/libraries/three-js-r145/' );
		this.tv_settings.draco_loader.setDecoderConfig( {type: 'js'} );
		this.tv_settings.draco_loader.preload();
		this.tv_settings.gltf_loader.setDRACOLoader(this.tv_settings.draco_loader);
		
		// create inner elements
		this.tv_settings.message_bar                = document.createElement('div');
		this.tv_settings.message_bar.classList.add(this.tv_settings.container_prefix + '-message');
		this.tv_settings.message_bar.style.display  = 'none';
		
		this.tv_settings.progress_bar               = document.createElement('div'); 
		this.tv_settings.progress_bar.classList.add(this.tv_settings.container_prefix + '-progress');
		this.tv_settings.progress_bar.style.display = 'none';
		
		var bar                                     = document.createElement('span');
		var icon                                    = document.createElement('div');
		icon.className                              = this.tv_settings.container_prefix + '-spinner';
		//bar.style.width                             = '100%';
		this.tv_settings.progress_bar.appendChild( bar );
		this.tv_settings.progress_bar.appendChild( icon );
	
		this.tv_settings.container               = document.createElement('div');
		this.tv_settings.container.classList.add(this.tv_settings.container_prefix + '-container');
		this.tv_settings.container.style.display = 'none';
	
		// initialize elements
		
		this.tv_settings.fullscreen_button_display       = tv_options.fullscreen_button_display;
		this.tv_settings.help_button_display             = tv_options.help_button_display;
		this.tv_settings.info_button_display             = tv_options.info_button_display;
		this.tv_settings.bbox_button_display             = tv_options.bounding_box_button_display;
		this.tv_settings.download_button_display         = tv_options.model_download_button_display;
		this.tv_settings.rotation_button_display         = tv_options.camera_rotation_button_display;
	
		this.tv_settings.fullscreen_button               = document.createElement('li');
		this.tv_settings.fullscreen_button.classList.add(this.tv_settings.container_prefix + '-fullscreen-button');
		this.tv_settings.fullscreen_button.innerHTML     = '<a href="#" title="Fullscreen toggle"><i class="' + this.tv_settings.container_prefix + '-resize-full-alt"></i></a>';
		this._setDisplay('fullscreen_button', false);
	
		this.tv_settings.thingviewer_button              = document.createElement('li');
		this.tv_settings.thingviewer_button.id           = this.tv_settings.container_prefix + '-thingviewer-button';
		this.tv_settings.thingviewer_button.innerHTML    = '<a href="#" title="Thingviewer toggle"><i class="' + this.tv_settings.container_prefix + '-cube"></i></a>';
	
		this.tv_settings.help_button                     = document.createElement('li');
		this.tv_settings.help_button.classList.add(this.tv_settings.container_prefix + '-help-button');
		this.tv_settings.help_button.innerHTML           = '<a href="#" title="Help"><i class="' + this.tv_settings.container_prefix + '-help"></i></a>';
		this._setDisplay('help_button', false);
	
		this.tv_settings.info_button                     = document.createElement('li');
		this.tv_settings.info_button.classList.add(this.tv_settings.container_prefix + '-info-button');
		this.tv_settings.info_button.innerHTML           = '<a href="#" title="Info"><i class="' + this.tv_settings.container_prefix + '-info"></i></a>';
		this._setDisplay('info_button', false);
	
		this.tv_settings.bbox_button                     = document.createElement('li');
		this.tv_settings.bbox_button.classList.add(this.tv_settings.container_prefix + '-boundingbox-button');
		this.tv_settings.bbox_button.innerHTML           = '<a href="#" title="Bounding box toggle"><i class="' + this.tv_settings.container_prefix + '-grid"></i></a>';
		this._setDisplay('bbox_button', false);
	
		this.tv_settings.download_button                 = document.createElement('li');
		this.tv_settings.download_button.classList.add(this.tv_settings.container_prefix + '-download-button');

		this.tv_settings.download_button.innerHTML       = '<a href="' + this.tv_settings.tv_file_url + '" title="Download model"><i class="' + this.tv_settings.container_prefix + '-download"></i></a>';

		this._setDisplay('download_button', false);	
	
		this.tv_settings.rotation_button                 = document.createElement('li');
		this.tv_settings.rotation_button.classList.add(this.tv_settings.container_prefix + '-rotation-button');
		this.tv_settings.rotation_button.innerHTML       = '<a href="#" title="Camera auto-rotation toggle"><i class="' + this.tv_settings.container_prefix + '-videocam"></i></a>';
		this._setDisplay('rotation_button', false);
	
		this.tv_settings.model_preview                   = document.createElement('img');
		this.tv_settings.model_preview.classList.add(this.tv_settings.container_prefix + '-model-preview');
		this._setDisplay('model_preview', false);
		//this.tv_settings.model_preview.setAttribute('src', this.tv_world.renderer.domElement.toDataURL());
		this.tv_settings.model_preview.setAttribute('src', this.tv_settings.tv_preview_url);
		this.tv_settings.model_preview.style.width  = desired_width + "px";
		this.tv_settings.model_preview.style.height = desired_height + "px";
	
		this.tv_settings.info_window               = document.createElement('div');
		this.tv_settings.info_window.classList.add(this.tv_settings.container_prefix + '-info-window');
		this._setDisplay('info_window', false);
	
		this.tv_settings.help_window               = document.createElement('div');
		this.tv_settings.help_window.classList.add(this.tv_settings.container_prefix + '-help-window');
		this.tv_settings.help_window.innerHTML     = this.tv_settings.help_template;
		this._setDisplay('help_window', false);
	
		this.tv_settings.buttons_container    = document.createElement('ul');
		this.tv_settings.buttons_container.classList.add(this.tv_settings.container_prefix + '-buttons');
	
		this.tv_settings.buttons_container.appendChild(this.tv_settings.help_button);
		this.tv_settings.buttons_container.appendChild(this.tv_settings.fullscreen_button);
		this.tv_settings.buttons_container.appendChild(this.tv_settings.info_button);
		this.tv_settings.buttons_container.appendChild(this.tv_settings.bbox_button);
		this.tv_settings.buttons_container.appendChild(this.tv_settings.download_button);
		this.tv_settings.buttons_container.appendChild(this.tv_settings.rotation_button);
		this.tv_settings.buttons_container.appendChild(this.tv_settings.thingviewer_button);
	
		this.tv_settings.parent_element.innerHTML = '';
		
		this.tv_settings.parent_element.appendChild(this.tv_settings.message_bar);
		this.tv_settings.parent_element.appendChild(this.tv_settings.progress_bar);
		this.tv_settings.parent_element.appendChild(this.tv_settings.info_window);
		this.tv_settings.parent_element.appendChild(this.tv_settings.help_window);
		this.tv_settings.parent_element.appendChild(this.tv_settings.buttons_container);
		this.tv_settings.parent_element.appendChild(this.tv_settings.model_preview);
	
		this.tv_settings.parent_element.style.backgroundColor = this.tv_settings.fog_color;
		//this.tv_settings.parent_element.style.backgroundColor = '#FF0000';
	
		this._init();
	}

	extend( defaults, options ) {
		if (options === 'undefined') {
			return defaults;
		}
		var extended = {};
		var prop;
		for (prop in defaults) {
			if (Object.prototype.hasOwnProperty.call(defaults, prop)) {
				extended[prop] = defaults[prop];
			}
		}
		for (prop in options) {
			if (Object.prototype.hasOwnProperty.call(options, prop)) {
				extended[prop] = options[prop];
			}
		}
		return extended;
	};
	
	_setDisplay ( element_name, show) {
		switch(element_name) {
			case 'fullscreen_button':
				this.tv_settings.fullscreen_button.style.display = ( show && ( this.tv_settings.fullscreen_button_display == 1 ) && ( this.tv_settings.is_woocommerce != 1 ) ) ? 'inline-block' : 'none';
				break;
			case 'help_button':
				this.tv_settings.help_button.style.display = ( show && ( this.tv_settings.help_button_display == 1 ) ) ? 'inline-block' : 'none';
				break;
			case 'info_button':
				this.tv_settings.info_button.style.display = ( show && ( this.tv_settings.info_button_display == 1 ) ) ? 'inline-block' : 'none';
				break;
			case 'bbox_button':
				this.tv_settings.bbox_button.style.display = ( show && ( this.tv_settings.bbox_button_display == 1 ) ) ? 'inline-block' : 'none';
				break;
			case 'download_button':
				this.tv_settings.download_button.style.display = ( show && ( this.tv_settings.download_button_display == 1 ) ) ? 'inline-block' : 'none';
				break;			
			case 'rotation_button':
				this.tv_settings.rotation_button.style.display = ( show && ( this.tv_settings.rotation_button_display == 1 ) ) ? 'inline-block' : 'none';
				break;
			case 'thingviewer_button':
				this.tv_settings.thingviewer_button.style.display = ( show ) ? 'inline-block' : 'none';
				break;
			case 'info_window':
				this.tv_settings.info_window.style.display = ( show ) ? 'inline-block' : 'none';
				break;
			case 'help_window':
				this.tv_settings.help_window.style.display = ( show ) ? 'inline-block' : 'none';
				break;
			case 'model_preview':
				this.tv_settings.model_preview.style.display = ( show ) ? 'inline-block' : 'none';
				break;
		}
	}

	_init() {
		
		this.tv_settings.parent_element.appendChild(this.tv_settings.container);

		// DETECT IF WEBGL ARE ENABLED
		if ( ! THREE.WEBGL.isWebGLAvailable() ) {
			var warning = WEBGL.getWebGLErrorMessage();
			this.tv_settings.message_bar.appendChild( warning );
			return;
		}

		// DEFINE SCENE VARIABLES
		this.tv_world.scene            = new THREE.Scene();
		this.tv_world.model            = [];
		this.tv_world.model_volume     = 0;
		this.tv_world.model_triangles  = 0;
		this.tv_world.model_area       = 0;
		this.tv_world.model_filesize   = 0;
		this.tv_world.bbox_data        = null;
		this.tv_world.bbox_grid        = null;
		this.tv_world.plane_size       = 1000;
		this.tv_world.plane_division   = 50
		this.tv_world.plane_material   = new THREE.MeshBasicMaterial({
			color    : this.tv_settings.plane_color,
			side     : THREE.FrontSide,
		});

		if (this.tv_settings.is_object_loaded == false) {
			this._loadModel();
		}

		this._initThreeScene();
		this._initThreeLights();
		this._initThreeControls();
		this._addEventListeners();
	}

	_initThreeScene() {
		// SCENE
		//this.tv_world.scene.background = new THREE.Color(this.tv_settings.fog_color);

		const loader = new THREE.TextureLoader();
		const texture = loader.load(this.tv_settings.tv_background_url);
		texture.magFilter = THREE.LinearFilter;
		texture.minFilter = THREE.LinearFilter;
		
		const shader = THREE.ShaderLib.equirect;
		const material = new THREE.ShaderMaterial({
			fragmentShader: shader.fragmentShader,
			vertexShader: shader.vertexShader,
			uniforms: shader.uniforms,
			depthWrite: false,
			side: THREE.BackSide,
		});
		material.uniforms.tEquirect.value = texture;
		const plane1 = new THREE.BoxBufferGeometry(this.tv_world.plane_size/2, this.tv_world.plane_size/2, this.tv_world.plane_size/2);
		this.tv_world.background_mesh = new THREE.Mesh(plane1, material);
		this.tv_world.scene.add(this.tv_world.background_mesh);

		// CAMERA
		this.tv_world.camera = new THREE.PerspectiveCamera( 37.5, this.tv_settings.desired_width / this.tv_settings.desired_height, 1, 10000 );
		this.tv_world.camera.position.set(this.tv_world.plane_size/2, this.tv_world.plane_size/2, 100);
		switch(this.tv_settings.axis_up) {
			case 'x_up':
				this.tv_world.camera.up.set(1,0,0);
				break;
			case 'z_up':
				this.tv_world.camera.up.set(0,0,1);
				break;
			case 'y_up':
			default:
				this.tv_world.camera.up.set(0,1,0);
		}

		// PLANE
		if ( this.tv_settings.plane_wire_display == false) {
			this.tv_world.plane = false;
		} else {
			var plane = new THREE.Plane( new THREE.Vector3( 0, 0, 1 ), 0 );
			this.tv_world.plane = new THREE.PlaneHelper( plane, this.tv_world.plane_size/2, this.tv_settings.plane_color );
			this.tv_world.plane.name = 'plane';
			//this.tv_world.plane.receiveShadow = true;
			this.tv_world.plane.position.set(0,0,-0.1);
			this.tv_world.scene.add( this.tv_world.plane );
		}
		
		// GRID
		if ( this.tv_settings.plane_wire_display == false) {
			this.tv_world.plane_wire = false;
		} else {
			this.tv_world.plane_wire = new THREE.GridHelper( this.tv_world.plane_size, this.tv_world.plane_division, this.tv_settings.wire_color, this.tv_settings.wire_color );
			this.tv_world.plane_wire.geometry.rotateX( Math.PI / 2 );
			this.tv_world.plane_wire.position.set(0,0,0);
			this.tv_world.plane_wire.name = 'planewire';
			this.tv_world.scene.add( this.tv_world.plane_wire );
		}
		
		// FOG
		if ( this.tv_settings.fog_display == true) {
			//this.tv_world.scene.fog = new THREE.Fog(this.tv_settings.fog_color, 50, 200);
			//this.tv_world.scene.fog = new THREE.FogExp2(this.tv_settings.fog_color,  0.015);
		}

		// RENDERER
		this.tv_world.renderer = new THREE.WebGLRenderer({
			antialias : true,
		});
		this.tv_world.renderer.shadowMap.enabled = true;
		this.tv_world.renderer.shadowMap.type = THREE.PCFSoftShadowMap;

		this.tv_world.renderer.setClearColor(this.tv_settings.fog_color, 1);
		this.tv_world.renderer.setSize(this.tv_settings.desired_width, this.tv_settings.desired_height);

		this.tv_settings.container.appendChild(this.tv_world.renderer.domElement);
	}

	_initThreeControls() {
		this.tv_world.controls = new THREE.OrbitControls( this.tv_world.camera, this.tv_settings.container );
		this.tv_world.controls.autoRotateSpeed = this.tv_settings.rotation_speed;
		this.tv_world.controls.enableDamping   = true;
		this.tv_world.controls.dampingFactor   = 0.1;
		this.tv_world.controls.rotateSpeed     = 0.5;
		this.tv_world.controls.panSpeed        = 0.8;
		this.tv_world.controls.minDistance     = 0;
		this.tv_world.controls.maxDistance     = 500;
		//this.tv_world.controls.target.set(0, 0, 0);
		/*
		this.tv_world.controls.touches = {
			ONE: THREE.TOUCH.ROTATE,
			TWO: THREE.TOUCH.DOLLY_PAN
		};
		*/

		this.tv_world.controls.update();
	}

	toggleFullscreen() {
		/*!
		* JavaScript detach - v0.2 - 5/18/2011
		* http://benalman.com/
		* 
		* Copyright (c) 2011 "Cowboy" Ben Alman
		* Dual licensed under the MIT and GPL licenses.
		* http://benalman.com/about/license/
		*/

		function detach(node, async, fn) {
			var parent = node.parentNode;
			var next = node.nextSibling;
			// No parent node? Abort!
			if (!parent) { return; }
			// Detach node from DOM.
			parent.removeChild(node);
			// Handle case where optional `async` argument is omitted.
			if (typeof async !== "boolean") {
				fn = async;
				async = false;
			}
			// Note that if a function wasn't passed, the node won't be re-attached!
			if (fn && async) {
				// If async == true, reattach must be called manually.
				fn.call(node, reattach);
			} else if (fn) {
				// If async != true, reattach will happen automatically.
				fn.call(node);
				reattach();
			}
			// Re-attach node to DOM.
			function reattach() {
				parent.insertBefore(node, next);
			}
		}

		detach(this.tv_settings.container);

		if(this.tv_settings.is_fullscreen == false) {
			this.tv_settings.is_fullscreen = true;
		
			this.tv_settings.fullscreen_button.children[0].children[0].className = this.tv_settings.container_prefix + '-cancel';

			this.tv_settings.help_button.children[0].className = '';
			this.tv_settings.info_button.children[0].className = '';
			this.tv_settings.is_info_visible                   = false;
			this.tv_settings.is_help_visible                   = false;

			this.tv_settings.container.style.position         = 'fixed';
			this.tv_settings.info_window.style.position       = 'fixed';
			this.tv_settings.help_window.style.position       = 'fixed';
			this.tv_settings.buttons_container.style.position = 'fixed';
			this.tv_settings.buttons_container.classList.add('fullscreen');
			this.tv_settings.info_window.classList.add('fullscreen');
			this.tv_settings.help_window.classList.add('fullscreen');

			// hide all other thingviewers
			document.querySelectorAll('.' + this.tv_settings.container_prefix + '-wrapper').forEach( (item, i) => {
				if (item.id != this.tv_settings.parent_element.id) {
					item.style.display = 'none';
				}
			});

			this._setDisplay('help_button', false);
			this._setDisplay('download_button', false);
			this._setDisplay('help_window', false);
			//this._setDisplay('info_button', false);
			this._setDisplay('info_window', false);
			this._setDisplay('thingviewer_button', false);

			this.resize( window.innerWidth, window.innerHeight);

			document.body.appendChild(this.tv_settings.container);
		} else {
			this.tv_settings.is_fullscreen   = false;

			this.tv_settings.fullscreen_button.children[0].children[0].className = this.tv_settings.container_prefix + '-resize-full-alt';

			this.tv_settings.info_button.children[0].className = '';
			this.tv_settings.is_info_visible = false;

			this.tv_settings.container.style.position         = 'relative';
			this.tv_settings.info_window.style.position       = 'absolute';
			this.tv_settings.help_window.style.position       = 'absolute';
			this.tv_settings.buttons_container.style.position = 'absolute';
			this.tv_settings.buttons_container.classList.remove('fullscreen');
			this.tv_settings.info_window.classList.remove('fullscreen');
			this.tv_settings.help_window.classList.remove('fullscreen');

			// show all other thingviewers
			document.querySelectorAll('.' + this.tv_settings.container_prefix + '-wrapper').forEach( (item, i) => {
				if (item.id != this.tv_settings.parent_element.id) {
					item.style.display = 'inline-block';
				}
			});

			this._setDisplay('help_button', true);
			this._setDisplay('download_button', true);
			this._setDisplay('info_button', true);
			this._setDisplay('info_window', false);
			if (this.tv_settings.tv_preview_url != '') {
				this._setDisplay('thingviewer_button', true);
			} else  {
				this._setDisplay('thingviewer_button', false);
			}

			this.resize( this.tv_settings.desired_width, this.tv_settings.desired_height );

			this.tv_settings.parent_element.appendChild( this.tv_settings.container );
		}

		this.tv_settings.rotation_button.children[0].className = '';
		this.tv_settings.is_rotation_enabled                   = false;
	}

	resize( width, height ) {
		this.tv_settings.container.width = width;
		this.tv_settings.container.height = height;

		this.tv_world.renderer.setSize( width, height );
		this.tv_world.camera.aspect = width / height;
		this._fitCameraToObject();
		this.tv_world.camera.updateProjectionMatrix();
	}

	_initThreeLights( ) {
		if (this.tv_settings.ambient_light_intensity > 0) {
			this.tv_world.ambient_light = new THREE.AmbientLight(this.tv_settings.light_color, this.tv_settings.ambient_light_intensity);
			this.tv_world.scene.add( this.tv_world.ambient_light );
		} 

		this.tv_world.spot_light = new THREE.SpotLight(this.tv_settings.light_color, 1);
		this.tv_world.spot_light.position.set(-1000, 1000, 1000);

		this.tv_world.scene.add( this.tv_world.spot_light );

		this.tv_world.point_light = new THREE.PointLight(this.tv_settings.light_color, 0.7);
		this.tv_world.point_light.position.set(3000, -4000, 3500);
		
		this.tv_world.scene.add( this.tv_world.point_light );			
	}

	_fitCameraToObject() {
		const offset   = {
			'xy': 5 * this.tv_settings.zoom_factor,
			'z' : 1.25 * this.tv_settings.zoom_factor,
		};
		const fov = this.tv_world.camera.fov * (Math.PI / 180);
		
		var bbox = new THREE.Vector3();

		this.tv_world.bbox_data.getSize(bbox);

		var cameraZ    = Math.abs(Math.max(bbox.x, bbox.y, bbox.z) / 2 * Math.tan(fov * 2)) * offset.z;
		var cameraXY   = Math.max(bbox.x, bbox.y) * offset.xy;
		this.tv_world.camera.position.set(cameraXY, cameraXY, cameraZ);

		// SET MAX ZOOM TO MAX OF BOUNDING BOX SIZE
		this.tv_world.controls.maxDistance = this.tv_world.plane_size;  

		/* 80 -> 0.015 */

		if ( this.tv_settings.fog_display == true) {
			this.tv_world.scene.fog = new THREE.FogExp2(this.tv_settings.fog_color, 1 / this.tv_world.controls.maxDistance);
		}

		this.tv_world.camera.updateProjectionMatrix();
		this.tv_world.controls.update();
	}

	_addModel( raw_data, file_extension ) {
		var geometry;

		geometry = raw_data;

		switch(geometry.type) {
			case 'Group':
				var group_geoms = this._groupToBufferGeometry(geometry);
				geometry = THREE.BufferGeometryUtils.mergeBufferGeometries(group_geoms, false);
			case 'Geometry':
			case 'BufferGeometry':
			default:
				this._rotateObject(geometry);
				geometry.computeVertexNormals();
				geometry.translate(0,0,0);
				geometry.computeBoundingBox();
				break;
		}

		var material = new THREE.MeshPhongMaterial({
			color       : this.tv_settings.model_color,
			flatShading : true,
			shininess   : 80,
			fog         : false,
			side        : THREE.DoubleSide,
		});

		var mesh = new THREE.Mesh( geometry, material );

		mesh.castShadow = true;
		mesh.receiveShadow = true;

		this.tv_world.scene.add(mesh);
		this.tv_world.bbox_data = geometry.boundingBox;
		this.tv_world.model = [];
		this.tv_world.model.push(mesh);
		
		// ADD BOUNDING BOX GRID (WITHOUT TRIANGLES ON FACES) 
		this.tv_world.bbox_grid = new THREE.BoxHelper( mesh );
		this.tv_world.bbox_grid.material.color = new THREE.Color(this.tv_settings.bbox_color);
		this.tv_world.scene.add( this.tv_world.bbox_grid );
		this.tv_world.bbox_grid.visible = false;

	}

	_addTexturedModel( raw_data, file_extension ) {
		var self = this;

		this._rotateObject(raw_data.scene);
		this._centerGroup(raw_data.scene);

		// GENERATE BOUNDING BOX
		this.tv_world.bbox_data = new THREE.Box3(new THREE.Vector3(0,0,0),new THREE.Vector3(0,0,0));
		this.tv_world.bbox_data = new THREE.Box3().setFromObject(raw_data.scene);

		// ADJUST MESHES ATTRIBUTES
		var meshes = this._groupToMeshes(raw_data.scene);
		meshes.forEach( function (mesh) {
			mesh.material.normalMapType = THREE.ObjectSpaceNormalMap;
			mesh.material.side = THREE.DoubleSide;
		});

		// ADD MESHES LIST
		this.tv_world.model = meshes;

		// ADD TO SCENE
		this.tv_world.scene.add(raw_data.scene);

		// ADD BOUNDING BOX GRID (WITHOUT TRIANGLES ON FACES) 
		this.tv_world.bbox_grid = new THREE.Box3Helper( this.tv_world.bbox_data, new THREE.Color(this.tv_settings.bbox_color) );
		this.tv_world.scene.add( this.tv_world.bbox_grid );
		this.tv_world.bbox_grid.visible = false;

	}	

	_rotateObject(obj) {
		if (this.tv_settings.rotation_x != '') {
			obj.rotateX( this.tv_settings.rotation_x * (Math.PI / 180.) );
		}
		if (this.tv_settings.rotation_y != '') {
			obj.rotateY( this.tv_settings.rotation_y * (Math.PI / 180.) );
		}
		if (this.tv_settings.rotation_z != '') {
			obj.rotateZ( this.tv_settings.rotation_z * (Math.PI / 180.) );
		}
	}

	_centerGroup(group) {
		var center = new THREE.Vector3();
		var box = new THREE.Box3().setFromObject(group);

		center.x = (box.max.x + box.min.x) / 2;
    	center.y = (box.max.y + box.min.y) / 2;
    	center.z = (box.max.z + box.min.z) / 2;

		group.applyMatrix4( 
			new THREE.Matrix4()
			.makeTranslation( 
				-(center.x), 
				-(center.y), 
				-(center.z) 
			) 
		);
	}

	_groupToMeshes(group) {
		var self = this;
		var meshes = [];

		if (group.children !== undefined) {
			group.children.forEach(function(element) {
				if (element.isMesh) {
					if ( element.geometry !== undefined ) {
						meshes.push(element);
					}
					if ( element.children !== undefined && element.children.length > 0 ) {
						meshes.concat(self._groupToMeshes(element.children));
					}
				}
			});
		}
		
		return meshes;
	}

	_groupToBufferGeometry(geometry) {
		var self = this;
		var geometries = [];
		
		geometry.traverse(function(child) {
			if ( child.geometry !== undefined ) {
				geometries.push(child.geometry);
			}
		});
		return geometries;
	}

	_loadModel() {
		var self = this;

		var file_extension = this.tv_settings.tv_file.split('.').pop().toLowerCase();

		var loader = false;
		var draco_loader = false;

		switch( file_extension ) {
			case 'stl':
				loader = this.tv_settings.stl_loader;	
				break;
			case 'fbx':
				loader = this.tv_settings.fbx_loader;	
				break;
			case 'obj':
				loader = this.tv_settings.obj_loader;	
				break;
			case 'glb':
				loader = this.tv_settings.gltf_loader;
				break;
			case 'drc':
				loader = this.tv_settings.draco_loader;
				break;								
			default:
				self.tv_settings.message_bar.style.display = 'block';
				self.tv_settings.message_bar.innerHTML = 'Unable to load model: unknown file type.';
				return false;
		}

		this.tv_settings.progress_bar.style.display = 'block';
		var bar = this.tv_settings.progress_bar.children[0];
		/*bar.style.width = '0px%';*/

		var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
		var normalized_total = false;

		loader.load( this.tv_settings.tv_file, function ( loaded_data ) {
			if (file_extension == 'glb') {
				self._addTexturedModel( loaded_data, file_extension );
			} else {
				self._addModel( loaded_data, file_extension );
			}

			//const axesHelper = new THREE.AxesHelper(100);
			//self.tv_world.scene.add( axesHelper );

			self._resizePlaneGrid();
			self._fitCameraToObject();
			self._calculateVolumeArea();
			self._positionLights();
			//bar.style.width = '0%';
			self.tv_settings.progress_bar.style.display = 'none';
			self.tv_settings.is_object_loaded = true;
			self.resize( self.tv_settings.parent_element.offsetWidth, self._getHeightWidthRatio(self.tv_settings.parent_element.offsetWidth) );	
		
		}, function ( progress ) {
			var progress_percentage = Math.round( (progress.loaded / progress.total) * 100.0 );
			//bar.style.width = progress_percentage + '%';

			var i = 0;
			if ( normalized_total === false ) {
				i = parseInt(Math.floor(Math.log(progress.total) / Math.log(1024)));
				normalized_total = Math.round(progress.total / Math.pow(1024, i), 2) + ' ' + sizes[i];
			}
			i = parseInt(Math.floor(Math.log(progress.loaded) / Math.log(1024)));
			var normalized_loaded = Math.round(progress.loaded / Math.pow(1024, i), 2) + ' ' + sizes[i];

			bar.innerHTML = '&nbsp;' + normalized_loaded + ' / ' + normalized_total;
			self.tv_world.model_filesize = progress.total;
		}, function ( error ) {
			//bar.style.width = '0%';
			self.tv_settings.progress_bar.style.display = 'none';
			self.tv_settings.message_bar.style.display = 'block';
			self.tv_settings.message_bar.innerHTML = 'An error occurred loading model';
			console.log(error);
		});
	}

	_positionLights() {
		let factor = 10.;
		let lightX = this.tv_world.bbox_data.min.x * factor;
		let lightY = this.tv_world.bbox_data.max.y * factor;
		let lightZ = this.tv_world.bbox_data.max.z * factor;
		this.tv_world.spot_light.position.set(lightX, lightY, lightZ);
	}

	_resizePlaneGrid() {
		this.tv_world.plane_size = Math.max(this.tv_world.bbox_data.max.x, this.tv_world.bbox_data.max.y, this.tv_world.bbox_data.max.z) * 10;
		if ( this.tv_settings.plane_wire_display == true ) {
			this.tv_world.plane_wire = new THREE.GridHelper( this.tv_world.plane_size, this.tv_world.plane_division, this.tv_settings.wire_color, this.tv_settings.wire_color );
			this.tv_world.plane_wire.rotateX( Math.PI / 2 );
			this.tv_world.plane_wire.position.z = this.tv_world.bbox_data.min.z - 0.1
			this.tv_world.plane_wire.name = 'planewire';
			this.tv_world.scene.remove(this.tv_world.scene.getObjectByName("planewire"));
			this.tv_world.scene.add(this.tv_world.plane_wire);
			this.tv_world.plane = new THREE.Mesh(new THREE.PlaneGeometry(this.tv_world.plane_size, this.tv_world.plane_size, this.tv_world.plane_size), this.tv_world.plane_material);
			this.tv_world.plane.position.z = this.tv_world.bbox_data.min.z - 0.2;
			this.tv_world.plane.name = "plane";
			//this.tv_world.plane.receiveShadow = true;
			this.tv_world.scene.remove(this.tv_world.scene.getObjectByName("plane"));
			this.tv_world.scene.add(this.tv_world.plane);
		}
	}

	_calculateVolumeArea() {
		function SignedTriangleVolume( v1, v2, v3 ) {
			var v321 = v3.x * v2.y * v1.z;
			var v231 = v2.x * v3.y * v1.z;
			var v312 = v3.x * v1.y * v2.z;
			var v132 = v1.x * v3.y * v2.z;
			var v213 = v2.x * v1.y * v3.z;
			var v123 = v1.x * v2.y * v3.z;
			return ( -v321 + v231 + v312 - v132 - v213 + v123 ) / 6.0;
		}

		function TriangleArea ( v1, v2, v3 ) {
			var v12 = v2.clone().sub(v1);
			var v13 = v3.clone().sub(v1);

			var cross = new THREE.Vector3();
			cross.crossVectors( v12, v13 );
		
			return (cross.length() / 2.0);;
		}

		function geometryVolumeArea(geometry) {
			switch(geometry.type) {
				case 'Geometry':
				//case 'Object3D':
					var vertices = geometry.vertices;
					var triangles_count = 0;
					geometry.faces.forEach( function( triangle ) {
						var currentVol  = SignedTriangleVolume(vertices[triangle.a], vertices[triangle.b], vertices[triangle.c]);
						var currentArea = TriangleArea(vertices[triangle.a], vertices[triangle.b], vertices[triangle.c]);
	
						self.tv_world.model_volume += currentVol;
						self.tv_world.model_area   += currentArea;
						triangles_count++;
					}); 
					self.tv_world.model_triangles += triangles_count;
					break;
				case 'BufferGeometry':
					var vertices = geometry.attributes.position;
					self.tv_world.model_triangles += geometry.attributes.position.count / 3;
	
					for (var i = 0; i < self.tv_world.model_triangles; i++) {
						var a = new THREE.Vector3(vertices.array[ (i * 9)     ], vertices.array[ (i * 9) + 1 ], vertices.array[ (i * 9) + 2 ]);
						var b = new THREE.Vector3(vertices.array[ (i * 9) + 3 ], vertices.array[ (i * 9) + 4 ], vertices.array[ (i * 9) + 5 ]);
						var c = new THREE.Vector3(vertices.array[ (i * 9) + 6 ], vertices.array[ (i * 9) + 7 ], vertices.array[ (i * 9) + 8 ]);
	
						var currentVol  = SignedTriangleVolume(a, b, c);
						var currentArea = TriangleArea(a, b, c);
						
						self.tv_world.model_volume += currentVol;
						self.tv_world.model_area   += currentArea;
					}
					break;
			}
		}

		var self = this;

		this.tv_world.model.forEach(function(child) {
			switch(child.type) {
				case 'Geometry':
				case 'BufferGeometry':
				//case 'Object3D':
					geometryVolumeArea(child);
					break;
				case 'Mesh':
					geometryVolumeArea(child.geometry);
					break;

			}
		});

		this.tv_world.model_triangles = Math.round(this.tv_world.model_triangles);
		
		// VOLUME IN CM3 (NORMALIZED)
		this.tv_world.model_volume = Math.round( (Math.abs(this.tv_world.model_volume) / 1000.0) * 1000) / 1000;

		// AREA IN CM2 (NORMALIZED)
		this.tv_world.model_area = Math.round( (Math.abs(this.tv_world.model_area) / 100.0) * 100) / 100;
	}

	render() {
		if (this.tv_settings.is_visible) {

			if (this.tv_settings.is_rotation_enabled) {
				this.tv_world.controls.autoRotate = true;
			} else {
				this.tv_world.controls.autoRotate = false;
			}

			this.tv_world.controls.update();

			//this.tv_world.renderer.preserveDrawingBuffer = true;

			/* TODO OBJECTS REFLECTIONS ON PLANE
			this.tv_world.reflect_camera.position.z =- this.tv_world.camera.position.z;
			this.tv_world.reflect_camera.position.y = this.tv_world.camera.position.y;
			this.tv_world.reflect_camera.position.x = this.tv_world.camera.position.x;
			
			this.tv_world.scene.traverse( function(object) { 
				if(object.name == 'plane' || object.name == 'planewire')
					object.visible = false;
			});
			this.tv_world.reflect_camera.updateCubeMap( this.tv_world.renderer, this.tv_world.scene );
			this.tv_world.scene.traverse( function(object) {
				if(object.name == 'plane' || object.name == 'planewire')
					object.visible = true;
			});
			*/

			//this.tv_world.renderer.outputEncoding = THREE.sRGBEncoding;
			
			this.tv_world.renderer.render( this.tv_world.scene, this.tv_world.camera );
		}
	}

	toggleThingviewer(check_preview = false) {
		if (check_preview) {
			this.tv_settings.is_visible = (this.tv_settings.tv_preview_url == '' ? false : true);
		}

		if (this.tv_settings.tv_preview_url == '') {
			this._setDisplay('thingviewer_button', false);
		} else {
			this._setDisplay('thingviewer_button', true);
		}

		if (this.tv_settings.is_visible == false) {
			this.tv_settings.container.style.display = 'block';

			this._setDisplay('fullscreen_button', true);
			this._setDisplay('info_button', true);
			this._setDisplay('help_button', true);
			this._setDisplay('download_button', true);
			this._setDisplay('bbox_button', true);
			this._setDisplay('rotation_button', true);
			this._setDisplay('model_preview', false);

			this.tv_settings.is_visible = true;
		} else {
			this.tv_settings.container.style.display = 'none';

			this._setDisplay('fullscreen_button', false);
			this._setDisplay('info_button', false);
			this._setDisplay('help_button', false);
			this._setDisplay('download_button', false);
			this._setDisplay('bbox_button', false);
			this._setDisplay('rotation_button', false);
			this._setDisplay('model_preview', true);

			this.tv_settings.info_button.children[0].className     = '';
			this.tv_settings.help_button.children[0].className     = '';
			this.tv_settings.rotation_button.children[0].className = '';
			this.tv_settings.info_window.style.display             = 'none';
			this.tv_settings.help_window.style.display             = 'none';
			this.tv_settings.is_visible                            = false;
			this.tv_settings.is_rotation_enabled                   = false;
		}
	}

	isFullscreen() {
		return this.tv_settings.is_fullscreen;
	}

	isThingviewerEnabled() {
		return this.tv_settings.is_visible;
	}

	isRotationEnabled() {
		return this.tv_settings.is_rotation_enabled;
	}
	_getInfoPopupTemplate() {
		return this.tv_settings.info_template;
	}

	_getHeightWidthRatio( width ) {
		return (width * 9.0) / 16.0;
	}

	_addEventListeners() {

		// Javascript sprintf equilvalent
		// source: http://stackoverflow.com/questions/610406/javascript-equivalent-to-printf-string-format
		// use: var result = "{0} and {1}".format("A", "B") 
		// produce "A and B"
		String.prototype.format = function() {
			var formatted = this;
			for (var i = 0; i < arguments.length; i++) {
				var regexp = new RegExp("\\{" + i + "\\}", 'gi');
				formatted = formatted.replace(regexp, arguments[i]);
			}
			return formatted;
		}

		var self = this;

		this.tv_settings.fullscreen_button.addEventListener('click', function (event) {
			self.toggleFullscreen();
			event.preventDefault();
		}, false);

		this.tv_settings.info_button.addEventListener('click', function (event) {
			if (self.tv_settings.is_info_visible) {
				self.tv_settings.info_window.style.display = 'none';
				self.tv_settings.is_info_visible           = false;
				this.children[0].className                 = '';
			} else {
				self.tv_settings.info_window.style.display         = 'inline-block';
				self.tv_settings.is_info_visible                   = true;
				self.tv_settings.help_window.style.display         = 'none';
				self.tv_settings.help_button.children[0].className = '';
				self.tv_settings.is_help_visible                   = false;
				this.children[0].className                         = 'selected';

				var bbox = new THREE.Vector3();
				self.tv_world.bbox_data.getSize(bbox);

				var bbox_x = Math.round(bbox.x * 10) / 100;
				var bbox_y = Math.round(bbox.y * 10) / 100;
				var bbox_z = Math.round(bbox.z * 10) / 100;

				var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
				var i = parseInt(Math.floor(Math.log(self.tv_world.model_filesize) / Math.log(1024)));
				var normalized_file_size = Math.round(self.tv_world.model_filesize / Math.pow(1024, i), 2) + ' ' + sizes[i];

				var file_name = self.tv_settings.parent_element.getAttribute('data-model').split("/");

				self.tv_settings.info_window.innerHTML = self._getInfoPopupTemplate().format(file_name[file_name.length - 1], normalized_file_size, self.tv_world.model_triangles, self.tv_world.model_area, self.tv_world.model_volume, bbox_x, bbox_y, bbox_z);
			}
			event.preventDefault();
		}, false);

		this.tv_settings.thingviewer_button.addEventListener('click', function (event) {
			self.toggleThingviewer();
			event.preventDefault();
		}, false);
		
		this.tv_settings.help_button.addEventListener('click', function (event) {
			if (self.tv_settings.is_help_visible) {
				self.tv_settings.help_window.style.display = 'none';
				self.tv_settings.is_help_visible           = false;
				this.children[0].className                 = '';
			} else {
				self.tv_settings.help_window.style.display         = 'inline-block';
				self.tv_settings.is_help_visible                   = true;
				self.tv_settings.info_window.style.display         = 'none';
				self.tv_settings.is_info_visible                   = false;
				this.children[0].className                         = 'selected';
				self.tv_settings.info_button.children[0].className = '';
			}
			event.preventDefault();
		}, false);

		this.tv_settings.bbox_button.addEventListener('click', function (event) {
			if (self.tv_settings.is_bbox_visible) {
				self.tv_world.bbox_grid.visible = false;
				self.tv_settings.is_bbox_visible = false;
				this.children[0].className = '';
			} else {
				self.tv_world.bbox_grid.visible = true;
				self.tv_settings.is_bbox_visible = true;
				this.children[0].className = 'selected';
			}
			event.preventDefault();
		}, false);

		this.tv_settings.rotation_button.addEventListener('click', function (event) {
			if (self.tv_settings.is_rotation_enabled) {
				self.tv_settings.is_rotation_enabled = false;
				this.children[0].className = '';
			} else {
				self.tv_settings.is_rotation_enabled = true;
				this.children[0].className = 'selected';
			}
			event.preventDefault();
		}, false);

		window.addEventListener('keydown', function (event) {
			//event.preventDefault();
			if ( event.keyCode == 88 ) { // X (DEACTIVATE FULLSCREEN MODE)
				if ( self.tv_settings.is_fullscreen == true ) {
					self.toggleFullscreen();
					self.resize( self.tv_settings.parent_element.offsetWidth, self._getHeightWidthRatio(self.tv_settings.parent_element.offsetWidth) );	
				}
			} else {
				return;
			}
			
		}, false);

		window.addEventListener('resize', function (event) {
			if (self.tv_settings.is_fullscreen == true) {
				self.resize( window.innerWidth, window.innerHeight );
			} else {
				self.resize( self.tv_settings.parent_element.offsetWidth, self._getHeightWidthRatio(self.tv_settings.parent_element.offsetWidth) );
			}
		}, false);
	}
}