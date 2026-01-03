<?php

namespace WPV_AE;

class Ui {

	private static $_instance = null;

	private $screens = [];

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {
		add_action( 'in_admin_header', [ $this, 'top_bar' ] );
		$this->set_screens();
	}

	protected function set_screens() {
		$this->screens = [
			'ae_global_templates',
			'edit-ae_global_templates',
			'ae_global_templates_page_aepro-settings',
		];
	}

	public function top_bar() {
		$nav_links = [
			5 => [
				'id'    => 'edit-ae_global_templates',
				'label' => __( 'Templates', 'ae-pro' ),
				'icon'  => 'eicon-archive',
				'link'  => admin_url( 'edit.php?post_type=ae_global_templates' ),
			],
			20 => [
				'id'    => 'support',
				'label' => __( 'Get Support', 'ae-pro' ),
				'icon'  => 'eicon-help',
				'link'  => 'https://wpvibes.link/go/ea-support/',
			],
		];

		$nav_links = apply_filters( 'wpvae/admin/ui/header_menu', $nav_links );

		$current_screen = get_current_screen();

		if ( ! in_array( $current_screen->id, $this->screens, true ) ) {
			return;
		}

		?>

		<div class="ae-admin-topbar">
			<div class="ae-branding">
				<svg width="50" height="50" viewBox="0 0 1900 1900" fill="none" xmlns="http://www.w3.org/2000/svg">
				<rect x="45" y="45" width="1810" height="1810" rx="355" stroke="#183A59" stroke-width="90"/>
				<path d="M768.137 689.992C804.213 689.992 837.513 695.419 867.761 706.64C898.172 717.921 924.392 734.876 946.07 757.551H946.069C953.518 765.266 960.337 773.586 966.537 782.489L918.23 901.79L918.644 901.957L902.974 939.224C902.13 914.106 898.778 892.756 893.225 874.918L893.216 874.891L893.208 874.862C886.673 853.672 877.474 837.223 866.108 824.775C854.693 812.273 840.614 802.934 823.463 796.782L823.363 796.747L823.265 796.71C805.762 790.242 785.281 786.785 761.496 786.785H716.758V1122.3H748.547C802.967 1122.3 840.25 1107.35 864.487 1080.94C871.121 1073.72 876.994 1065.4 882.033 1055.92C883.111 1054.39 886.246 1048.81 895 1028.5C906.619 1001.54 921.693 960.765 923.761 955.148L983.894 812.146C988.174 820.916 991.998 830.108 995.371 839.71L995.913 841.238C1007.2 873.394 1012.6 909.787 1012.6 950.059C1012.6 992.856 1007.05 1031.33 995.471 1065.1L995.463 1065.13L995.455 1065.15C983.911 1098.58 966.957 1127.25 944.314 1150.63L944.273 1150.68L944.232 1150.72C921.555 1173.89 893.83 1191.11 861.512 1202.54C829.506 1213.94 793.739 1219.42 754.523 1219.42H611V689.992H768.137Z" fill="#183A59"/>
				<path d="M938.871 1202.83L933.653 1217.42H881.899C882.889 1216.84 883.878 1216.25 884.869 1215.66C894.706 1210.98 906.61 1203.66 918.743 1194.51C934.572 1182.56 947.238 1170.16 953.921 1160.74L938.871 1202.83ZM1111.14 700.43L1289.77 1187.85L1300.61 1217.42H1190.47L1185.28 1202.75L1137.34 1067.01H987.442L982.936 1079.61C967.359 1059.62 919.716 1067.22 868.771 1095.74L1013.37 700.442L1018.66 686H1105.85L1111.14 700.43ZM1062.11 852.854C1061.65 854.193 1061.22 855.462 1060.8 856.657L1022.64 968.227H1102.33L1063.4 856.695L1063.35 856.549L1063.3 856.402C1062.93 855.294 1062.53 854.112 1062.11 852.854Z" fill="#183A59"/>
				<path d="M1419.85 673.113V1231.79C1419.85 1321.72 1346.62 1394.53 1256.6 1394.53H638.935H367.695H322L639.13 1705.6V1545H1256.6C1429.81 1545 1570.8 1404.65 1570.8 1231.79V673.113V597H1419.07L1419.85 673.113Z" fill="#E82A5B"/>
				<path d="M1258.74 359.657C1257.96 359.657 1257.38 359.657 1256.6 359.657H636.201C462.99 359.657 322 500.007 322 672.866V1231.54V1307.66H473.926L472.949 1231.54V672.866C472.949 582.933 546.178 510.13 636.201 510.13H1525.1H1570.8L1258.74 194V359.657Z" fill="#E82A5B"/>
				</svg>
				<h1>Dynific Addons for Elementor <em style="font-size: 16px;"> (formerly Anywhere Elementor)</em></h1>
				<span class="ae-version"><?php echo esc_html( AE_VERSION ); ?> </span>
			</div>


			<nav class="ae-nav">
				<ul>
					<?php
					if ( isset( $nav_links ) && count( $nav_links ) ) {
						ksort( $nav_links );
						foreach ( $nav_links as $id => $link ) {

							$active = ( $current_screen->id === $link['id'] ) ? 'ae-nav-active' : '';

							$target = '';
							$class  = '';
							if ( $link['id'] === 'doc' || $id === 'support' ) {
								$target = 'target="_blank"';
							}

							if ( $link['id'] === 'import-ae_global_templates' ) {
								$class = 'ae-import-template-btn';
							}

							?>
							<li class="<?php echo esc_html( $active ); ?>">
								<!-- <a class="<?php echo esc_attr( $class ); ?>" <?php echo esc_html( $target ); ?> href="<?php echo esc_html( $link['link'] ); ?>"><?php echo esc_html( $link['label'] ); ?></a> -->
								<a class="<?php echo esc_attr( $class ); ?>" title="<?php echo esc_html( $link['label'] ); ?>" <?php echo esc_html( $target ); ?> href="<?php echo esc_html( $link['link'] ); ?>"><i class="<?php echo esc_html( $link['icon'] ); ?>"></i></a>
							</li>
							<?php
						}
					}
					?>
				</ul>
			</nav>
		</div>

		<?php
	}
}
UI::instance();
