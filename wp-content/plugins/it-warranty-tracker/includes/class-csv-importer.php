<?php
/**
 * Bulk CSV import / export for assets.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

use ITWT\Repositories\Asset_Repo;
use ITWT\Repositories\Contract_Repo;

/**
 * Handles CSV import batches and export.
 */
final class Csv_Importer {

	private const BATCH_SIZE = 200;
	private const MAX_BYTES  = 5242880; // 5 MB.

	/**
	 * Queue an uploaded CSV for batch import.
	 *
	 * @param array<string,mixed> $file $_FILES element.
	 * @return true|\WP_Error
	 */
	public function queue_upload( array $file ) {
		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) {
			return new \WP_Error( 'itwt_upload', __( 'ไม่พบไฟล์อัปโหลด', 'it-warranty-tracker' ) );
		}
		if ( (int) ( $file['size'] ?? 0 ) > self::MAX_BYTES ) {
			return new \WP_Error( 'itwt_size', __( 'ไฟล์เกิน 5 MB', 'it-warranty-tracker' ) );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], (string) ( $file['name'] ?? '' ) );
		$ext   = strtolower( (string) ( $check['ext'] ?? '' ) );
		if ( 'csv' !== $ext && 'txt' !== $ext ) {
			// Also allow by mime for CSV.
			$mime = (string) ( $check['type'] ?? '' );
			if ( ! in_array( $mime, array( 'text/csv', 'text/plain', 'application/vnd.ms-excel' ), true ) ) {
				$name = strtolower( (string) ( $file['name'] ?? '' ) );
				if ( ! str_ends_with( $name, '.csv' ) ) {
					return new \WP_Error( 'itwt_type', __( 'รับเฉพาะไฟล์ CSV', 'it-warranty-tracker' ) );
				}
			}
		}

		$upload = wp_upload_bits(
			'itwt-import-' . wp_date( 'Ymd-His' ) . '.csv',
			null,
			(string) file_get_contents( $file['tmp_name'] ) // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		);
		if ( ! empty( $upload['error'] ) ) {
			return new \WP_Error( 'itwt_upload', (string) $upload['error'] );
		}

		$job = array(
			'file'    => $upload['file'],
			'offset'  => 0,
			'done'    => 0,
			'errors'  => array(),
			'status'  => 'pending',
			'started' => current_time( 'mysql' ),
		);
		update_option( 'itwt_csv_import_job', $job, false );

		if ( ! wp_next_scheduled( 'itwt_csv_import_batch' ) ) {
			wp_schedule_single_event( time() + 5, 'itwt_csv_import_batch' );
		}

		return true;
	}

	/**
	 * Process one import batch.
	 */
	public function process_batch(): void {
		$job = get_option( 'itwt_csv_import_job', null );
		if ( ! is_array( $job ) || empty( $job['file'] ) || ! is_readable( $job['file'] ) ) {
			return;
		}

		$handle = fopen( $job['file'], 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( false === $handle ) {
			$job['status'] = 'error';
			update_option( 'itwt_csv_import_job', $job, false );
			return;
		}

		$header = fgetcsv( $handle );
		if ( ! is_array( $header ) ) {
			fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
			$job['status'] = 'error';
			update_option( 'itwt_csv_import_job', $job, false );
			return;
		}
		$header = array_map( static fn( $h ) => sanitize_key( (string) $h ), $header );

		$offset = (int) ( $job['offset'] ?? 0 );
		$row_n  = 0;
		$asset_repo = new Asset_Repo();
		$processed  = 0;

		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			++$row_n;
			if ( $row_n <= $offset ) {
				continue;
			}
			if ( $processed >= self::BATCH_SIZE ) {
				break;
			}

			$data = array();
			foreach ( $header as $i => $key ) {
				$data[ $key ] = $row[ $i ] ?? '';
			}

			$result = $this->upsert_row( $asset_repo, $data, $row_n );
			if ( is_wp_error( $result ) ) {
				$job['errors'][] = array(
					'row'     => $row_n,
					'message' => $result->get_error_message(),
				);
			}
			++$processed;
			++$job['done'];
			$job['offset'] = $row_n;
		}

		$eof = feof( $handle );
		fclose( $handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose

		if ( $eof || 0 === $processed ) {
			$job['status'] = 'done';
			update_option( 'itwt_csv_import_job', $job, false );
			return;
		}

		$job['status'] = 'running';
		update_option( 'itwt_csv_import_job', $job, false );
		wp_schedule_single_event( time() + 5, 'itwt_csv_import_batch' );
	}

	/**
	 * Upsert one CSV row by service_tag.
	 *
	 * @param Asset_Repo           $repo Asset repo.
	 * @param array<string,string> $data Row data.
	 * @param int                  $row_n Row number.
	 * @return true|\WP_Error
	 */
	private function upsert_row( Asset_Repo $repo, array $data, int $row_n ) {
		unset( $row_n );
		$tag = Service_Tag::normalize( (string) ( $data['service_tag'] ?? '' ) );
		if ( '' === $tag ) {
			return new \WP_Error( 'itwt_csv', __( 'ไม่มี service_tag', 'it-warranty-tracker' ) );
		}

		$existing = $repo->find_by_tag( $tag );
		$id       = $existing ? (int) $existing->id : 0;

		$result = $repo->save(
			array(
				'service_tag'      => $tag,
				'brand'            => (string) ( $data['brand'] ?? '' ),
				'serial_number'    => (string) ( $data['serial_number'] ?? '' ),
				'asset_type'       => (string) ( $data['asset_type'] ?? \ITWT\Asset_Types::default_slug() ),
				'model_name'       => (string) ( $data['model_name'] ?? '' ),
				'customer_id'      => absint( $data['customer_id'] ?? 0 ),
				'install_location' => (string) ( $data['install_location'] ?? '' ),
				'status'           => (string) ( $data['status'] ?? 'active' ),
			),
			$id
		);

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( ! empty( $data['contract_type'] ) && ! empty( $data['start_date'] ) && ! empty( $data['end_date'] ) ) {
			( new Contract_Repo() )->save(
				array(
					'asset_id'      => (int) $result,
					'contract_type' => (string) $data['contract_type'],
					'start_date'    => (string) $data['start_date'],
					'end_date'      => (string) $data['end_date'],
					'sla_tier'      => (string) ( $data['sla_tier'] ?? '' ),
					'contract_no'   => (string) ( $data['contract_no'] ?? '' ),
					'status'        => 'active',
				)
			);
		}

		return true;
	}

	/**
	 * Stream CSV export to browser.
	 *
	 * @param array<int,object> $assets Assets to export.
	 */
	public function export( array $assets ): void {
		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=itwt-assets-' . wp_date( 'Ymd' ) . '.csv' );

		$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( false === $out ) {
			return;
		}

		fputcsv(
			$out,
			array(
				'service_tag',
				'brand',
				'serial_number',
				'asset_type',
				'model_name',
				'customer_id',
				'install_location',
				'status',
			)
		);

		foreach ( $assets as $asset ) {
			fputcsv(
				$out,
				array(
					$this->escape_csv( (string) $asset->service_tag ),
					$this->escape_csv( (string) $asset->brand ),
					$this->escape_csv( (string) ( $asset->serial_number ?? '' ) ),
					$this->escape_csv( (string) $asset->asset_type ),
					$this->escape_csv( (string) $asset->model_name ),
					(string) (int) $asset->customer_id,
					$this->escape_csv( (string) $asset->install_location ),
					$this->escape_csv( (string) $asset->status ),
				)
			);
		}

		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		exit;
	}

	/**
	 * Prevent CSV injection.
	 *
	 * @param string $value Cell value.
	 */
	private function escape_csv( string $value ): string {
		if ( preg_match( '/^[=+\-@]/', $value ) ) {
			return "'" . $value;
		}
		return $value;
	}
}
