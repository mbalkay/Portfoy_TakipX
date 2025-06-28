<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Advanced Assets List Table with sophisticated features
 */
class Portfoy_TakipX_Assets_Table extends WP_List_Table {

	private $user_id;
	private $total_items = 0;

	public function __construct() {
		parent::__construct( array(
			'singular' => 'asset',
			'plural'   => 'assets',
			'ajax'     => true
		) );
		
		$this->user_id = get_current_user_id();
	}

	/**
	 * Define table columns
	 */
	public function get_columns() {
		return array(
			'cb'             => '<input type="checkbox" />',
			'asset_info'     => 'Varlık Bilgisi',
			'quantity'       => 'Miktar',
			'purchase_info'  => 'Alış Bilgileri',
			'current_info'   => 'Güncel Durum',
			'performance'    => 'Performans',
			'value'          => 'Toplam Değer',
			'actions'        => 'İşlemler'
		);
	}

	/**
	 * Define sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'asset_info'    => array( 'symbol', false ),
			'quantity'      => array( 'quantity', false ),
			'purchase_info' => array( 'purchase_date', false ),
			'current_info'  => array( 'current_price', false ),
			'performance'   => array( 'profit_loss', false ),
			'value'         => array( 'total_value', false )
		);
	}

	/**
	 * Define bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'delete'        => 'Sil',
			'update_prices' => 'Fiyatları Güncelle',
			'export'        => 'Dışa Aktar',
			'archive'       => 'Arşivle'
		);
	}

	/**
	 * Handle bulk actions
	 */
	public function process_bulk_action() {
		if ( ! isset( $_POST['asset'] ) || ! is_array( $_POST['asset'] ) ) {
			return;
		}

		$action = $this->current_action();
		$asset_ids = array_map( 'intval', $_POST['asset'] );

		switch ( $action ) {
			case 'delete':
				$this->bulk_delete( $asset_ids );
				break;
			case 'update_prices':
				$this->bulk_update_prices( $asset_ids );
				break;
			case 'export':
				$this->bulk_export( $asset_ids );
				break;
			case 'archive':
				$this->bulk_archive( $asset_ids );
				break;
		}
	}

	/**
	 * Prepare table items with advanced filtering and pagination
	 */
	public function prepare_items() {
		global $wpdb;

		$per_page = $this->get_items_per_page( 'assets_per_page', 20 );
		$current_page = $this->get_pagenum();
		$offset = ( $current_page - 1 ) * $per_page;

		// Handle search and filters
		$search = isset( $_REQUEST['s'] ) ? sanitize_text_field( $_REQUEST['s'] ) : '';
		$asset_type_filter = isset( $_REQUEST['asset_type'] ) ? sanitize_text_field( $_REQUEST['asset_type'] ) : '';
		$status_filter = isset( $_REQUEST['status'] ) ? sanitize_text_field( $_REQUEST['status'] ) : 'active';
		$date_from = isset( $_REQUEST['date_from'] ) ? sanitize_text_field( $_REQUEST['date_from'] ) : '';
		$date_to = isset( $_REQUEST['date_to'] ) ? sanitize_text_field( $_REQUEST['date_to'] ) : '';

		// Build WHERE clause
		$where_conditions = array( "a.user_id = %d" );
		$where_values = array( $this->user_id );

		if ( $search ) {
			$where_conditions[] = "(a.symbol LIKE %s OR a.name LIKE %s)";
			$where_values[] = '%' . $wpdb->esc_like( $search ) . '%';
			$where_values[] = '%' . $wpdb->esc_like( $search ) . '%';
		}

		if ( $asset_type_filter ) {
			$where_conditions[] = "a.asset_type = %s";
			$where_values[] = $asset_type_filter;
		}

		if ( $status_filter ) {
			$where_conditions[] = "a.status = %s";
			$where_values[] = $status_filter;
		}

		if ( $date_from ) {
			$where_conditions[] = "a.purchase_date >= %s";
			$where_values[] = $date_from . ' 00:00:00';
		}

		if ( $date_to ) {
			$where_conditions[] = "a.purchase_date <= %s";
			$where_values[] = $date_to . ' 23:59:59';
		}

		$where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );

		// Handle sorting
		$orderby = isset( $_REQUEST['orderby'] ) ? sanitize_sql_orderby( $_REQUEST['orderby'] ) : 'purchase_date';
		$order = isset( $_REQUEST['order'] ) && $_REQUEST['order'] === 'asc' ? 'ASC' : 'DESC';

		$assets_table = $wpdb->prefix . 'portfoy_takipx_assets';
		$price_history_table = $wpdb->prefix . 'portfoy_takipx_price_history';

		// Get total count for pagination
		$total_query = "SELECT COUNT(*) FROM {$assets_table} a {$where_clause}";
		$this->total_items = $wpdb->get_var( $wpdb->prepare( $total_query, $where_values ) );

		// Main query with advanced calculations
		$query = "
			SELECT a.*,
				   (a.quantity * a.current_price) as current_value,
				   (a.quantity * a.purchase_price) as invested_value,
				   ((a.quantity * a.current_price) - (a.quantity * a.purchase_price)) as profit_loss,
				   (CASE WHEN a.purchase_price > 0 
					THEN (((a.current_price - a.purchase_price) / a.purchase_price) * 100)
					ELSE 0 END) as profit_loss_percentage,
				   ph.price_change_24h,
				   ph.price_change_percentage_24h
			FROM {$assets_table} a
			LEFT JOIN (
				SELECT DISTINCT symbol, price_change_24h, price_change_percentage_24h
				FROM {$price_history_table} ph1
				WHERE ph1.recorded_at = (
					SELECT MAX(ph2.recorded_at) 
					FROM {$price_history_table} ph2 
					WHERE ph2.symbol = ph1.symbol
				)
			) ph ON a.symbol = ph.symbol
			{$where_clause}
			ORDER BY {$orderby} {$order}
			LIMIT %d OFFSET %d
		";

		$query_values = array_merge( $where_values, array( $per_page, $offset ) );
		$this->items = $wpdb->get_results( $wpdb->prepare( $query, $query_values ) );

		// Set pagination arguments
		$this->set_pagination_args( array(
			'total_items' => $this->total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->total_items / $per_page )
		) );
	}

	/**
	 * Render checkbox column
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="asset[]" value="%s" />', $item->id );
	}

	/**
	 * Render asset info column with enhanced display
	 */
	public function column_asset_info( $item ) {
		$asset_type_labels = array(
			'stock'     => 'Hisse',
			'crypto'    => 'Kripto',
			'forex'     => 'Döviz',
			'commodity' => 'Emtia',
			'bond'      => 'Tahvil'
		);

		$type_label = isset( $asset_type_labels[ $item->asset_type ] ) 
			? $asset_type_labels[ $item->asset_type ] 
			: ucfirst( $item->asset_type );

		$type_color = $this->get_asset_type_color( $item->asset_type );

		$html = '<div class="asset-info-cell">';
		$html .= '<div class="asset-symbol">' . esc_html( $item->symbol ) . '</div>';
		$html .= '<div class="asset-name">' . esc_html( $item->name ) . '</div>';
		$html .= '<span class="asset-type-badge" style="background-color: ' . $type_color . '">' . esc_html( $type_label ) . '</span>';
		if ( $item->broker ) {
			$html .= '<div class="asset-broker">📊 ' . esc_html( $item->broker ) . '</div>';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render quantity column
	 */
	public function column_quantity( $item ) {
		return '<div class="quantity-cell">' . number_format( $item->quantity, 8 ) . '</div>';
	}

	/**
	 * Render purchase info column
	 */
	public function column_purchase_info( $item ) {
		$purchase_date = date( 'd.m.Y H:i', strtotime( $item->purchase_date ) );
		$purchase_price = number_format( $item->purchase_price, 4 );
		
		$html = '<div class="purchase-info-cell">';
		$html .= '<div class="purchase-price">₺' . $purchase_price . '</div>';
		$html .= '<div class="purchase-date">📅 ' . $purchase_date . '</div>';
		if ( $item->commission > 0 ) {
			$html .= '<div class="commission">💰 Komisyon: ₺' . number_format( $item->commission, 2 ) . '</div>';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render current info column with real-time data
	 */
	public function column_current_info( $item ) {
		$current_price = number_format( $item->current_price, 4 );
		$change_24h = $item->price_change_percentage_24h ?? 0;
		$change_class = $change_24h >= 0 ? 'positive' : 'negative';
		$change_icon = $change_24h >= 0 ? '📈' : '📉';

		$html = '<div class="current-info-cell">';
		$html .= '<div class="current-price">₺' . $current_price . '</div>';
		if ( $change_24h != 0 ) {
			$html .= '<div class="price-change ' . $change_class . '">';
			$html .= $change_icon . ' ' . number_format( $change_24h, 2 ) . '%';
			$html .= '</div>';
		}
		$html .= '<div class="last-updated">🕒 ' . $this->time_ago( $item->updated_at ) . '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render performance column with advanced metrics
	 */
	public function column_performance( $item ) {
		$profit_loss = $item->profit_loss ?? 0;
		$profit_loss_percentage = $item->profit_loss_percentage ?? 0;
		$performance_class = $profit_loss >= 0 ? 'positive' : 'negative';
		$performance_icon = $profit_loss >= 0 ? '🟢' : '🔴';

		$html = '<div class="performance-cell">';
		$html .= '<div class="profit-loss ' . $performance_class . '">';
		$html .= $performance_icon . ' ₺' . number_format( $profit_loss, 2 );
		$html .= '</div>';
		$html .= '<div class="profit-loss-percentage ' . $performance_class . '">';
		$html .= '(' . number_format( $profit_loss_percentage, 2 ) . '%)';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render value column
	 */
	public function column_value( $item ) {
		$current_value = $item->current_value ?? 0;
		$invested_value = $item->invested_value ?? 0;
		
		$html = '<div class="value-cell">';
		$html .= '<div class="current-value">₺' . number_format( $current_value, 2 ) . '</div>';
		$html .= '<div class="invested-value">Yatırım: ₺' . number_format( $invested_value, 2 ) . '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render actions column
	 */
	public function column_actions( $item ) {
		$actions = array();

		$actions['edit'] = sprintf(
			'<a href="#" class="edit-asset" data-id="%d" title="Düzenle">✏️</a>',
			$item->id
		);

		$actions['view_history'] = sprintf(
			'<a href="#" class="view-history" data-id="%d" title="Geçmiş">📊</a>',
			$item->id
		);

		$actions['add_transaction'] = sprintf(
			'<a href="#" class="add-transaction" data-id="%d" title="İşlem Ekle">💰</a>',
			$item->id
		);

		if ( $item->status === 'active' ) {
			$actions['archive'] = sprintf(
				'<a href="#" class="archive-asset" data-id="%d" title="Arşivle">📁</a>',
				$item->id
			);
		} else {
			$actions['activate'] = sprintf(
				'<a href="#" class="activate-asset" data-id="%d" title="Aktifleştir">✅</a>',
				$item->id
			);
		}

		$actions['delete'] = sprintf(
			'<a href="#" class="delete-asset" data-id="%d" title="Sil" onclick="return confirm(\'Emin misiniz?\')">🗑️</a>',
			$item->id
		);

		return '<div class="actions-cell">' . implode( ' ', $actions ) . '</div>';
	}

	/**
	 * Display filters above the table
	 */
	public function extra_tablenav( $which ) {
		if ( $which !== 'top' ) {
			return;
		}

		$asset_types = array(
			'stock'     => 'Hisse Senedi',
			'crypto'    => 'Kripto Para',
			'forex'     => 'Döviz',
			'commodity' => 'Emtia',
			'bond'      => 'Tahvil'
		);

		$current_type = isset( $_REQUEST['asset_type'] ) ? $_REQUEST['asset_type'] : '';
		$current_status = isset( $_REQUEST['status'] ) ? $_REQUEST['status'] : 'active';
		$date_from = isset( $_REQUEST['date_from'] ) ? $_REQUEST['date_from'] : '';
		$date_to = isset( $_REQUEST['date_to'] ) ? $_REQUEST['date_to'] : '';

		echo '<div class="alignleft actions">';
		
		// Asset type filter
		echo '<select name="asset_type">';
		echo '<option value="">Tüm Türler</option>';
		foreach ( $asset_types as $value => $label ) {
			echo '<option value="' . esc_attr( $value ) . '" ' . selected( $current_type, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}
		echo '</select>';

		// Status filter
		echo '<select name="status">';
		echo '<option value="active" ' . selected( $current_status, 'active', false ) . '>Aktif</option>';
		echo '<option value="archived" ' . selected( $current_status, 'archived', false ) . '>Arşivlenmiş</option>';
		echo '<option value="all" ' . selected( $current_status, 'all', false ) . '>Tümü</option>';
		echo '</select>';

		// Date filters
		echo '<input type="date" name="date_from" value="' . esc_attr( $date_from ) . '" placeholder="Başlangıç tarihi" />';
		echo '<input type="date" name="date_to" value="' . esc_attr( $date_to ) . '" placeholder="Bitiş tarihi" />';

		submit_button( 'Filtrele', 'secondary', 'filter', false );

		// Export button
		echo '<input type="submit" name="export_csv" class="button" value="CSV Dışa Aktar" />';

		echo '</div>';
	}

	/**
	 * Helper functions
	 */
	private function get_asset_type_color( $type ) {
		$colors = array(
			'stock'     => '#1e88e5',
			'crypto'    => '#ff9800',
			'forex'     => '#4caf50',
			'commodity' => '#795548',
			'bond'      => '#9c27b0'
		);

		return isset( $colors[ $type ] ) ? $colors[ $type ] : '#666666';
	}

	private function time_ago( $datetime ) {
		$time = time() - strtotime( $datetime );

		if ( $time < 60 ) return 'Az önce';
		if ( $time < 3600 ) return floor( $time / 60 ) . ' dk önce';
		if ( $time < 86400 ) return floor( $time / 3600 ) . ' sa önce';
		return floor( $time / 86400 ) . ' gün önce';
	}

	/**
	 * Bulk action handlers
	 */
	private function bulk_delete( $asset_ids ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		
		foreach ( $asset_ids as $asset_id ) {
			$wpdb->delete( $table_name, array( 'id' => $asset_id, 'user_id' => $this->user_id ) );
		}
	}

	private function bulk_update_prices( $asset_ids ) {
		// Implementation for bulk price updates
		// This could integrate with external APIs
	}

	private function bulk_export( $asset_ids ) {
		// Implementation for bulk export
		// Generate CSV/Excel file
	}

	private function bulk_archive( $asset_ids ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'portfoy_takipx_assets';
		
		foreach ( $asset_ids as $asset_id ) {
			$wpdb->update( 
				$table_name, 
				array( 'status' => 'archived' ), 
				array( 'id' => $asset_id, 'user_id' => $this->user_id ) 
			);
		}
	}
}