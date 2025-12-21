<?php

namespace BP3D\Addons;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class ModelViewer extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return '3dModelViewer';
	}

	public function get_title()
	{
		return esc_html__('Model Viewer', 'model-viewer');
	}

	public function get_icon()
	{
		return 'eicon-preview-medium';
	}

	public function get_categories()
	{
		return ['general'];
	}

	public function get_keywords()
	{
		return ['3d embed', '3d viewer', 'model viewer'];
	}

	public function get_script_depends()
	{
		return ['bp3d-public'];
	}

	// /**
	//  * Style
	//  */
	public function get_style_depends()
	{
		return [];
	}

	protected function register_controls()
	{

		// Content Tab Start
		$this->start_controls_section(
			'embedder',
			[
				'label' => esc_html__('Model Viewer', 'model-viewer'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'currentViewer',
			[
				'label' => esc_html__('Viewer', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'modelViewer',
				'options' => [
					'modelViewer' => __("Lite", 'model-viewer'),
					'O3DViewer' => __("Advanced", 'model-viewer'),
				],
				'default' => 'modelViewer',
			]
		);

		$this->add_control(
			'multiple',
			[
				'label' => esc_html__('Use Multiple Model?', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => false,
			]
		);

		// single model input field start
		$this->add_control(
			'modelUrl',
			[
				'label' 		=> esc_html__('Select Model', 'model-viewer'),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'before',
				'placeholder' => esc_html__("Paste Model URL", 'model-viewer'),
				'condition' => array(
					'multiple!' => 'yes'
				)
			]
		);

		$this->add_control(
			'useDecoder',
			[
				'label' => esc_html__('Use Decoder', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'  => esc_html__('None', 'model-viewer'),
					'draco'  => esc_html__('Draco', 'model-viewer'),
				],
				'condition' => array(
					'multiple!' => 'yes',
					'currentViewer' => 'modelViewer',
				)
			]
		);

		$this->add_control(
			'bin_file',
			[
				'label' 		=> esc_html__('Upload bin file', 'model-viewer'),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'before',
				'placeholder' => esc_html__("Paste bin file URL", 'model-viewer'),
				'condition' => array(
					'decoder' => 'draco',
					'multiple!' => 'yes',
					'currentViewer' => 'modelViewer',
				)
			]
		);

		$this->add_control(
			'poster',
			[
				'label' 		=> esc_html__('Select Poster', 'model-viewer'),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'after',
				'placeholder' => esc_html__("Paste Poster URL", 'model-viewer'),
				'condition' => array(
					'multiple!' => 'yes',
					'currentViewer' => 'modelViewer',
				)
			]
		);
		//single model input field end

		//multiple model input field start
		$repeater = new \Elementor\Repeater();
		$repeater->add_control(
			'modelUrl',
			[
				'label' 		=> esc_html__('Select Model', 'model-viewer'),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'before',
				'placeholder' => esc_html__("Paste Model URL", 'model-viewer'),
			]
		);

		$repeater->add_control(
			'useDecoder',
			[
				'label' => esc_html__('Use Decoder', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none'  => esc_html__('None', 'model-viewer'),
					'draco'  => esc_html__('Draco', 'model-viewer'),
				],
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		$repeater->add_control(
			'bin_file',
			[
				'label' 		=> esc_html__('Upload bin file', 'model-viewer'),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'before',
				'placeholder' => esc_html__("Paste bin file URL", 'model-viewer'),
				'condition' => array(
					'decoder' => 'draco',
					'currentViewer' => 'modelViewer',
				)
			]
		);

		$repeater->add_control(
			'poster',
			[
				'label' 		=> esc_html__('Select Poster', 'model-viewer'),
				'type' 			=> 'b-select-file',
				'separator' 	=> 'after',
				'placeholder' => esc_html__("Paste Poster URL", 'model-viewer'),
				'condition' => array(
					'currentViewer' => 'modelViewer',
				)
			]
		);

		$this->add_control(
			'models',
			[
				'label' => esc_html__('Models', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'condition' => array(
					'multiple' => 'yes'
				),
				'default' => [
					[
						'modelUrl' => '',
						'poster' => ''
					]
				]
			]
		);
		//multiple model input field end


		//custom angle start
		$this->add_control(
			'hr',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
			]
		);

		$this->add_control(
			'rotate',
			[
				'label' => esc_html__('Rotate', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		$this->add_control(
			'rotateAlongX',
			[
				'label' => esc_html__('Rotate Along X (degree)', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 360,
						'step' => 1,
					],
				],
				'condition' => array(
					'multiple!' => 'yes',
					'rotate' => 'yes',
					'currentViewer' => 'modelViewer'
				)
			]
		);

		$this->add_control(
			'rotateAlongY',
			[
				'label' => esc_html__('Rotate Along Y (degree)', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 360,
						'step' => 1,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => '75'
				],
				'condition' => array(
					'multiple!' => 'yes',
					'rotate' => 'yes',
					'currentViewer' => 'modelViewer'
				)
			]
		);

		$this->add_control(
			'hr_after_angle',
			[
				'type' => \Elementor\Controls_Manager::DIVIDER,
				'condition' => array(
					'multiple!' => 'yes',
					'rotate' => 'yes'
				)
			]
		);
		//custom angle end

		//disable full screen
		$this->add_control(
			'fullscreen',
			[
				'label' => esc_html__('Fullscreen Button', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		//camera controls
		$this->add_control(
			'mouseControls',
			[
				'label' => esc_html__('Mouse Control', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Enable', 'model-viewer'),
				'label_off' => esc_html__('Disable', 'model-viewer'),
				'return_value' => 'yes',
				'default' => 'yes',
			]
		);

		//lazy load
		$this->add_control(
			'lazy_load',
			[
				'label' => esc_html__('Lazy Load', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Enable', 'model-viewer'),
				'label_off' => esc_html__('Disable', 'model-viewer'),
				'return_value' => 'yes',
				'default' => false,
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		//shadow
		$this->add_control(
			'shadow',
			[
				'label' => esc_html__('Enable Shadow', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => esc_html__('Enable', 'model-viewer'),
				'label_off' => esc_html__('Disable', 'model-viewer'),
				'return_value' => 'yes',
				'default' => false,
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		//autoplay
		$this->add_control(
			'autoplay',
			[
				'label' => esc_html__('Autoplay ( if animated )', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => false,
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		//variant
		$this->add_control(
			'variant',
			[
				'label' => esc_html__('Enable Variant Selector', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => false,
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		$this->add_control(
			'enableAnimationSelector',
			[
				'label' => esc_html__('Enable Animation Selector', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => false,
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		$this->add_control(
			'loadingPercentage',
			[
				'label' => esc_html__('show loading percentage', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => false,
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);


		$this->add_control(
			'progressBar',
			[
				'label' => esc_html__('show progressbar', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default' => 'yes',
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		$this->add_control(
			'exposure',
			[
				'label' => esc_html__('Exposure', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px'],
				'range' => [
					'px' => [
						'min' => 0.1,
						'max' => 10,
						'step' => 0.1,
					],
				],
				'default' => [
					'unit'  => 'px',
					'size' => 1
				],
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		$this->end_controls_section();


		// Style Tab Start
		$this->start_controls_section(
			'model',
			[
				'label' => esc_html__('Model', 'model-viewer'),
				'tab' => \Elementor\Controls_Manager::TAB_STYLE,
			]
		);

		//width
		$this->add_control(
			'width',
			[
				'label' => esc_html__('Width', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', '%', 'vw'],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 1000,
						'step' => 5,
					],
					'%' => [
						'min' => 20,
						'max' => 100,
					],
					'vw' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => '%',
					'size' => 100,
				],
				'selectors' => [
					'{{WRAPPER}} .b3dviewer model-viewer' => 'width: {{SIZE}}{{UNIT}};margin:0 auto;max-width:100%;',
				],
			]
		);

		//height
		$this->add_control(
			'height',
			[
				'label' => esc_html__('Height', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::SLIDER,
				'size_units' => ['px', 'vh'],
				'range' => [
					'px' => [
						'min' => 200,
						'max' => 1000,
						'step' => 5,
					],
					'vh' => [
						'min' => 5,
						'max' => 100,
					],
				],
				'default' => [
					'unit' => 'px',
					'size' => 500,
				],
				'selectors' => [
					'{{WRAPPER}} .b3dviewer model-viewer' => 'height: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .b3dviewer model-viewer #lazy-load-poster img' => 'height: {{SIZE}}{{UNIT}};',
				],
			]
		);

		//background color
		$this->add_control(
			'backgroundColor',
			[
				'label' => esc_html__('Background Color', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .b3dviewer model-viewer' => 'background: {{VALUE}}',
				],
			]
		);

		//background image
		$this->add_control(
			'backgroundImage',
			[
				'label' => esc_html__('Choose Background Image', 'model-viewer'),
				'type' => \Elementor\Controls_Manager::MEDIA,
				'dynamic' => [
					'active' => true,
				],
				'selectors' => [
					'{{WRAPPER}} .b3dviewer model-viewer' => 'background-image: url({{URL}});background-repeat: no-repeat; background-size: cover',
				],
				'condition' => [
					'currentViewer' => 'modelViewer'
				]
			]
		);

		$this->end_controls_section();
		// Style Tab End

	}

	public function bp3d_get_settings()
	{
		$settings = $this->get_settings_for_display();
		return function ($key, $default = false, $is_boolean = false, $key2 = null) use ($settings) {
			if (isset($settings[$key]) && isset($settings[$key][$key2])) {
				if ($is_boolean) {
					return $settings[$key][$key2] === 'yes';
				}
				return $settings[$key][$key2];
			}
			if (isset($settings[$key])) {
				if ($is_boolean) {
					return $settings[$key] === 'yes';
				}
				return $settings[$key];
			}
			return $default;
		};
	}

	protected function render()
	{

		$settings = $this->get_settings_for_display();
		$get_settings = $this->bp3d_get_settings();


		$finalData = [
			"align" => 'center',
			'uniqueId' => "b3dviewer" . uniqid(),
			"multiple" => $settings['multiple'] === 'yes',
			"O3DVSettings" =>  [
				"isFullscreen" =>  true,
				"isPagination" =>  false,
				"isNavigation" =>  false,
				"camera" =>  null,
				"mouseControl" =>  true
			],
			'model' => [
				'modelUrl' => $settings['modelUrl'],
				'poster' => $settings['poster'],
				'useDecoder' => $settings['useDecoder']
			],
			"currentViewer" => $settings['currentViewer'],
			'models' => $get_settings('models', []),
			"lazyLoad" => $settings['lazy_load'] == 'yes', // done
			"autoplay" => (bool) $settings['autoplay'], // done
			"shadow" =>  $settings['shadow'] == 'yes', //done
			"autoRotate" => true, // done
			"zoom" => true,
			"isPagination" => false,
			"isNavigation" => false,
			"preload" => 'auto', //$options['bp_3d_preloader'] == '1' ? 'auto' : 'interaction',
			'rotationPerSecond' => '30', // done
			"mouseControl" =>  $settings['mouseControls'] == 'yes',
			"fullscreen" =>   $settings['fullscreen'] == 'yes', // done
			"variant" => (bool) $settings['variant'],
			"loadingPercentage" =>  (bool) $settings['loadingPercentage'], //$options['bp_model_progress_percent'] == '1',
			"progressBar" =>   $settings['progressBar'] == 'yes', //$options['bp_3d_progressbar'] == '1',
			"rotate" =>  $settings['rotate'] == 'yes', //$options['bp_model_angle'] === '1',
			"rotateAlongX" => isset($settings['rotateAlongX']['size']) ? $settings['rotateAlongX']['size'] : 0, //$options['angle_property']['top'],
			"rotateAlongY" => isset($settings['rotateAlongY']['size']) ? $settings['rotateAlongY']['size'] : 75, //$options['angle_property']['right'],
			"exposure" => isset($settings['exposure']['size']) ? $settings['exposure']['size'] : 1, //$options['3d_exposure'],
			"styles" => [
				"width" => '100%', //$options['bp_3d_width']['width'].$options['bp_3d_width']['unit'],
				// "height" =>  isset($settings['height']) ? $settings['height']['size'] . $settings['height']['unit'] : '500px', //$options['bp_3d_height']['height'].$options['bp_3d_height']['unit'],
				"height" =>  $get_settings('height', '500', false, 'size') . $get_settings('height', 'px', false, 'unit'),
				"bgColor" => $settings['backgroundColor'] ?? 'transparent', // done
				"bgImage" => $settings['backgroundImage']['url'] ?? '',
				"progressBarColor" => '#666', //$options['bp_model_progressbar_color'] ?? ''
			],
			"stylesheet" => null,
			"additional" => [
				"ID" => "",
				"Class" => "",
				"CSS" => '', //$options['css'] ?? '',
			],
			"animation" => (bool) $settings['enableAnimationSelector'],
			"selectedAnimation" => ""
		];

		if ($finalData['currentViewer'] === 'O3DViewer') {
			wp_enqueue_script('bp3d-o3dviewer');
		} else {
			wp_enqueue_script('bp3d-model-viewer');
		}

?>

		<div class="modelViewerBlock elementor" data-attributes='<?php echo esc_attr(wp_json_encode($finalData)) ?>'></div>

<?php


		if (is_admin()) {
			// wp_enqueue_script('bp3d-model-viewer');
			wp_enqueue_script('bp3d-o3dviewer');
		}

		wp_enqueue_script('bp3d-public');
		wp_enqueue_style('bp3d-custom-style');
		// wp_enqueue_style('bp3d-public');


		// echo \BP3D\Template\ModelViewer::html($data);

	}
}
