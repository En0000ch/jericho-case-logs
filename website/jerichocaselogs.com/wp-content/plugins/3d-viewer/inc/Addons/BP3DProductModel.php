<?php

namespace BP3D\Addons;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class BP3DProductModel extends \Elementor\Widget_Base
{

	public function get_name()
	{
		return 'BP3DProductModel';
	}

	public function get_title()
	{
		return esc_html__('Product Model', 'model-viewer');
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
		return ['3d embed', '3d viewer', 'model viewer', 'product model'];
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

		// $this->add_control('late_initialize', [
		// 	'label' => esc_html__( 'Initialize when change product variation', 'swatches-enabled' ),
		// 	'type' => \Elementor\Controls_Manager::SWITCHER,
		// 	'label_on' => esc_html__( 'Yes', 'textdomain' ),
		// 	'label_off' => esc_html__( 'No', 'textdomain' ),
		// 	'return_value' => true,
		// 	'default' => false,
		// ]);


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

		$this->end_controls_section();
		// Style Tab End

	}

	protected function render()
	{

		$settings = $this->get_settings_for_display();

		echo do_shortcode('[3d_viewer_product]');
	}
}
