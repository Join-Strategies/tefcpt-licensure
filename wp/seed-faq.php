<?php
/**
 * Seeds FAQ and hero_intro_copy ACF fields on the licensure page.
 * Usage: wp --path=/home/wpe-user/sites/tefcpt eval-file <path>/seed-faq.php
 * Remove after running.
 */

$post_id = 2140;

// ── Hero intro ─────────────────────────────────────────────────────────────

$hero = "If you're a CPT participant pursuing licensure or certification in New York, TEF can pay your application and exam fees. Find your profession below to see what to submit and how.";
$r = update_field( 'hero_intro_copy', $hero, $post_id );
echo $r ? "hero_intro_copy: saved\n" : "hero_intro_copy: FAILED\n";

// ── FAQ (stored as WYSIWYG HTML) ───────────────────────────────────────────

$faq = '
<p><strong>How long does the state take to process my application?</strong><br>
Allow <strong>3–4 weeks after your TEF licensure application has been approved for the state to process your application</strong>. Only the applicant can receive status updates from NYSED — direct status questions to NYSED at 518-474-3817 or via their <a href="https://www.op.nysed.gov/contact-us">Contact Us form</a>.</p>

<p><strong>Do I have to get Form 1 notarized?</strong><br>
Yes, New York State requires Form 1 to be notarized before TEF can submit your application.</p>

<p><strong>How and where do I get a document notarized?</strong></p>
<ul>
  <li>Complete the document that requires notarization, but do <strong>not</strong> sign it beforehand.</li>
  <li>Locate a Notary Public at a bank, law office, shipping center, or another authorized location.</li>
  <li>Bring a valid government-issued photo ID.</li>
  <li>Sign the document in the presence of the Notary Public.</li>
  <li>The Notary will verify your identity and complete the notarization by adding their signature, seal/stamp, and the date.</li>
  <li>Pay any applicable notarization fee.</li>
  <li>Retain the notarized document for your records or submit it as required.</li>
</ul>

<p><strong>Will I be responsible for any out-of-pocket costs if I submit a request?</strong></p>
<ul>
  <li>TEF covers the full cost of eligible licensure and application fees.</li>
  <li>Participants are responsible for any notarization costs, which may be free through your bank or typically range from <strong>$2–$15</strong> at local businesses.</li>
  <li>TEF will cover the cost of the <strong>first two exam attempts</strong>. Any additional exam attempts must be paid for by the participant.</li>
</ul>

<p><strong>What if my request is denied?</strong><br>
If your request is denied, you will be notified of the reason for the denial and provided with guidance regarding next steps or reapplication options, if applicable.</p>

<p><strong>If I already paid for a licensure/application or exam fee, can I be reimbursed?</strong><br>
No. CPT Participants are not eligible for reimbursement of licensure, application, or examination fees that have already been paid. Unfortunately, reimbursement cannot be provided for previously incurred expenses.</p>

<p><strong>I failed my exam. Can I submit another request? Will I need to repay the exam fee?</strong><br>
If you have failed an exam and want TEF to cover another exam fee, you must complete a test preparation program. For information about available test preparation resources, please contact (Email TBD).</p>

<p><strong>Where can I request test preparation services for my exam?</strong><br>
Our team can provide support and guidance for exam preparation. To learn more about available test preparation services, please contact (Email TBD).</p>

<p><strong>I submitted a request for the wrong profession. What should I do?</strong><br>
Please contact us immediately at (Email TBD) if you submitted a request for the wrong profession. Do not submit a new request until a member of our team confirms that the original request has been successfully canceled.</p>

<p><strong>I have withdrawn from the CPT program or am in breach of the CPT service commitment. Can I still submit a request?</strong><br>
No. Participants who have withdrawn from the CPT program or are not in compliance with the CPT service commitment are not eligible for this benefit. Any requests submitted under these circumstances will be denied.</p>

<p><strong>Can I submit a request if I am not enrolled in the CPT program?</strong><br>
No. This benefit is available exclusively to eligible CPT participants.</p>

<p><strong>Can I submit the same request more than once?</strong><br>
Please don\'t — duplicate submissions slow processing. Submit once and allow up to 2 weeks before following up at (Email TBD).</p>

<p><strong>What if I\'m applying for CASAC?</strong><br>
CASAC uses a different process through OASAS. Submit the request form on the CASAC card, then wait for TEF to email you a confirmation letter to upload with your OASAS application.</p>

<p><strong>What if my profession isn\'t listed here?</strong><br>
Contact (Email TBD). This page only covers professions where TEF currently pays licensure or exam fees.</p>
';

$r = update_field( 'faq', trim( $faq ), $post_id );
echo $r ? "faq: saved\n" : "faq: FAILED\n";

echo "Done.\n";
