<?php

namespace BP3D\Base;

class ExtendMimeType
{

    function register()
    {
        global $wp_version;
        add_filter('upload_mimes', [$this, 'bplugins_stp_mime_types']);

        if (version_compare($wp_version, '5.1') >= 0) {
            add_filter('wp_check_filetype_and_ext', [$this, 'bplugins_stp_add_allow_upload_extension_exception'], 10, 5);
        } else {
            add_filter('wp_check_filetype_and_ext', [$this, 'bplugins_stp_add_allow_upload_extension_exception'], 10, 4);
        }
    }

    function bplugins_stp_mime_types($mimes)
    {
        return wp_parse_args($mimes, $this->getMimeTypes());
    }

    function bplugins_stp_add_allow_upload_extension_exception($data, $file, $filename, $mimes, $real_mime = null)
    {
        // If file extension is 2 or more 
        $f_sp = explode(".", $filename);
        $f_exp_count  = count($f_sp);

        if ($f_exp_count <= 1) {
            return $data;
        } else {
            $f_name = $f_sp[0];
            $ext  = $f_sp[$f_exp_count - 1];
        }

        $extendedMimes = $this->getMimeTypes();

        if (isset($extendedMimes[$ext])) {
            $type = $extendedMimes[$ext];
            $proper_filename = '';
            return compact('ext', 'type', 'proper_filename');
        }
        return $data;
    }

    function getMimeTypes()
    {
        $mimes = [
            'glb' => 'model/gltf-binary',
            'gltf' => 'model/gltf-binary',
            'obj' => 'model/obj',
            '3ds' => 'application/x-3ds',
            'step' => 'application/step',
            'stl' => 'application/vnd.ms-pki.stl',
            'fbx' => 'application/octet-stream',
            '3dml' => 'text/vnd.in3d.3dml',
            'dae' => 'application/collada+xml',
            'wrl' => 'model/vrml',
            '3mf' => 'application/vnd.ms-3mfdocument',
            'mtl' => 'model/mtl',
            'bin' => 'application/octet-stream',
            'hdr' => 'image/vnd.radiance',
            'usdz' => 'model/vnd.pixar.usd',
        ];

        return $mimes;
    }
}

// add_action('wp_footer', function(){
//     echo '<pre>';
//     echo "mimes";
//     print_r( get_option( 'my_mimes' ) );
//     echo '</pre>';
// });


add_action('admin_footer', function () {
?>
    <style>
        .csf-metabox .ui-icon,
        .csf-metabox .ui-state-hover .ui-icon {
            background-image: none !important
        }
    </style>
<?php
});
