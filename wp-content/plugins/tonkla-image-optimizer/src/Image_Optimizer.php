<?php
namespace Tonkla\ImageOptimizer;

defined( 'ABSPATH' ) || exit;
/** Facade for legacy IO_Image_Optimizer singleton. */
final class Image_Optimizer {
	public static function get_instance() {
		return class_exists( 'IO_Image_Optimizer' ) ? \IO_Image_Optimizer::get_instance() : null;
	}
}
