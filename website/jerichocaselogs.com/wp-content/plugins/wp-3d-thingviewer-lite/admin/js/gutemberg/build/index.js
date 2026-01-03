/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*****************************************!*\
  !*** ./admin/js/gutemberg/src/index.js ***!
  \*****************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);


/**
 * Thingviewer Gutenberg block
 */
const {
  registerBlockType
} = wp.blocks;
const {
  RawHTML,
  userState
} = wp.element;
const {
  InspectorControls,
  MediaUpload
} = wp.editor;
const {
  PanelBody,
  Button,
  RangeControl,
  ToggleControl
} = wp.components;
registerBlockType('wp3d-thingviewer-lite/gutemberg-block', {
  title: 'WP 3D Thingviewer Lite',
  icon: 'embed-generic',
  description: 'Embed a WP 3D Thingviewer into the content',
  category: 'embed',
  example: {},
  attributes: {
    model_url: {
      type: 'string',
      default: ''
    },
    model_url_bck: {
      type: 'string',
      default: ''
    },
    model_id: {
      type: 'number',
      default: ''
    },
    model_id_bck: {
      type: 'number',
      default: ''
    },
    model_preview: {
      type: 'string',
      default: ''
    },
    rotation_x: {
      type: 'number',
      default: 0
    },
    rotation_y: {
      type: 'number',
      default: 0
    },
    rotation_z: {
      type: 'number',
      default: 0
    },
    link_cloaking: {
      type: 'boolean',
      default: false
    }
  },
  edit: _ref => {
    let {
      attributes,
      setAttributes
    } = _ref;
    const {
      model_url,
      model_url_bck,
      model_id,
      model_id_bck,
      model_preview,
      rotation_x,
      rotation_y,
      rotation_z,
      link_cloaking
    } = attributes;
    const ALLOWED_MEDIA_TYPES = ['application/sla', 'application/octet-stream', 'application/octet-stream', 'text/plain'];
    var shortcode = ['[wp-3dtvl model_id="' + attributes.model_id + '"', 'model_file="' + attributes.model_url + '"', 'model_preview="' + attributes.model_preview + '"', 'rotation_x="' + attributes.rotation_x + '"', 'rotation_y="' + attributes.rotation_y + '"', 'rotation_z="' + attributes.rotation_z + '"][/wp-3dtvl]'].join(' ');

    function onRotationXChange(new_value) {
      setAttributes({
        rotation_x: new_value
      });
    }

    function onRotationYChange(new_value) {
      setAttributes({
        rotation_y: new_value
      });
    }

    function onRotationZChange(new_value) {
      setAttributes({
        rotation_z: new_value
      });
    }

    function onSelectModel(new_media) {
      if (new_media && new_media.url && new_media.id) {
        setAttributes({
          model_id_bck: new_media.id
        });
        setAttributes({
          model_url_bck: new_media.url
        });

        if ({
          link_cloaking
        } == true) {
          setAttributes({
            model_id: new_media.id
          });
          setAttributes({
            model_url: ''
          });
        } else {
          setAttributes({
            model_id: ''
          });
          setAttributes({
            model_url: new_media.url
          });
        }
      }
    }

    function onSelectPreview(new_media) {
      if (new_media && new_media.sizes) {
        setAttributes({
          model_preview: new_media.sizes.full.url
        });
      }
    }

    function onLinkCloakingSwitch(new_value) {
      setAttributes({
        link_cloaking: new_value
      });

      if (new_value == true) {
        if ({
          model_id_bck
        } != '') {
          setAttributes({
            model_id: model_id_bck
          });
          setAttributes({
            model_url: ''
          });
        }
      } else {
        if ({
          model_url_bck
        } != '') {
          setAttributes({
            model_id: ''
          });
          setAttributes({
            model_url: model_url_bck
          });
        }
      }
    }

    return [(0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(InspectorControls, {
      style: {
        marginBottom: '40px'
      }
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: "Model File"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
      type: "text",
      value: model_url_bck,
      style: {
        marginBottom: '10px'
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MediaUpload, {
      style: {
        marginBottom: '20px',
        marginTop: '10px'
      },
      onSelect: onSelectModel,
      allowedTypes: ALLOWED_MEDIA_TYPES,
      value: model_url_bck,
      render: _ref2 => {
        let {
          open
        } = _ref2;
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
          onClick: open,
          variant: "primary"
        }, "Select 3D Model");
      }
    }), ",", (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(ToggleControl, {
      style: {
        marginBottom: '10px',
        marginTop: '10px'
      },
      label: "Link Cloaking",
      help: link_cloaking ? "The model file url is invisible and will be loaded using the Wordpress REST Api." : "The model file url is visible.",
      checked: link_cloaking,
      onChange: onLinkCloakingSwitch
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: 'Preview Image'
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("input", {
      type: "text",
      value: model_preview,
      style: {
        marginBottom: '10px'
      }
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(MediaUpload, {
      style: {
        marginBottom: '10px',
        marginTop: '10px'
      },
      onSelect: onSelectPreview,
      allowedTypes: ['image'],
      value: model_preview,
      render: _ref3 => {
        let {
          open
        } = _ref3;
        return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(Button, {
          onClick: open,
          variant: "primary"
        }, "Select Preview Image");
      }
    })), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(PanelBody, {
      title: "Model Settings"
    }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RangeControl, {
      label: "Rotation X",
      value: rotation_x,
      onChange: onRotationXChange,
      min: 0,
      max: 360,
      step: 1
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RangeControl, {
      label: "Rotation Y",
      value: rotation_y,
      onChange: onRotationYChange,
      min: 0,
      max: 360,
      step: 1
    }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RangeControl, {
      label: "Rotation Z",
      value: rotation_z,
      onChange: onRotationZChange,
      min: 0,
      max: 360,
      step: 1
    }))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RawHTML, null, shortcode)];
  },
  save: _ref4 => {
    let {
      attributes
    } = _ref4;
    var shortcode = ['[wp-3dtvl model_id="' + attributes.model_id + '"', 'model_file="' + attributes.model_url + '"', 'model_preview="' + attributes.model_preview + '"', 'rotation_x="' + attributes.rotation_x + '"', 'rotation_y="' + attributes.rotation_y + '"', 'rotation_z="' + attributes.rotation_z + '"][/wp-3dtvl]'].join(' ');
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(RawHTML, null, shortcode);
  }
});
})();

/******/ })()
;
//# sourceMappingURL=index.js.map