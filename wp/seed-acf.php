<?php
/**
 * ACF seed via WP-CLI eval-file.
 * Usage: wp --path=/home/wpe-user/sites/tefcpt eval-file /tmp/seed-acf.php
 * Remove from /tmp after running.
 */

$post_id = 2140;

// ── Helper: look up attachment ID by filename ──────────────────────────────

function tefcpt_attachment_id_by_name( $filename ) {
	global $wpdb;
	return (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND post_title=%s ORDER BY ID ASC LIMIT 1",
		pathinfo( $filename, PATHINFO_FILENAME )
	) );
}

// ── PDFs ───────────────────────────────────────────────────────────────────

$pdf = [
	'social-work' => tefcpt_attachment_id_by_name( 'social-work' ),
	'nursing'     => tefcpt_attachment_id_by_name( 'nursing' ),
	'rpt'         => tefcpt_attachment_id_by_name( 'rpt' ),
	'rt'          => tefcpt_attachment_id_by_name( 'rt' ),
	'pa'          => tefcpt_attachment_id_by_name( 'pa' ),
	'mhc'         => tefcpt_attachment_id_by_name( 'mhc' ),
];

echo "PDF attachment IDs:\n";
foreach ( $pdf as $name => $id ) {
	echo "  $name => $id\n";
}

// ── Page-level fields ──────────────────────────────────────────────────────

update_field( 'hero_intro_copy', "If you're a CPT participant pursuing licensure or certification in New York, TEF can pay your application and exam fees. Find your profession below to see what to submit and how.", $post_id );

update_field( 'eligibility_copy', "These services are for **active 1199SEIU TEF CPT participants**. If you're not sure whether you qualify, contact (Email TBD) before submitting a request — duplicate or ineligible submissions can delay processing for everyone.", $post_id );

update_field( 'contact_footer', "For assistance with licensure forms or updates on your submitted licensure/exam fee request, contact (Email TBD).\n\nFor NYSED-specific licensure questions (status checks, processing inquiries), call NYSED directly at **518-474-3817** or use the [NYSED Contact Us form](https://www.op.nysed.gov/contact-us).", $post_id );

// ── Professions repeater ───────────────────────────────────────────────────

$professions = [
	[
		'name'                   => 'Social Work — LMSW / LCSW',
		'slug'                   => 'social-work-lmsw',
		'regulator'              => 'NYSED',
		'form1_pdf'              => $pdf['social-work'],
		'form1_revision'         => 'Rev. 11/19',
		'checklist_urls'         => [
			[ 'url' => 'https://www.op.nysed.gov/professions/licensed-master-social-worker/application-forms', 'label' => 'LMSW' ],
			[ 'url' => 'https://www.op.nysed.gov/professions/licensed-clinical-social-worker/application-forms', 'label' => 'LCSW' ],
		],
		'licensure_asana_url'    => 'https://form.asana.com/?k=TZghbKlRg6P7GVR__ERC7g&d=26094319990341',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => '',
		'exam_asana_url'         => 'https://form.asana.com/?k=NqhmnttDrwmXn2aix7rfuQ&d=26094319990341',
		'exam_walk_in_only'      => false,
		'exam_notes'             => '',
		'card_body'              => '',
		'needs_content_review'   => false,
		'review_notes'           => '',
	],
	[
		'name'                   => 'Nursing — RN / PN',
		'slug'                   => 'nursing-rn',
		'regulator'              => 'NYSED',
		'form1_pdf'              => $pdf['nursing'],
		'form1_revision'         => 'See PDF footer',
		'checklist_urls'         => [
			[ 'url' => 'https://www.op.nysed.gov/professions/registered-professional-nursing/application-forms', 'label' => 'RN' ],
			[ 'url' => 'https://www.op.nysed.gov/professions/licensed-practical-nurses/application-forms', 'label' => 'PN' ],
		],
		'licensure_asana_url'    => 'https://form.asana.com/?k=87sqKe-rLadeWZnlleBYeA&d=26094319990341',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => '',
		'exam_asana_url'         => 'https://form.asana.com/?k=FKoXAba4c93EkuoDjdTCiQ&d=26094319990341',
		'exam_walk_in_only'      => false,
		'exam_notes'             => 'NCLEX-RN or NCLEX-PN exam fee.',
		'card_body'              => '',
		'needs_content_review'   => false,
		'review_notes'           => '',
	],
	[
		'name'                   => 'Registered Pharmacy Technician (RPT)',
		'slug'                   => 'rpt',
		'regulator'              => 'NYSED',
		'form1_pdf'              => $pdf['rpt'],
		'form1_revision'         => 'See PDF footer',
		'checklist_urls'         => [
			[ 'url' => 'https://www.op.nysed.gov/registered-pharmacy-technicians', 'label' => 'NYSED' ],
		],
		'licensure_asana_url'    => 'https://form.asana.com/?k=orXeUggrG2SoShG1MCAJtQ&d=26094319990341',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => '',
		'exam_asana_url'         => 'https://form.asana.com/?k=-w0v7byOE8o2kVdgz1JqIg&d=26094319990341',
		'exam_walk_in_only'      => false,
		'exam_notes'             => '',
		'card_body'              => '',
		'needs_content_review'   => true,
		'review_notes'           => 'Exam Fee Asana URL is shared with CMA and Respiratory Therapist — confirm with client.',
	],
	[
		'name'                   => 'Respiratory Therapist (RT)',
		'slug'                   => 'rt',
		'regulator'              => 'NYSED',
		'form1_pdf'              => $pdf['rt'],
		'form1_revision'         => 'See PDF footer',
		'checklist_urls'         => [
			[ 'url' => 'https://www.op.nysed.gov/respiratory-therapists', 'label' => 'NYSED' ],
		],
		'licensure_asana_url'    => 'https://form.asana.com/?k=74HOmZoKqEejFfZMkHcYXA&d=26094319990341',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => '',
		'exam_asana_url'         => 'https://form.asana.com/?k=-w0v7byOE8o2kVdgz1JqIg&d=26094319990341',
		'exam_walk_in_only'      => false,
		'exam_notes'             => '',
		'card_body'              => '',
		'needs_content_review'   => true,
		'review_notes'           => 'Exam Fee Asana URL is shared with RPT and CMA — confirm with client.',
	],
	[
		'name'                   => 'Physician Assistant (PA)',
		'slug'                   => 'pa',
		'regulator'              => 'NYSED',
		'form1_pdf'              => $pdf['pa'],
		'form1_revision'         => 'See PDF footer',
		'checklist_urls'         => [
			[ 'url' => 'https://www.op.nysed.gov/professions/physician-assistants/license-application-forms', 'label' => 'NYSED' ],
		],
		'licensure_asana_url'    => 'https://form.asana.com/?k=5v6lgttJNT09GoUkzsTe8w&d=26094319990341',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => '',
		'exam_asana_url'         => '',
		'exam_walk_in_only'      => true,
		'exam_notes'             => 'PANCE exam fee is handled walk-in. Contact career services for next steps.',
		'card_body'              => '',
		'needs_content_review'   => false,
		'review_notes'           => '',
	],
	[
		'name'                   => 'Mental Health Counseling (MHC)',
		'slug'                   => 'mhc',
		'regulator'              => 'NYSED',
		'form1_pdf'              => $pdf['mhc'],
		'form1_revision'         => 'See PDF footer',
		'checklist_urls'         => [
			[ 'url' => 'https://www.op.nysed.gov/professions/mental-health-counselors/application-forms', 'label' => 'NYSED' ],
		],
		'licensure_asana_url'    => 'https://form.asana.com/?k=BCv8U9myxesTQogdLiTh2Q&d=26094319990341',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => '',
		'exam_asana_url'         => '',
		'exam_walk_in_only'      => true,
		'exam_notes'             => 'MHC exam fee is handled walk-in. Contact career services for next steps.',
		'card_body'              => '',
		'needs_content_review'   => false,
		'review_notes'           => '',
	],
	[
		'name'                   => 'CASAC — Credentialed Alcoholism and Substance Abuse Counselor',
		'slug'                   => 'casac',
		'regulator'              => 'OASAS',
		'form1_pdf'              => 0,
		'form1_revision'         => '',
		'checklist_urls'         => [],
		'licensure_asana_url'    => 'https://form.asana.com/?k=qQGVATit97gHg8Ii8ZFeyA&d=26094319990341',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => 'Single combined intake form covers both Licensure and Exam fees — indicate which on the form.',
		'exam_asana_url'         => 'https://form.asana.com/?k=qQGVATit97gHg8Ii8ZFeyA&d=26094319990341',
		'exam_walk_in_only'      => false,
		'exam_notes'             => 'Same combined form as Licensure fee.',
		'card_body'              => '',
		'needs_content_review'   => false,
		'review_notes'           => '',
	],
	[
		'name'                   => 'Certified Medical Assistant (CMA)',
		'slug'                   => 'cma',
		'regulator'              => 'AAMA',
		'form1_pdf'              => 0,
		'form1_revision'         => '',
		'checklist_urls'         => [],
		'licensure_asana_url'    => '',
		'licensure_walk_in_only' => false,
		'licensure_notes'        => '',
		'exam_asana_url'         => 'https://form.asana.com/?k=-w0v7byOE8o2kVdgz1JqIg&d=26094319990341',
		'exam_walk_in_only'      => false,
		'exam_notes'             => '',
		'card_body'              => '',
		'needs_content_review'   => true,
		'review_notes'           => 'CMA needs full content review — confirm scope, correct Asana URL, and whether there is a licensure fee process.',
	],
];

$result = update_field( 'professions', $professions, $post_id );
echo $result ? "professions: saved\n" : "professions: FAILED\n";

echo "Done.\n";
