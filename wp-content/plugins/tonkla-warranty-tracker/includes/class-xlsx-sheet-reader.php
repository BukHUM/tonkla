<?php
/**
 * Minimal first-sheet XLSX reader (ZipArchive + SharedStrings).
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Reads rows from the first worksheet of an .xlsx file without Composer deps.
 */
final class Xlsx_Sheet_Reader {

	/**
	 * Read all rows as arrays of string cells (sparse cells filled as '').
	 *
	 * @param string $path Absolute path to .xlsx.
	 * @return array<int,array<int,string>>|\WP_Error
	 */
	public function read_rows( string $path ) {
		if ( ! class_exists( 'ZipArchive' ) ) {
			return new \WP_Error( 'itwt_zip', __( 'เซิร์ฟเวอร์ไม่รองรับ ZipArchive', 'tonkla-warranty-tracker' ) );
		}
		if ( ! is_readable( $path ) ) {
			return new \WP_Error( 'itwt_file', __( 'อ่านไฟล์ไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
		}

		$zip = new \ZipArchive();
		if ( true !== $zip->open( $path ) ) {
			return new \WP_Error( 'itwt_xlsx', __( 'เปิดไฟล์ Excel ไม่สำเร็จ', 'tonkla-warranty-tracker' ) );
		}

		$shared = $this->parse_shared_strings( (string) $zip->getFromName( 'xl/sharedStrings.xml' ) );
		$sheet  = $this->first_sheet_xml( $zip );
		$zip->close();

		if ( '' === $sheet ) {
			return new \WP_Error( 'itwt_xlsx', __( 'ไม่พบชีตในไฟล์ Excel', 'tonkla-warranty-tracker' ) );
		}

		return $this->parse_sheet( $sheet, $shared );
	}

	/**
	 * @param \ZipArchive $zip Open archive.
	 */
	private function first_sheet_xml( \ZipArchive $zip ): string {
		$candidates = array(
			'xl/worksheets/sheet1.xml',
			'xl/worksheets/sheet.xml',
		);
		foreach ( $candidates as $name ) {
			$xml = $zip->getFromName( $name );
			if ( false !== $xml && '' !== $xml ) {
				return (string) $xml;
			}
		}
		for ( $i = 0; $i < $zip->numFiles; $i++ ) {
			$stat = $zip->statIndex( $i );
			$name = (string) ( $stat['name'] ?? '' );
			if ( preg_match( '#^xl/worksheets/sheet\d+\.xml$#', $name ) ) {
				$xml = $zip->getFromIndex( $i );
				if ( false !== $xml ) {
					return (string) $xml;
				}
			}
		}
		return '';
	}

	/**
	 * @param string $xml Shared strings XML or empty.
	 * @return array<int,string>
	 */
	private function parse_shared_strings( string $xml ): array {
		if ( '' === $xml ) {
			return array();
		}
		$doc = $this->load_xml( $xml );
		if ( null === $doc ) {
			return array();
		}

		$out   = array();
		$nodes = $doc->xpath( '//*[local-name()="si"]' );
		if ( ! is_array( $nodes ) ) {
			return array();
		}
		foreach ( $nodes as $si ) {
			$text  = '';
			$texts = $si->xpath( './/*[local-name()="t"]' );
			if ( is_array( $texts ) ) {
				foreach ( $texts as $t ) {
					$text .= (string) $t;
				}
			}
			$out[] = $text;
		}
		return $out;
	}

	/**
	 * @param string            $xml    Sheet XML.
	 * @param array<int,string> $shared Shared strings.
	 * @return array<int,array<int,string>>
	 */
	private function parse_sheet( string $xml, array $shared ): array {
		$doc = $this->load_xml( $xml );
		if ( null === $doc ) {
			return array();
		}

		$row_nodes = $doc->xpath( '//*[local-name()="sheetData"]/*[local-name()="row"]' );
		if ( ! is_array( $row_nodes ) ) {
			return array();
		}

		$rows = array();
		foreach ( $row_nodes as $row_node ) {
			$cells = array();
			$max   = -1;
			$c_nodes = $row_node->xpath( './*[local-name()="c"]' );
			if ( ! is_array( $c_nodes ) ) {
				$rows[] = array();
				continue;
			}
			foreach ( $c_nodes as $c ) {
				$ref = (string) ( $c['r'] ?? '' );
				$col = $this->column_index( $ref );
				if ( $col < 0 ) {
					continue;
				}
				$type  = (string) ( $c['t'] ?? '' );
				$value = '';
				if ( 's' === $type ) {
					$v     = $c->xpath( './*[local-name()="v"]' );
					$idx   = ( is_array( $v ) && isset( $v[0] ) ) ? (int) (string) $v[0] : -1;
					$value = $shared[ $idx ] ?? '';
				} elseif ( 'inlineStr' === $type ) {
					$parts = $c->xpath( './/*[local-name()="t"]' );
					if ( is_array( $parts ) ) {
						foreach ( $parts as $t ) {
							$value .= (string) $t;
						}
					}
				} else {
					$v = $c->xpath( './*[local-name()="v"]' );
					if ( is_array( $v ) && isset( $v[0] ) ) {
						$value = (string) $v[0];
					}
				}
				$cells[ $col ] = $value;
				$max           = max( $max, $col );
			}
			$flat = array();
			for ( $i = 0; $i <= $max; $i++ ) {
				$flat[ $i ] = isset( $cells[ $i ] ) ? (string) $cells[ $i ] : '';
			}
			$rows[] = $flat;
		}
		return $rows;
	}

	/**
	 * @param string $xml XML string.
	 */
	private function load_xml( string $xml ): ?\SimpleXMLElement {
		$prev = libxml_use_internal_errors( true );
		$doc  = simplexml_load_string( $xml );
		libxml_clear_errors();
		libxml_use_internal_errors( $prev );
		return false === $doc ? null : $doc;
	}

	/**
	 * Convert cell ref like "C12" to 0-based column index.
	 *
	 * @param string $ref Cell reference.
	 */
	private function column_index( string $ref ): int {
		if ( ! preg_match( '/^([A-Z]+)/i', $ref, $m ) ) {
			return -1;
		}
		$letters = strtoupper( $m[1] );
		$n       = 0;
		$len     = strlen( $letters );
		for ( $i = 0; $i < $len; $i++ ) {
			$n = ( $n * 26 ) + ( ord( $letters[ $i ] ) - 64 );
		}
		return $n - 1;
	}
}
