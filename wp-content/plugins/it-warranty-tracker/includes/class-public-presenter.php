<?php
/**
 * Public allowlist presenter for asset data.
 *
 * @package ITWT
 */

declare(strict_types=1);

namespace ITWT;

defined( 'ABSPATH' ) || exit;

/**
 * Strips private fields before sending to public API / portal.
 */
final class Public_Presenter {

	/**
	 * Present a single asset with contracts and public service logs.
	 *
	 * @param object            $asset     Asset row.
	 * @param array<int,object> $contracts Contracts.
	 * @param array<int,object> $logs      Service logs (already filtered or raw).
	 * @return array<string,mixed>
	 */
	public static function present_asset( object $asset, array $contracts, array $logs = array() ): array {
		$coverage = Coverage::compute( (string) $asset->status, $contracts );

		$public_specs = array();
		$specs        = json_decode( (string) ( $asset->specs_json ?? '[]' ), true );
		if ( is_array( $specs ) ) {
			foreach ( $specs as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}
				if ( empty( $row['public'] ) ) {
					continue;
				}
				$public_specs[] = array(
					'key'   => sanitize_text_field( (string) ( $row['key'] ?? '' ) ),
					'value' => sanitize_text_field( (string) ( $row['value'] ?? '' ) ),
				);
			}
		}

		$public_contracts = array();
		foreach ( $contracts as $c ) {
			$public_contracts[] = array(
				'contract_type' => (string) $c->contract_type,
				'start_date'    => (string) $c->start_date,
				'end_date'      => (string) $c->end_date,
				'sla_tier'      => (string) $c->sla_tier,
				'status'        => (string) $c->status,
			);
		}

		$public_logs = array();
		foreach ( $logs as $log ) {
			$summary = $log->public_summary ?? null;
			if ( null === $summary || '' === $summary ) {
				continue;
			}
			$public_logs[] = array(
				'service_date'   => (string) $log->service_date,
				'service_type'   => (string) $log->service_type,
				'public_summary' => (string) $summary,
			);
		}

		$serial = (string) ( $asset->serial_number ?? '' );
		$masked = '';
		if ( '' !== $serial ) {
			$masked = '••••' . substr( $serial, -4 );
		}

		return array(
			'service_tag'    => (string) $asset->service_tag,
			'brand'          => (string) $asset->brand,
			'serial_masked'  => $masked,
			'asset_type'     => (string) $asset->asset_type,
			'asset_type_label' => Asset_Types::label( (string) $asset->asset_type ),
			'model_name'     => (string) $asset->model_name,
			'asset_status'   => (string) $asset->status,
			'coverage'       => array(
				'status'         => $coverage['status'],
				'label'          => Coverage::label( $coverage['status'], $coverage['days_remaining'] ),
				'days_remaining' => $coverage['days_remaining'],
				'latest_end'     => $coverage['latest_end'],
			),
			'specs'          => $public_specs,
			'contracts'      => $public_contracts,
			'service_logs'   => $public_logs,
		);
	}

	/**
	 * Compact choice row when serial matches multiple brands.
	 *
	 * @param object $asset Asset row.
	 * @return array<string,string>
	 */
	public static function present_choice( object $asset ): array {
		return array(
			'service_tag' => (string) $asset->service_tag,
			'brand'       => (string) $asset->brand,
			'model_name'  => (string) $asset->model_name,
		);
	}
}
