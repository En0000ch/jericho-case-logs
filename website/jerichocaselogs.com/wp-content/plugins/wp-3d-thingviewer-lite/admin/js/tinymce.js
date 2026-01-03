(function( $ ) {
    'use strict';

    tinymce.PluginManager.add('wp3dtv_shortcode_mce_button', function(editor, url) {
        editor.addButton('wp3dtv_shortcode_mce_button', {
            icon: 'dashicon dashicons-video-alt3',
            tooltip: 'WP 3D Thingviewer Shortcode',
            onclick: function() {
                var win = editor.windowManager.open({
                    title: 'WP 3D Thingviewer Shortcode',
                    body: [
                    /*{
                        type  : 'textbox',
                        name  : 'color1_hex',
                        id    : 'color1_hex',
                        label : 'Color 1',
                        value : '#ff0000',
                        onkeypress: function() {
                            console.log(this.value());
                            win.find('#color1_picker').value(this.value());
                        },
                    }, {
                        type  : 'colorpicker',
                        name  : 'color1_picker',
                        id    : 'color1_picker',
                        label : 'Pick a color',
                        value : '#ff0000',
                        onchange: function() {
                            console.log(this.rgb());
                            win.find('#color1_hex').value(this.value());
                        },                    
                    }, 
                    */{
                        type  : 'textbox',
                        name  : 'model_url',
                        id    : 'model_url',
                        label : '3D Model file URL',
                        value : '',
                    }, {
                        type  : 'textbox',
                        name  : 'model_id',
                        id    : 'model_id',
                        label : '3D Model media ID',
                        value : '',
                    }, {
                        type  : 'checkbox',
                        name  : 'link_cloaking',
                        id    : 'link_cloaking',
                        label : 'Enable link cloaking?',
                    }, {
                        type   : 'button',
                        name   : 'model_wpmedia',
                        label  : '',
                        text   : 'Select 3D Model',
                        onclick: function() {
                            if ( wp && wp.media && wp.media.editor ) {
                                wp.media.editor.send.attachment = function( a, media ) {
                                    win.find('#model_url').value( media.url );
                                    win.find('#model_id').value( media.id );
                                };
                                wp.media.editor.open( editor.id, {
                                    title : 'Select 3D Model',
                                    multiple : false,
                                    library : {
                                        type: [ 'application/sla', 'text/plain', 'application/octet-stream' ]
                                    },
                                    button : {
                                        text : 'Select'
                                    },
                                });
                            }
                        },
                    }, {
                        type  : 'textbox',
                        name  : 'rotation_x',
                        id    : 'rotation_x',
                        label : 'Rotate object on X axis (deg)',
                        value : '',
                    }, {
                        type  : 'textbox',
                        name  : 'rotation_y',
                        id    : 'rotation_y',
                        label : 'Rotate object on Y axis (deg)',
                        value : '',
                    }, {
                        type  : 'textbox',
                        name  : 'rotation_z',
                        id    : 'rotation_z',
                        label : 'Rotate object on Z axis (deg)',
                        value : '',                                                
                    /*
                    }, {
                        type: 'checkbox',
                        name: 'is_enabled',
                        label: 'Is thingviewer enabled at page load?',
                        text: 'Yes',
                        classes: 'checkclass'
                    */
                    }],
                    onsubmit: function(e) {
                        if (e.data.model_url === '' && e.data.model_id === '') {
                            var window_id = this._id;
                            var inputs = jQuery('#' + window_id + '-body').find('.mce-formitem input');
                            jQuery(inputs.get(0)).css('border-color', 'red');
                            editor.windowManager.alert('You must select a valid model file from Wordpress media');
                            return false;
                        }
                        var content = '[wp-3dtv';
                        if (e.data.link_cloaking && e.data.model_id != '') {
                            content = content + ' model_id=&quot;' + e.data.model_id + '&quot;';
                        } else {
                            content = content + ' model_file=&quot;' + e.data.model_url + '&quot;';
                        }
                        if (e.data.rotation_x != '') {
                            content = content + ' rotation_x=&quot;' + e.data.rotation_x + '&quot;';
                        }
                        if (e.data.rotation_y != '') {
                            content = content + ' rotation_y=&quot;' + e.data.rotation_y + '&quot;';
                        }
                        if (e.data.rotation_z != '') {
                            content = content + ' rotation_z=&quot;' + e.data.rotation_z + '&quot;';
                        }

                        /*content = content + ' is_enabled=&quot;' + e.data.is_enabled + '&quot;';*/
                        /*content = content + ' viewer_size=&quot;' + e.data.viewer_size + '&quot;';*/
                        content = content + ']';
                        /*
                        if (e.data.model_wpmedia !== '') {
                            content = content + e.data.placeholder_text;
                        }
                        */
                        content = content + '[/wp-3dtv]';

                        editor.insertContent(content);
                    }
                });
            }
        });
    });
})( jQuery );