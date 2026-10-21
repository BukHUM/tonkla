<?php
namespace Tonkla\ImageOptimizer;

defined( 'ABSPATH' ) || exit;
/** Facade for legacy IO_Image_Cleanup. */
final class Image_Cleanup {
	public static function create(): ?\IO_Image_Cleanup {
		return class_exists( 'IO_Image_Cleanup' ) ? new \IO_Image_Cleanup() : null;
	}
}
