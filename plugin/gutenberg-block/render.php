<?php
/**
 * Server-render for the TEF-CPT Licensure Page block.
 *
 * Emits the hero header plus the three JSON data blobs consumed by
 * public/licensure-flow.js. All guided-flow rendering is handled
 * client-side; this file owns only the data layer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id = get_the_ID();

// ── ACF helpers ────────────────────────────────────────────────────────────

if ( ! function_exists( 'tefcpt_lic_field' ) ) {
	function tefcpt_lic_field( $name, $post_id ) {
		return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : '';
	}
}

$hero_intro       = tefcpt_lic_field( 'hero_intro_copy', $post_id );
$eligibility_text = tefcpt_lic_field( 'eligibility_copy', $post_id );
$contact_text     = tefcpt_lic_field( 'contact_footer', $post_id );
$faq_html         = tefcpt_lic_field( 'faq', $post_id );
$professions_raw  = tefcpt_lic_field( 'professions', $post_id );

if ( ! is_array( $professions_raw ) ) {
	$professions_raw = [];
}

// ── Build profData ─────────────────────────────────────────────────────────

if ( ! function_exists( 'tefcpt_lic_fee_kind' ) ) {
	function tefcpt_lic_fee_kind( $url, $walk_in, $notes ) {
		if ( $url ) {
			return [ 'kind' => 'asana', 'url' => $url, 'notes' => $notes ?: null ];
		}
		if ( $walk_in ) {
			return [ 'kind' => 'walkin', 'notes' => $notes ?: null ];
		}
		return [ 'kind' => 'none', 'notes' => null ];
	}
}

$prof_data = [];
foreach ( $professions_raw as $p ) {
	$checklists = [];
	if ( ! empty( $p['checklist_urls'] ) && is_array( $p['checklist_urls'] ) ) {
		foreach ( $p['checklist_urls'] as $cl ) {
			if ( ! empty( $cl['url'] ) ) {
				$checklists[] = [
					'url'   => $cl['url'],
					'label' => $cl['label'] ?? '',
				];
			}
		}
	}

	$prof_data[] = [
		'slug'          => sanitize_title( $p['slug'] ?? '' ),
		'name'          => $p['name'] ?? '',
		'regulator'     => $p['regulator'] ?? '',
		'form1Pdf'      => $p['form1_pdf'] ?? null,
		'form1Revision' => $p['form1_revision'] ?? null,
		'checklists'    => $checklists,
		'licensure'     => tefcpt_lic_fee_kind(
			$p['licensure_asana_url'] ?? '',
			! empty( $p['licensure_walk_in_only'] ),
			$p['licensure_notes'] ?? ''
		),
		'exam'          => tefcpt_lic_fee_kind(
			$p['exam_asana_url'] ?? '',
			! empty( $p['exam_walk_in_only'] ),
			$p['exam_notes'] ?? ''
		),
	];
}

// ── Process definitions (structural, hardcoded) ────────────────────────────

$processes = [
	'NYSED' => [
		'prepHeading'  => 'Prepare your Form 1',
		'submitHeading' => 'Submit your fee request',
		'submitIntro'  => "Have your scanned, notarized Form 1 ready \u{2014} you'll upload it inside the licensure fee request. Pick what you need below; you can do one, both, or come back later.",
		'prepSteps'    => [
			[
				'title' => 'Download Form 1',
				'mode'  => 'on-page',
				'body'  => "Download Form 1 for **{{name}}** and review the official NYSED applicant checklist so you know what to gather.\n\n{{form1}}{{checklist}}",
			],
			[
				'title' => 'Complete Form 1',
				'mode'  => 'offline',
				'body'  => "Fill out Form 1 completely. Take your time \u{2014} every section needs to be done before a notary will sign it.",
			],
			[
				'title' => 'Get Form 1 notarized',
				'mode'  => 'offline',
				'body'  => "New York State requires Form 1 to be **notarized**. Bring your completed form to a notary, then scan or photograph the notarized copy so it's ready to upload with your fee request.",
			],
		],
	],
	'OASAS' => [
		'prepHeading'  => '',
		'submitHeading' => 'Submit your CASAC intake',
		'submitIntro'  => "CASAC is regulated by **OASAS**, not NYSED \u{2014} no Form 1 or notarization. Submit the combined intake form below. After you submit, TEF emails you a confirmation letter to upload with your OASAS licensure and exam fee application.",
		'prepSteps'    => [],
	],
	'AAMA' => [
		'prepHeading'  => '',
		'submitHeading' => 'Request your exam fee',
		'submitIntro'  => "**{{name}}** is certified nationally through AAMA, not New York State. No Form 1 or notarization needed \u{2014} TEF can pay your exam fee for eligible participants.",
		'prepSteps'    => [],
	],
];

// ── Simple markdown renderer (for textarea fields) ─────────────────────────

if ( ! function_exists( 'tefcpt_lic_md' ) ) {
	function tefcpt_lic_md( $text ) {
		$text = esc_html( (string) $text );
		$text = preg_replace( '/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text );
		$text = preg_replace( '/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2" target="_blank" rel="noopener">$1</a>', $text );
		$text = preg_replace( '/\n{2,}/', '</p><p>', $text );
		$text = nl2br( $text );
		return '<p>' . $text . '</p>';
	}
}

// ── Config ─────────────────────────────────────────────────────────────────

$config = [
	'eligibilityText' => (string) $eligibility_text,
	'contactText'     => (string) $contact_text,
];

// ── Output ─────────────────────────────────────────────────────────────────

ob_start();
?>
<div class="page">
	<section class="hero">
		<h1><?php echo esc_html( get_the_title( $post_id ) ); ?></h1>
		<?php if ( $hero_intro ) : ?>
			<div class="hero-body"><?php echo wp_kses_post( $hero_intro ); ?></div>
		<?php endif; ?>
	</section>

	<div id="flow-root" class="lf-root"></div>

	<script type="application/json" id="flow-data"><?php echo wp_json_encode( $prof_data ); ?></script>
	<script type="application/json" id="flow-processes"><?php echo wp_json_encode( $processes ); ?></script>
	<script type="application/json" id="flow-config"><?php echo wp_json_encode( $config ); ?></script>

	<?php if ( $faq_html ) : ?>
		<section class="faq">
			<h2>Frequently asked questions</h2>
			<?php echo wp_kses_post( $faq_html ); ?>
		</section>
	<?php endif; ?>

	<?php if ( $contact_text ) : ?>
		<footer class="contact-footer">
			<h2>Questions?</h2>
			<?php echo tefcpt_lic_md( $contact_text ); ?>
		</footer>
	<?php endif; ?>
</div>
<?php
echo ob_get_clean();
