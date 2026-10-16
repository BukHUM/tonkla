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
		$settings = Settings::get();
		$coverage = Coverage::compute( (string) $asset->status, $contracts );
		$show_sla = ! empty( $settings['show_sla'] );

		$public_specs = array();
		if ( ! empty( $settings['show_specs'] ) ) {
			$specs = json_decode( (string) ( $asset->specs_json ?? '[]' ), true );
			if ( is_array( $specs ) ) {
				foreach ( $specs as $row ) {
					if ( ! is_array( $row ) || empty( $row['public'] ) ) {
						continue;
					}
					$key  = sanitize_text_field( (string) ( $row['key'] ?? '' ) );
					$icon = Spec_Icons::sanitize( (string) ( $row['icon'] ?? '' ) );
					if ( '' === $icon ) {
						$icon = Spec_Icons::guess_from_key( $key );
					}
					$public_specs[] = array(
						'key'   => $key,
						'value' => sanitize_text_field( (string) ( $row['value'] ?? '' ) ),
						'icon'  => $icon,
					);
				}
			}
		}

		// Public list: MA only — company/OEM warranty is shown as coverage, not a "signed contract".
		$ma_contracts = array();
		if ( ! empty( $settings['show_contracts'] ) ) {
			foreach ( $contracts as $c ) {
				if ( 'ma' !== (string) $c->contract_type ) {
					continue;
				}
				$row = array(
					'contract_type'       => 'ma',
					'contract_type_label' => Labels::contract_type( 'ma' ),
					'start_date'          => Dates::format( (string) $c->start_date ),
					'end_date'            => Dates::format( (string) $c->end_date ),
					'status'              => (string) $c->status,
					'status_label'        => Labels::contract_status( (string) $c->status ),
				);
				if ( $show_sla ) {
					$row['sla_tier'] = (string) ( $c->sla_tier ?? '' );
				}
				$ma_contracts[] = $row;
			}
		}

		$public_logs = array();
		if ( ! empty( $settings['show_service_logs'] ) ) {
			foreach ( $logs as $log ) {
				$summary = $log->public_summary ?? null;
				if ( null === $summary || '' === $summary ) {
					continue;
				}
				$type = (string) $log->service_type;
				$public_logs[] = array(
					'service_date'       => Dates::format( (string) $log->service_date ),
					'service_type'       => $type,
					'service_type_label' => Labels::service_type( $type ),
					'public_summary'     => (string) $summary,
				);
			}
		}

		$serial = (string) ( $asset->serial_number ?? '' );
		$masked = '';
		if ( ! empty( $settings['show_masked_serial'] ) && '' !== $serial ) {
			$masked = '••••' . substr( $serial, -4 );
		}

		$purchase_fmt = null;
		$warranty_fmt = null;
		$purchase_raw = (string) ( $asset->purchase_date ?? '' );
		$warranty_raw = (string) ( $asset->warranty_end_date ?? '' );
		$coverage_end = ! empty( $coverage['latest_end'] ) ? (string) $coverage['latest_end'] : '';
		$next_start   = ! empty( $coverage['next_start'] ) ? (string) $coverage['next_start'] : '';

		if ( ! empty( $settings['show_purchase_date'] ) && '' !== $purchase_raw ) {
			$purchase_fmt = Dates::format( $purchase_raw );
		}
		if ( ! empty( $settings['show_warranty_end'] ) ) {
			$end_src = '' !== $warranty_raw ? $warranty_raw : $coverage_end;
			if ( '' !== $end_src ) {
				$warranty_fmt = Dates::format( $end_src );
			}
		}

		$status    = (string) $coverage['status'];
		$ma_count  = 0;
		foreach ( $contracts as $c ) {
			if ( 'ma' === (string) ( $c->contract_type ?? '' ) ) {
				++$ma_count;
			}
		}
		$renewable = Coverage::is_renewable( $asset, $ma_count );

		return array(
			'service_tag'      => (string) $asset->service_tag,
			'brand'            => (string) $asset->brand,
			'serial_masked'    => $masked,
			'asset_type'       => (string) $asset->asset_type,
			'asset_type_label' => Asset_Types::label( (string) $asset->asset_type ),
			'model_name'       => (string) $asset->model_name,
			'asset_status'     => (string) $asset->status,
			'purchase_date'    => $purchase_fmt,
			'warranty_end'     => $warranty_fmt,
			'coverage'         => array(
				'status'         => $status,
				'label'          => Coverage::label( $status, $coverage['days_remaining'], $next_start ?: null ),
				'days_remaining' => $coverage['days_remaining'],
				'latest_end'     => '' !== $coverage_end ? Dates::format( $coverage_end ) : null,
				'next_start'     => '' !== $next_start ? Dates::format( $next_start ) : null,
				'can_renew'      => $renewable && ! empty( $coverage['can_renew'] ),
				'renewable'      => $renewable,
			),
			'specs'            => $public_specs,
			'contracts'        => $ma_contracts,
			'service_logs'     => $public_logs,
			'ui'               => array(
				'show_support'      => ! empty( $settings['show_support_button'] ),
				'show_line'         => ! empty( $settings['show_line_button'] ),
				'show_purchase'     => ! empty( $settings['show_purchase_date'] ),
				'show_warranty_end' => ! empty( $settings['show_warranty_end'] ),
				'show_sla'          => $show_sla,
				'show_coverage_bar' => ! empty( $settings['show_coverage_bar'] ),
				'show_contracts'    => ! empty( $settings['show_contracts'] ),
			),
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
