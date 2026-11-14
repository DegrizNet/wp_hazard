<?php
/**
 * Hazard information block (product tab).
 *
 * @package degriz-hazard
 *
 * @var array  $record     Hazard record.
 * @var array  $words      Signal word labels.
 * @var string $pictograms Pictogram row HTML (escaped by the caller).
 * @var array  $h          Hazard statements code => text.
 * @var array  $p          Precautionary statements code => text.
 * @var array  $files      Downloads: url, label, size.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="degriz-hz">
	<h2 class="degriz-hz__title"><?php echo esc_html( \Degriz\Hazard\Settings::tab_title() ); ?></h2>

	<?php if ( $record['pictograms'] || '' !== $record['signal'] ) : ?>
		<div class="degriz-hz__head">
			<?php echo $pictograms; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts in render_pictograms(). ?>
		</div>
	<?php endif; ?>

	<?php if ( $h ) : ?>
		<h3 class="degriz-hz__subtitle"><?php esc_html_e( 'Hazard statements', 'degriz-hazard' ); ?></h3>
		<ul class="degriz-hz__list">
			<?php foreach ( $h as $code => $text ) : ?>
				<li><strong class="degriz-hz__code"><?php echo esc_html( $code ); ?></strong> <?php echo esc_html( $text ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $p ) : ?>
		<h3 class="degriz-hz__subtitle"><?php esc_html_e( 'Precautionary statements', 'degriz-hazard' ); ?></h3>
		<ul class="degriz-hz__list">
			<?php foreach ( $p as $code => $text ) : ?>
				<li><strong class="degriz-hz__code"><?php echo esc_html( $code ); ?></strong> <?php echo esc_html( $text ); ?></li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>

	<?php if ( $files ) : ?>
		<h3 class="degriz-hz__subtitle"><?php esc_html_e( 'Documents', 'degriz-hazard' ); ?></h3>
		<ul class="degriz-hz__downloads">
			<?php foreach ( $files as $file ) : ?>
				<li>
					<a class="degriz-hz__download" href="<?php echo esc_url( $file['url'] ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html( $file['label'] ); ?>
						<?php if ( '' !== $file['size'] ) : ?>
							<span class="degriz-hz__size">(<?php echo esc_html( $file['size'] ); ?>)</span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	<?php endif; ?>
</div>
