<?php
/**
 * Import customers / partners from Peak-style contact XLSX or CSV.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

use ITWT\Repositories\Customer_Repo;

/**
 * Sync import for contact workbook (ลูกค้า / ผู้จำหน่าย).
 */
final class Contact_Importer {

	private const MAX_BYTES = 5242880; // 5 MB.
	private const MAX_ROWS  = 2000;

	/**
	 * Handle uploaded file and import rows.
	 *
	 * @param array<string,mixed> $file $_FILES element.
	 * @return array{created:int,updated:int,skipped:int,errors:array<int,string>}|\WP_Error
	 */
	public function import_upload( array $file ) {
		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( (string) $file['tmp_name'] ) ) {
			return new \WP_Error( 'itwt_upload', __( 'ไม่พบไฟล์อัปโหลด', 'it-warranty-tracker' ) );
		}
		if ( (int) ( $file['size'] ?? 0 ) > self::MAX_BYTES ) {
			return new \WP_Error( 'itwt_size', __( 'ไฟล์เกิน 5 MB', 'it-warranty-tracker' ) );
		}

		$name = strtolower( (string) ( $file['name'] ?? '' ) );
		$tmp  = (string) $file['tmp_name'];
		$check = wp_check_filetype_and_ext( $tmp, (string) ( $file['name'] ?? '' ) );
		$ext   = strtolower( (string) ( $check['ext'] ?? '' ) );

		$is_xlsx = str_ends_with( $name, '.xlsx' ) || 'xlsx' === $ext;
		$is_csv  = str_ends_with( $name, '.csv' ) || 'csv' === $ext || 'txt' === $ext;

		if ( ! $is_xlsx && ! $is_csv ) {
			return new \WP_Error( 'itwt_type', __( 'รับเฉพาะไฟล์ .xlsx หรือ .csv', 'it-warranty-tracker' ) );
		}

		$rows = $is_xlsx ? $this->rows_from_xlsx( $tmp ) : $this->rows_from_csv( $tmp );
		if ( is_wp_error( $rows ) ) {
			return $rows;
		}

		return $this->import_rows( $rows );
	}

	/**
	 * @param string $path Temp path.
	 * @return array<int,array<int,string>>|\WP_Error
	 */
	private function rows_from_xlsx( string $path ) {
		return ( new Xlsx_Sheet_Reader() )->read_rows( $path );
	}

	/**
	 * @param string $path Temp path.
	 * @return array<int,array<int,string>>|\WP_Error
	 */
	private function rows_from_csv( string $path ) {
		$handle = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( false === $handle ) {
			return new \WP_Error( 'itwt_file', __( 'อ่านไฟล์ไม่สำเร็จ', 'it-warranty-tracker' ) );
		}
		// Strip UTF-8 BOM.
		$bom = fread( $handle, 3 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fread
		if ( "\xEF\xBB\xBF" !== $bom ) {
			rewind( $handle );
		}
		$rows = array();
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$rows[] = array_map( static fn( $c ) => (string) $c, $row );
			if ( count( $rows ) > self::MAX_ROWS + 5 ) {
				fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
				return new \WP_Error( 'itwt_rows', __( 'จำนวนแถวเกินที่อนุญาต (2,000)', 'it-warranty-tracker' ) );
			}
		}
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return $rows;
	}

	/**
	 * @param array<int,array<int,string>> $rows Raw sheet rows.
	 * @return array{created:int,updated:int,skipped:int,errors:array<int,string>}|\WP_Error
	 */
	public function import_rows( array $rows ) {
		$header_index = $this->find_header_row( $rows );
		if ( null === $header_index ) {
			return new \WP_Error(
				'itwt_header',
				__( 'ไม่พบหัวคอลัมน์ที่รองรับ (ต้องมี “ชื่อธุรกิจ/ชื่อบุคคล” และ “ประเภท”)', 'it-warranty-tracker' )
			);
		}

		$map    = $this->map_headers( $rows[ $header_index ] );
		$repo   = new Customer_Repo();
		$result = array(
			'created' => 0,
			'updated' => 0,
			'skipped' => 0,
			'errors'  => array(),
		);

		$data_rows = array_slice( $rows, $header_index + 1 );
		if ( count( $data_rows ) > self::MAX_ROWS ) {
			return new \WP_Error( 'itwt_rows', __( 'จำนวนแถวเกินที่อนุญาต (2,000)', 'it-warranty-tracker' ) );
		}

		Installer::migrate_customer_columns();

		foreach ( $data_rows as $i => $row ) {
			$line = $header_index + 2 + $i;
			if ( ! $this->row_has_data( $row ) ) {
				continue;
			}
			$payload = $this->row_to_payload( $row, $map );
			if ( '' === $payload['company_name'] ) {
				++$result['skipped'];
				continue;
			}

			$existing = $repo->find_import_match( $payload );
			$id       = $existing ? (int) $existing->id : 0;
			if ( $existing && isset( $existing->party_role ) ) {
				$payload['party_role'] = $this->merge_roles(
					(string) $existing->party_role,
					(string) $payload['party_role']
				);
			}
			if ( $existing && ! empty( $existing->customer_code ) ) {
				$payload['customer_code'] = (string) $existing->customer_code;
			}

			$saved = $repo->save( $payload, $id );
			if ( is_wp_error( $saved ) ) {
				$result['errors'][] = sprintf(
					/* translators: 1: row number, 2: error */
					__( 'แถว %1$d: %2$s', 'it-warranty-tracker' ),
					$line,
					$saved->get_error_message()
				);
				continue;
			}
			if ( $id > 0 ) {
				++$result['updated'];
			} else {
				++$result['created'];
			}
		}

		return $result;
	}

	/**
	 * @param array<int,array<int,string>> $rows Rows.
	 */
	private function find_header_row( array $rows ): ?int {
		foreach ( $rows as $i => $row ) {
			$joined = implode( '|', array_map( 'strval', $row ) );
			if ( false !== strpos( $joined, 'ชื่อธุรกิจ/ชื่อบุคคล' ) && false !== strpos( $joined, 'ประเภท' ) ) {
				return (int) $i;
			}
		}
		return null;
	}

	/**
	 * @param array<int,string> $header Header cells.
	 * @return array<string,int> field => column index
	 */
	private function map_headers( array $header ): array {
		$aliases = array(
			'party_role_label'  => array( 'ประเภท' ),
			'external_code'     => array( 'รหัสผู้ติดต่อ' ),
			'customer_type_label' => array( 'ประเภทผู้ติดต่อ' ),
			'business_location_label' => array( 'ที่ตั้งธุรกิจ' ),
			'company_name'      => array( 'ชื่อธุรกิจ/ชื่อบุคคล', 'ชื่อธุรกิจ', 'ชื่อบริษัท' ),
			'address'           => array( 'ที่อยู่' ),
			'address_2'         => array( 'ที่อยู่ 2' ),
			'address_3'         => array( 'ที่อยู่ 3' ),
			'postal_code'       => array( 'รหัสไปรษณีย์' ),
			'tax_id'            => array( 'เลขผู้เสียภาษี' ),
			'branch_code'       => array( 'รหัสสาขา' ),
			'branch_name'       => array( 'สำนักงาน/สาขา' ),
			'contact_name'      => array( 'ชื่อผู้ติดต่อ' ),
			'email'             => array( 'อีเมล' ),
			'mobile'            => array( 'เบอร์มือถือ' ),
			'credit_days'       => array( 'เครดิต (วัน)', 'เครดิต' ),
			'office_phone'      => array( 'เบอร์สำนักงาน' ),
			'fax'               => array( 'เบอร์โทรสาร' ),
			'bank_name'         => array( 'ธนาคาร' ),
			'bank_account'      => array( 'เลขที่บัญชี' ),
			'bank_account_name' => array( 'ชื่อบัญชี' ),
			'bank_account_type' => array( 'ประเภทบัญชี' ),
			'bank_branch'       => array( 'สาขาธนาคาร' ),
			'bank_branch_code'  => array( 'รหัสสาขาธนาคาร' ),
		);

		$normalized = array();
		foreach ( $header as $idx => $label ) {
			$normalized[ $this->normalize_header( (string) $label ) ] = (int) $idx;
		}

		$map = array();
		foreach ( $aliases as $field => $names ) {
			foreach ( $names as $name ) {
				$key = $this->normalize_header( $name );
				if ( isset( $normalized[ $key ] ) ) {
					$map[ $field ] = $normalized[ $key ];
					break;
				}
			}
		}
		return $map;
	}

	private function normalize_header( string $label ): string {
		$label = trim( preg_replace( '/\s+/u', ' ', $label ) ?? $label );
		return $label;
	}

	/**
	 * @param array<int,string> $row Row.
	 */
	private function row_has_data( array $row ): bool {
		foreach ( $row as $cell ) {
			if ( '' !== trim( (string) $cell ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array<int,string>  $row Row cells.
	 * @param array<string,int>  $map Field map.
	 * @return array<string,mixed>
	 */
	private function row_to_payload( array $row, array $map ): array {
		$get = static function ( string $field ) use ( $row, $map ): string {
			if ( ! isset( $map[ $field ] ) ) {
				return '';
			}
			$idx = $map[ $field ];
			return isset( $row[ $idx ] ) ? trim( (string) $row[ $idx ] ) : '';
		};

		$mobile = $get( 'mobile' );
		$office = $get( 'office_phone' );
		$phone  = '' !== $mobile ? $mobile : $office;

		return array(
			'party_role'        => $this->map_party_role( $get( 'party_role_label' ) ),
			'external_code'     => $get( 'external_code' ),
			'customer_type'     => $this->map_customer_type( $get( 'customer_type_label' ) ),
			'business_location' => $this->map_business_location( $get( 'business_location_label' ) ),
			'company_name'      => $get( 'company_name' ),
			'address'           => $get( 'address' ),
			'address_2'         => $get( 'address_2' ),
			'address_3'         => $get( 'address_3' ),
			'postal_code'       => $get( 'postal_code' ),
			'tax_id'            => $get( 'tax_id' ),
			'branch_code'       => $get( 'branch_code' ),
			'branch_name'       => $get( 'branch_name' ),
			'contact_name'      => $get( 'contact_name' ),
			'email'             => $get( 'email' ),
			'mobile'            => $mobile,
			'office_phone'      => $office,
			'phone'             => $phone,
			'fax'               => $get( 'fax' ),
			'credit_days'       => absint( $get( 'credit_days' ) ),
			'bank_name'         => $get( 'bank_name' ),
			'bank_account'      => $get( 'bank_account' ),
			'bank_account_name' => $get( 'bank_account_name' ),
			'bank_account_type' => $get( 'bank_account_type' ),
			'bank_branch'       => $get( 'bank_branch' ),
			'bank_branch_code'  => $get( 'bank_branch_code' ),
		);
	}

	private function map_party_role( string $raw ): string {
		$raw = trim( $raw );
		if ( '' === $raw ) {
			return 'customer';
		}
		$has_customer = false !== strpos( $raw, 'ลูกค้า' );
		$has_partner  = false !== strpos( $raw, 'ผู้จำหน่าย' )
			|| false !== strpos( $raw, 'คู่ค้า' )
			|| false !== strpos( $raw, 'vendor' )
			|| false !== strpos( $raw, 'supplier' );

		if ( $has_customer && $has_partner ) {
			return 'both';
		}
		if ( $has_partner ) {
			return 'partner';
		}
		return 'customer';
	}

	private function map_customer_type( string $raw ): string {
		$raw = trim( $raw );
		if ( false !== strpos( $raw, 'บุคคลธรรมดา' ) ) {
			return 'individual';
		}
		if ( false !== strpos( $raw, 'ไม่แสวง' ) || false !== strpos( $raw, 'มูลนิธิ' ) ) {
			return 'nonprofit';
		}
		if ( false !== strpos( $raw, 'ราชการ' ) || false !== strpos( $raw, 'หน่วยงาน' ) ) {
			return 'government';
		}
		if ( false !== strpos( $raw, 'นิติบุคคล' ) || '' === $raw ) {
			return 'juristic';
		}
		return 'other';
	}

	private function map_business_location( string $raw ): string {
		$raw = trim( $raw );
		if ( false !== strpos( $raw, 'ต่างประเทศ' ) || 0 === strcasecmp( $raw, 'foreign' ) ) {
			return 'foreign';
		}
		return 'thai';
	}

	private function merge_roles( string $existing, string $incoming ): string {
		$roles = array();
		foreach ( array( $existing, $incoming ) as $role ) {
			if ( 'both' === $role ) {
				return 'both';
			}
			if ( 'customer' === $role || 'partner' === $role ) {
				$roles[ $role ] = true;
			}
		}
		if ( isset( $roles['customer'], $roles['partner'] ) ) {
			return 'both';
		}
		if ( isset( $roles['partner'] ) ) {
			return 'partner';
		}
		return 'customer';
	}
}
