<?php
/**
 * View Order Details
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.6
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * View Order Details Page
 *
 * @since 1.6
 * @return void
*/
if ( ! isset( $_GET['id'] ) || ! is_numeric( $_GET['id'] ) ) {
	wp_die( __( 'Payment ID not supplied. Please try again', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
}

// Setup the variables
$payment_id   = absint( $_GET['id'] );
$payment      = new EDD_Payment( $payment_id );

// Sanity check... fail if purchase ID is invalid
$payment_exists = $payment->ID;
if ( empty( $payment_exists ) ) {
	wp_die( __( 'The specified ID does not belong to a payment. Please try again', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ) );
}

$number         = $payment->number;
$payment_meta   = $payment->get_meta();
$transaction_id = esc_attr( $payment->transaction_id );
$cart_items     = $payment->cart_details;
$user_id        = $payment->user_id;
$payment_date   = strtotime( $payment->date );
$unlimited      = $payment->has_unlimited_downloads;
$user_info      = edd_get_payment_meta_user_info( $payment_id );
$address        = $payment->address;
$gateway        = $payment->gateway;
$currency_code  = $payment->currency;
$customer       = new EDD_Customer( $payment->customer_id );
?>
<div class="wrap edd-wrap">
	<h2><?php printf( __( 'Payment %s', 'easy-digital-downloads' ), $number ); ?></h2>
	<?php do_action( 'edd_view_order_details_before', $payment_id ); ?>
	<form id="edd-edit-order-form" method="post">
		<?php do_action( 'edd_view_order_details_form_top', $payment_id ); ?>
		<div id="poststuff">
			<div id="edd-dashboard-widgets-wrap">
				<div id="post-body" class="metabox-holder columns-2">
					<div id="postbox-container-1" class="postbox-container">
						<div id="side-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'edd_view_order_details_sidebar_before', $payment_id ); ?>


							<div id="edd-order-update" class="postbox edd-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Update Payment', 'easy-digital-downloads' ); ?></span>
								</h3>
								<div class="inside">
									<div class="edd-admin-box">

										<?php do_action( 'edd_view_order_details_totals_before', $payment_id ); ?>

										<div class="edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Status:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<select name="edd-payment-status" class="medium-text">
													<?php foreach( edd_get_payment_statuses() as $key => $status ) : ?>
														<option value="<?php echo esc_attr( $key ); ?>"<?php selected( $payment->status, $key, true ); ?>><?php echo esc_html( $status ); ?></option>
													<?php endforeach; ?>
												</select>

												<?php
												$status_help  = '<ul>';
												$status_help .= '<li>' . __( '<strong>Pending</strong>: payment is still processing or was abandoned by customer. Successful payments will be marked as Complete automatically once processing is finalized.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Complete</strong>: all processing is completed for this purchase.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Revoked</strong>: access to purchased items is disabled, perhaps due to policy violation or fraud.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Refunded</strong>: the purchase amount is returned to the customer and access to items is disabled.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Abandoned</strong>: the purchase attempt was not completed by the customer.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '<li>' . __( '<strong>Failed</strong>: customer clicked Cancel before completing the purchase.', 'easy-digital-downloads' ) . '</li>';
												$status_help .= '</ul>';
												?>
												<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php echo $status_help; ?>"></span>
											</p>
										</div>

										<div class="edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Date:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<input type="text" name="edd-payment-date" value="<?php echo esc_attr( date( 'm/d/Y', $payment_date ) ); ?>" class="medium-text edd_datepicker"/>
											</p>
										</div>

										<div class="edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Time:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<input type="text" maxlength="2" name="edd-payment-time-hour" value="<?php echo esc_attr( date_i18n( 'H', $payment_date ) ); ?>" class="small-text edd-payment-time-hour"/>&nbsp;:&nbsp;
												<input type="text" maxlength="2" name="edd-payment-time-min" value="<?php echo esc_attr( date( 'i', $payment_date ) ); ?>" class="small-text edd-payment-time-min"/>
											</p>
										</div>

										<?php do_action( 'edd_view_order_details_update_inner', $payment_id ); ?>

										<div class="edd-order-discount edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Discount Code', 'easy-digital-downloads' ); ?>:</span>&nbsp;
												<span><?php if ( $payment->discounts !== 'none' ) { echo '<code>' . $payment->discounts . '</code>'; } else { _e( 'None', 'easy-digital-downloads' ); } ?></span>
											</p>
										</div>

										<?php
										$fees = $payment->fees;
										if ( ! empty( $fees ) ) : ?>
										<div class="edd-order-fees edd-admin-box-inside">
											<p class="strong"><?php _e( 'Fees', 'easy-digital-downloads' ); ?>:</p>
											<ul class="edd-payment-fees">
												<?php foreach( $fees as $fee ) : ?>
												<li data-fee-id="<?php echo $fee['id']; ?>"><span class="fee-label"><?php echo $fee['label'] . ':</span> ' . '<span class="fee-amount" data-fee="' . esc_attr( $fee['amount'] ) . '">' . edd_currency_filter( $fee['amount'], $currency_code ); ?></span></li>
												<?php endforeach; ?>
											</ul>
										</div>
										<?php endif; ?>

										<?php if ( edd_use_taxes() ) : ?>
										<div class="edd-order-taxes edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Tax', 'easy-digital-downloads' ); ?>:</span>&nbsp;
												<input name="edd-payment-tax" class="med-text" type="text" value="<?php echo esc_attr( edd_format_amount( $payment->tax ) ); ?>"/>
											</p>
										</div>
										<?php endif; ?>

										<div class="edd-order-payment edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Total Price', 'easy-digital-downloads' ); ?>:</span>&nbsp;
												<?php echo edd_currency_symbol( $payment->currency ); ?>&nbsp;<input name="edd-payment-total" type="text" class="med-text" value="<?php echo esc_attr( edd_format_amount( $payment->total ) ); ?>"/>
											</p>
										</div>

										<div class="edd-order-payment-recalc-totals edd-admin-box-inside" style="display:none">
											<p>
												<span class="label"><?php _e( 'Recalculate Totals', 'easy-digital-downloads' ); ?>:</span>&nbsp;
												<a href="" id="edd-order-recalc-total" class="button button-secondary right"><?php _e( 'Recalculate', 'easy-digital-downloads' ); ?></a>
											</p>
										</div>

										<?php do_action( 'edd_view_order_details_totals_after', $payment_id ); ?>

									</div><!-- /.edd-admin-box -->

								</div><!-- /.inside -->

								<div class="edd-order-update-box edd-admin-box">
									<?php do_action( 'edd_view_order_details_update_before', $payment_id ); ?>
									<div id="major-publishing-actions">
										<div id="delete-action">
											<a href="<?php echo wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_payment', 'purchase_id' => $payment_id ), admin_url( 'edit.php?post_type=download&page=edd-payment-history' ) ), 'edd_payment_nonce' ) ?>" class="edd-delete-payment edd-delete"><?php _e( 'Delete Payment', 'easy-digital-downloads' ); ?></a>
										</div>
										<input type="submit" class="button button-primary right" value="<?php esc_attr_e( 'Save Payment', 'easy-digital-downloads' ); ?>"/>
										<div class="clear"></div>
									</div>
									<?php do_action( 'edd_view_order_details_update_after', $payment_id ); ?>
								</div><!-- /.edd-order-update-box -->

							</div><!-- /#edd-order-data -->

							<?php if( edd_is_payment_complete( $payment_id ) ) : ?>
							<div id="edd-order-resend-receipt" class="postbox edd-order-data">
								<div class="inside">
									<div class="edd-order-resend-receipt-box edd-admin-box">
										<?php do_action( 'edd_view_order_details_resend_receipt_before', $payment_id ); ?>
										<a href="<?php echo add_query_arg( array( 'edd-action' => 'email_links', 'purchase_id' => $payment_id ) ); ?>" id="<?php if( count( $customer->emails ) > 1 ) { echo 'edd-select-receipt-email'; } else { echo 'edd-resend-receipt'; } ?>" class="button-secondary alignleft"><?php _e( 'Resend Receipt', 'easy-digital-downloads' ); ?></a>
										<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Resend Receipt</strong>: This will send a new copy of the purchase receipt to the customer&#8217;s email address. If download URLs are included in the receipt, new file download URLs will also be included with the receipt.', 'easy-digital-downloads' ); ?>"></span>
										<?php if( count( $customer->emails ) > 1 ) : ?>
											<div class="clear"></div>
											<div class="edd-order-resend-receipt-addresses" style="display:none;">
												<select class="edd-order-resend-receipt-email">
													<option value=""><?php _e( ' -- select email --', 'easy-digital-downloads' ); ?></option>
													<?php foreach( $customer->emails as $email ) : ?>
														<option value="<?php echo esc_attr( $email ); ?>"><?php echo $email; ?></option>
													<?php endforeach; ?>
												</select>
											</div>
										<?php endif; ?>
										<div class="clear"></div>
										<?php do_action( 'edd_view_order_details_resend_receipt_after', $payment_id ); ?>
									</div><!-- /.edd-order-resend-receipt-box -->
								</div>
							</div>
							<?php endif; ?>

							<div id="edd-order-details" class="postbox edd-order-data">

								<h3 class="hndle">
									<span><?php _e( 'Payment Meta', 'easy-digital-downloads' ); ?></span>
								</h3>
								<div class="inside">
									<div class="edd-admin-box">

										<?php do_action( 'edd_view_order_details_payment_meta_before', $payment_id ); ?>

										<?php
										if ( $gateway ) : ?>
											<div class="edd-order-gateway edd-admin-box-inside">
												<p>
													<span class="label"><?php _e( 'Gateway:', 'easy-digital-downloads' ); ?></span>&nbsp;
													<?php echo edd_get_gateway_admin_label( $gateway ); ?>
												</p>
											</div>
										<?php endif; ?>

										<div class="edd-order-payment-key edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Key:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<span><?php echo $payment->key; ?></span>
											</p>
										</div>

										<div class="edd-order-ip edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'IP:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<span><?php echo esc_attr( $payment->ip ); ?></span>
											</p>
										</div>

										<?php if ( $transaction_id ) : ?>
										<div class="edd-order-tx-id edd-admin-box-inside">
											<p>
												<span class="label"><?php _e( 'Transaction ID:', 'easy-digital-downloads' ); ?></span>&nbsp;
												<span><?php echo apply_filters( 'edd_payment_details_transaction_id-' . $gateway, $transaction_id, $payment_id ); ?></span>
											</p>
										</div>
										<?php endif; ?>

										<div class="edd-unlimited-downloads edd-admin-box-inside">
											<p>
												<span class="label"><i data-code="f316" class="dashicons dashicons-download"></i></span>&nbsp;
												<input type="checkbox" name="edd-unlimited-downloads" id="edd_unlimited_downloads" value="1"<?php checked( true, $unlimited, true ); ?>/>
												<label class="description" for="edd_unlimited_downloads"><?php _e( 'Unlimited file downloads', 'easy-digital-downloads' ); ?></label>
												<span alt="f223" class="edd-help-tip dashicons dashicons-editor-help" title="<?php _e( '<strong>Unlimited file downloads</strong>: checking this box will override all other file download limits for this purchase, granting the customer unliimited downloads of all files included on the purchase.', 'easy-digital-downloads' ); ?>"></span>
											</p>
										</div>

										<?php do_action( 'edd_view_order_details_payment_meta_after', $payment_id ); ?>

									</div><!-- /.column-container -->

								</div><!-- /.inside -->

							</div><!-- /#edd-order-data -->

							<div id="edd-order-logs" class="postbox edd-order-logs">

								<h3 class="hndle">
									<span><?php _e( 'Logs', 'easy-digital-downloads' ); ?></span>
								</h3>
								<div class="inside">
									<div class="edd-admin-box">

										<div class="edd-admin-box-inside">

											<p>
												<a href="<?php echo admin_url( '/edit.php?post_type=download&page=edd-reports&tab=logs&payment=' . $payment_id ); ?>"><?php _e( 'View file download log for purchase', 'easy-digital-downloads' ); ?></a>
											</p>
											<p>
												<?php $download_log_url = admin_url( 'edit.php?post_type=download&page=edd-reports&tab=logs&user=' . $user_id ); ?>
												<a href="<?php echo $download_log_url; ?>"><?php _e( 'View customer download log', 'easy-digital-downloads' ); ?></a>
											</p>
											<p>
												<?php $purchase_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&user=' . esc_attr( edd_get_payment_user_email( $payment_id ) ) ); ?>
												<a href="<?php echo $purchase_url; ?>"><?php _e( 'View all purchases of customer', 'easy-digital-downloads' ); ?></a>
											</p>
										</div>

										<?php do_action( 'edd_view_order_details_logs_inner', $payment_id ); ?>

									</div><!-- /.column-container -->

								</div><!-- /.inside -->


							</div><!-- /#edd-order-logs -->

							<?php do_action( 'edd_view_order_details_sidebar_after', $payment_id ); ?>
						</div><!-- /#side-sortables -->
					</div><!-- /#postbox-container-1 -->

					<div id="postbox-container-2" class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">

							<?php do_action( 'edd_view_order_details_main_before', $payment_id ); ?>

							<?php $column_count = edd_item_quantities_enabled() ? 'columns-4' : 'columns-3'; ?>
							<div id="edd-purchased-files" class="postbox <?php echo $column_count; ?>">
								<h3 class="hndle">
									<span><?php printf( __( 'Purchased %s', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></span>
								</h3>

								<?php if ( is_array( $cart_items ) ) :

									$i = 0;
									foreach ( $cart_items as $key => $cart_item ) : ?>
									<div class="row">
										<ul>
											<?php

											// Item ID is checked if isset due to the near-1.0 cart data
											$item_id    = isset( $cart_item['id']    )                                  ? $cart_item['id']                                 : $cart_item;
											$price      = isset( $cart_item['price'] )                                  ? $cart_item['price']                              : false;
											$item_price = isset( $cart_item['item_price'] )                             ? $cart_item['item_price']                         : $price;
											$price_id   = isset( $cart_item['item_number']['options']['price_id'] )     ? $cart_item['item_number']['options']['price_id'] : null;
											$quantity   = isset( $cart_item['quantity'] ) && $cart_item['quantity'] > 0 ? $cart_item['quantity']                           : 1;

											if( false === $price ) {

												// This function is only used on payments with near 1.0 cart data structure
												$price = edd_get_download_final_price( $item_id, $user_info, null );
											}
											?>

											<li class="download">
												<span>
													<a href="<?php echo admin_url( 'post.php?post=' . $item_id . '&action=edit' ); ?>">
														<?php echo get_the_title( $item_id );

														if ( isset( $cart_items[ $key ]['item_number'] ) && isset( $cart_items[ $key ]['item_number']['options'] ) ) {
															$price_options = $cart_items[ $key ]['item_number']['options'];

															if ( edd_has_variable_prices( $item_id ) && isset( $price_id ) ) {
																echo ' - ' . edd_get_price_option_name( $item_id, $price_id, $payment_id );
															}
														}
														?>
													</a>
												</span>
												<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][id]" class="edd-payment-details-download-id" value="<?php echo esc_attr( $item_id ); ?>"/>
												<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][price_id]" class="edd-payment-details-download-price-id" value="<?php echo esc_attr( $price_id ); ?>"/>
												<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][item_price]" class="edd-payment-details-download-item-price" value="<?php echo esc_attr( $item_price ); ?>"/>
												<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][amount]" class="edd-payment-details-download-amount" value="<?php echo esc_attr( $price ); ?>"/>
												<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][quantity]" class="edd-payment-details-download-quantity" value="<?php echo esc_attr( $quantity ); ?>"/>
												<?php if ( ! empty( $cart_items[ $key ]['fees'] ) ) : ?>
													<?php $fees = array_keys( $cart_items[ $key ]['fees'] ); ?>
													<input type="hidden" name="edd-payment-details-downloads[<?php echo $key; ?>][fees]" class="edd-payment-details-download-fees" value="<?php echo esc_attr( json_encode( $fees ) ); ?>"/>
												<?php endif; ?>

											</li>

											<?php if( edd_item_quantities_enabled() ) : ?>
											<li class="quantity">
												<span class="item-price"><?php echo edd_currency_filter( edd_format_amount( $item_price ) ); ?></span>
												&nbsp;&times;&nbsp;<span class="item-quantity"><?php echo $quantity; ?></span>
											</li>
											<?php endif; ?>

											<li class="price">
												<?php if ( edd_item_quantities_enabled() ) : ?>
												<?php echo __( 'Total:', 'easy-digital-downloads' ) . '&nbsp;'; ?>
												<?php endif; ?>
												<span class="price-text"><?php echo edd_currency_filter( edd_format_amount( $price ), $currency_code ); ?></span>
											</li>

											<li class="actions">
												<input type="hidden" class="edd-payment-details-download-has-log" name="edd-payment-details-downloads[<?php echo $key; ?>][has_log]" value="1" />
												<?php if( edd_get_download_files( $item_id, $price_id ) && edd_is_payment_complete( $payment_id ) ) : ?>
													<a href="" class="edd-copy-download-link" data-download-id="<?php echo esc_attr( $item_id ); ?>" data-price-id="<?php echo esc_attr( $price_id ); ?>"><?php _e( 'Copy Download Link(s)', 'easy-digital-downloads' ); ?></a> |
												<?php endif; ?>
												<a href="" class="edd-order-remove-download edd-delete" data-key="<?php echo esc_attr( $key ); ?>"><?php _e( 'Remove', 'easy-digital-downloads' ); ?></a>
											</li>
										</ul>
									</div>
									<?php
									$i++;
									endforeach; ?>
									<div class="inside">
										<ul>
											<li class="download">
												<?php echo EDD()->html->product_dropdown( array(
													'name'   => 'edd-order-download-select',
													'id'     => 'edd-order-download-select',
													'chosen' => true
												) ); ?>
											</li>

											<?php if( edd_item_quantities_enabled() ) : ?>
											<li class="quantity">
												<span><?php _e( 'Quantity', 'easy-digital-downloads' ); ?>:&nbsp;</span>
												<input type="number" id="edd-order-download-quantity" class="small-text" min="1" step="1" value="1" />
											</li>
											<?php endif; ?>

											<li class="price">
												<?php
												echo EDD()->html->text(
													array(
														'name'  => 'edd-order-download-amount',
														'id'    => 'edd-order-download-amount',
														'label' => __( 'Item Price: ', 'easy-digital-downloads' ),
														'class' => 'small-text edd-order-download-price'
													)
												);
												?>
											</li>

											<li class="actions">
												<a href="" id="edd-order-add-download" class="button button-secondary"><?php printf( __( 'Add %s to Payment', 'easy-digital-downloads' ), edd_get_label_singular() ); ?></a>
											</li>

										</ul>

										<input type="hidden" name="edd-payment-downloads-changed" id="edd-payment-downloads-changed" value="" />
										<input type="hidden" name="edd-payment-removed" id="edd-payment-removed" value="{}" />

									</div><!-- /.inside -->
								<?php else : $key = 0; ?>
								<div class="row">
									<p><?php printf( __( 'No %s included with this purchase', 'easy-digital-downloads' ), edd_get_label_plural() ); ?></p>
								</div>
								<?php endif; ?>
							</div><!-- /#edd-purchased-files -->

							<?php do_action( 'edd_view_order_details_files_after', $payment_id ); ?>

							<?php do_action( 'edd_view_order_details_billing_before', $payment_id ); ?>

							<div id="edd-customer-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Customer Details', 'easy-digital-downloads' ); ?></span>
								</h3>
								<div class="inside edd-clearfix">

									<div class="column-container customer-info">
										<div class="column">
											<?php
												$args = array(
													'selected' => $customer->id,
													'name' => 'customer-id',
													'placeholder' => __( 'Type to search all Customers', 'easy-digital-downloads' ),
												);

												echo EDD()->html->customer_dropdown( $args );
											?>
										</div>
										<div class="column">
											<input type="hidden" name="edd-current-customer" value="<?php echo $customer->id; ?>" />
										</div>
										<div class="column">
											<?php if( ! empty( $customer->id ) ) : ?>
												<?php $customer_url = admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $customer->id ); ?>
												<a href="<?php echo $customer_url; ?>"><?php _e( 'View Customer Details', 'easy-digital-downloads' ); ?></a>
												&nbsp;|&nbsp;
											<?php endif; ?>
											<a href="#new" class="edd-payment-new-customer"><?php _e( 'New Customer', 'easy-digital-downloads' ); ?></a>
										</div>
									</div>

									<div class="column-container new-customer" style="display: none">
										<div class="column">
											<strong><?php _e( 'Name:', 'easy-digital-downloads' ); ?></strong>&nbsp;
											<input type="text" name="edd-new-customer-name" value="" class="medium-text"/>
										</div>
										<div class="column">
											<strong><?php _e( 'Email:', 'easy-digital-downloads' ); ?></strong>&nbsp;
											<input type="email" name="edd-new-customer-email" value="" class="medium-text"/>
										</div>
										<div class="column">
											<input type="hidden" id="edd-new-customer" name="edd-new-customer" value="0" />
											<a href="#cancel" class="edd-payment-new-customer-cancel edd-delete"><?php _e( 'Cancel', 'easy-digital-downloads' ); ?></a>
										</div>
										<div class="column">
											<small><em>*<?php _e( 'Click "Save Payment" to create new customer', 'easy-digital-downloads' ); ?></em></small>
										</div>
									</div>

									<?php
									// The edd_payment_personal_details_list hook is left here for backwards compatibility
									do_action( 'edd_payment_personal_details_list', $payment_meta, $user_info );
									do_action( 'edd_payment_view_details', $payment_id );
									?>

								</div><!-- /.inside -->
							</div><!-- /#edd-customer-details -->

							<div id="edd-billing-details" class="postbox">
								<h3 class="hndle">
									<span><?php _e( 'Billing Address', 'easy-digital-downloads' ); ?></span>
								</h3>
								<div class="inside edd-clearfix">

									<div id="edd-order-address">

										<div class="order-data-address">
											<div class="data column-container">
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 1:', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][line1]" value="<?php echo esc_attr( $address['line1'] ); ?>" class="medium-text" />
													</p>
													<p>
														<strong class="order-data-address-line"><?php _e( 'Street Address Line 2:', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][line2]" value="<?php echo esc_attr( $address['line2'] ); ?>" class="medium-text" />
													</p>

												</div>
												<div class="column">
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'City:', 'Address City', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][city]" value="<?php echo esc_attr( $address['city'] ); ?>" class="medium-text"/>

													</p>
													<p>
														<strong class="order-data-address-line"><?php echo _x( 'Zip / Postal Code:', 'Zip / Postal code of address', 'easy-digital-downloads' ); ?></strong><br/>
														<input type="text" name="edd-payment-address[0][zip]" value="<?php echo esc_attr( $address['zip'] ); ?>" class="medium-text"/>

													</p>
												</div>
												<div class="column">
													<p id="edd-order-address-country-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'Country:', 'Address country', 'easy-digital-downloads' ); ?></strong><br/>
														<?php
														echo EDD()->html->select( array(
															'options'          => edd_get_country_list(),
															'name'             => 'edd-payment-address[0][country]',
															'id'               => 'edd-payment-address-country',
															'selected'         => $address[ 'country' ],
															'show_option_all'  => false,
															'show_option_none' => false,
															'chosen'           => true,
															'placeholder'      => __( 'Select a country', 'easy-digital-downloads' ),
															'data'             => array( 'search-type' => 'no_ajax' ),
														) );
														?>
													</p>
													<p id="edd-order-address-state-wrap">
														<strong class="order-data-address-line"><?php echo _x( 'State / Province:', 'State / province of address', 'easy-digital-downloads' ); ?></strong><br/>
														<?php
														$states = edd_get_shop_states( $address['country'] );
														if( ! empty( $states ) ) {
															echo EDD()->html->select( array(
																'options'          => $states,
																'name'             => 'edd-payment-address[0][state]',
																'id'               => 'edd-payment-address-state',
																'selected'         => $address[ 'state' ],
																'show_option_all'  => false,
																'show_option_none' => false,
																'chosen'           => true,
																'placeholder'      => __( 'Select a state', 'easy-digital-downloads' ),
																'data'             => array( 'search-type' => 'no_ajax' ),
															) );
														} else { ?>
															<input type="text" name="edd-payment-address[0][state]" value="<?php echo esc_attr( $address['state'] ); ?>" class="medium-text"/>
															<?php
														} ?>
													</p>
												</div>
											</div>
										</div>
									</div><!-- /#edd-order-address -->

									<?php do_action( 'edd_payment_billing_details', $payment_id ); ?>

								</div><!-- /.inside -->
							</div><!-- /#edd-billing-details -->

							<?php do_action( 'edd_view_order_details_billing_after', $payment_id ); ?>

							<div id="edd-payment-notes" class="postbox">
								<h3 class="hndle"><span><?php _e( 'Payment Notes', 'easy-digital-downloads' ); ?></span></h3>
								<div class="inside">
									<div id="edd-payment-notes-inner">
										<?php
										$notes = edd_get_payment_notes( $payment_id );
										if ( ! empty( $notes ) ) :
											$no_notes_display = ' style="display:none;"';
											foreach ( $notes as $note ) :

												echo edd_get_payment_note_html( $note, $payment_id );

											endforeach;
										else :
											$no_notes_display = '';
										endif;
										echo '<p class="edd-no-payment-notes"' . $no_notes_display . '>'. __( 'No payment notes', 'easy-digital-downloads' ) . '</p>';
										?>
									</div>
									<textarea name="edd-payment-note" id="edd-payment-note" class="large-text"></textarea>

									<p>
										<button id="edd-add-payment-note" class="button button-secondary right" data-payment-id="<?php echo absint( $payment_id ); ?>"><?php _e( 'Add Note', 'easy-digital-downloads' ); ?></button>
									</p>

									<div class="clear"></div>
								</div><!-- /.inside -->
							</div><!-- /#edd-payment-notes -->

							<?php do_action( 'edd_view_order_details_main_after', $payment_id ); ?>
						</div><!-- /#normal-sortables -->
					</div><!-- #postbox-container-2 -->
				</div><!-- /#post-body -->
			</div><!-- #edd-dashboard-widgets-wrap -->
		</div><!-- /#post-stuff -->
		<?php do_action( 'edd_view_order_details_form_bottom', $payment_id ); ?>
		<?php wp_nonce_field( 'edd_update_payment_details_nonce' ); ?>
		<input type="hidden" name="edd_payment_id" value="<?php echo esc_attr( $payment_id ); ?>"/>
		<input type="hidden" name="edd_action" value="update_payment_details"/>
	</form>
	<?php do_action( 'edd_view_order_details_after', $payment_id ); ?>
</div><!-- /.wrap -->

<div id="edd-download-link"></div>
