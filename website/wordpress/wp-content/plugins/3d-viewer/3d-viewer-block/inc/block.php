<?php

if(!class_exists('TDViewerBlock')){
	class TDViewerBlock{
		public function __construct(){
			add_action( 'enqueue_block_assets', [$this, 'enqueueBlockAssets'] );
			add_action( 'init', [$this, 'onInit'] );
		}
	
		function enqueueBlockAssets(){
			wp_register_style( 'tdvb-td-viewer-style', plugins_url( 'dist/style.css', __DIR__ ), [], BP3D_VERSION ); // Style
		}
	
		function onInit(){
			wp_register_style( 'tdvb-td-viewer-editor-style', plugins_url( 'dist/editor.css', __DIR__ ), [ 'tdvb-td-viewer-style' ], BP3D_VERSION ); // Backend Style
	
			register_block_type( __DIR__, [
				'editor_style'		=> 'tdvb-td-viewer-editor-style',
				'render_callback'	=> [$this, 'render']
			] ); // Register Block
	
			wp_set_script_translations( 'tdvb-td-viewer-editor-script', 'model-viewer', plugin_dir_path( __FILE__ ) . 'languages' ); // Translate
	
			wp_localize_script( 'tdvb-td-viewer-editor-script', 'bp3dBlock', [
				'nonce' => wp_create_nonce( 'wp_ajax' ),
				'ajaxURL' => admin_url( 'admin-ajax.php' )
			] );
		}
	
		function render( $attributes ){
			extract( $attributes );
	
			wp_enqueue_style( 'tdvb-td-viewer-style' );
			wp_enqueue_script( 'tdvb-td-viewer-script', plugins_url( 'dist/script.js', __DIR__ ), [ 'react', 'react-dom', 'bp3d-model-viewer' ], BP3D_VERSION, true );
	
			$className = $className ?? '';
			$blockClassName = "wp-block-tdvb-td-viewer $className align$align";
	
			ob_start(); ?>
			<div class='<?php echo esc_attr( $blockClassName ); ?>' id='tdvb3DViewerBlock-<?php echo esc_attr( $cId ) ?>' data-attributes='<?php echo esc_attr( wp_json_encode( $attributes ) ); ?>'></div>
	
			<?php return ob_get_clean();
		}
	}
	new TDViewerBlock();
}