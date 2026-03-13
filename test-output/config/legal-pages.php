<?php
// config/legal-pages.php
//
// Legal Page Generator — Placeholder values for your legal documents.
//
// Placeholder format: 'category:field' => 'value'
// These map to {{category:field}} in Markdown templates.
//
// Edit these values to match your site, then run:  php generate.php

return [

	// ========================================================================
	// COMPANY INFORMATION
	// ========================================================================

	'company:name'       => 'Your Company, Inc.',
	'company:legal_name' => 'Your Company, Inc.',
	'company:email'      => 'contact@example.com',
	'company:phone'      => '+1-555-0100',
	'company:address'    => '123 Main Street, Suite 100',
	'company:city'       => 'Anytown',
	'company:state'      => 'California',
	'company:zip'        => '94001',
	'company:country'    => 'United States',

	// ========================================================================
	// WEBSITE INFORMATION
	// ========================================================================

	'website:url'        => 'https://example.com',
	'website:name'       => 'Example Site',
	'website:domain'     => 'example.com',

	// ========================================================================
	// COMPLIANCE FLAGS
	// ========================================================================
	// Set to true to include compliance-specific conditional sections.

	'compliance:gdpr'      => false,  // EU General Data Protection Regulation
	'compliance:ccpa'      => false,  // California Consumer Privacy Act
	'compliance:coppa'     => false,  // Children's Online Privacy Protection Act
	'compliance:ada'       => false,  // Americans with Disabilities Act
	'compliance:dpa_email' => 'dpo@example.com',  // GDPR Data Protection Officer email

	// ========================================================================
	// FEATURE FLAGS
	// ========================================================================
	// Enable/disable features that affect data collection notices.

	'feature:newsletter'         => false,  // Newsletter subscription
	'feature:analytics'          => true,   // Analytics tracking (Google Analytics, etc.)
	'feature:medical_content'    => false,  // Blog disclaimer: medical advice
	'feature:legal_content'      => false,  // Blog disclaimer: legal advice
	'feature:financial_content'  => false,  // Blog disclaimer: financial advice

	// ========================================================================
	// DATA & PRIVACY
	// ========================================================================
	// Used in privacy-policy.md and across multiple templates.

	'data:collected'       => '',     // e.g. 'name, email address, IP address, browser information'
	'data:cookies'         => '',     // e.g. 'essential, analytics, marketing'
	'data:retention'       => '',     // e.g. '24 months'
	'data:sharing'         => '',     // e.g. 'analytics providers, email service providers'
	'data:location'        => '',     // e.g. 'United States'

	// ========================================================================
	// E-COMMERCE PLACEHOLDERS
	// ========================================================================
	// Used in refund-policy.md, shipping-policy.md, and {{if:ecommerce}} blocks.

	'ecommerce:return_period'       => '',  // e.g. '30 days'
	'ecommerce:refund_time'         => '',  // e.g. '7-10 business days'
	'ecommerce:shipping_time'       => '',  // e.g. '3-5 business days'
	'ecommerce:shipping_providers'  => '',  // e.g. 'USPS, UPS, FedEx'
	'ecommerce:payment_processors'  => '',  // e.g. 'Credit/Debit Cards, PayPal, Stripe'

	// ========================================================================
	// SOCIAL PLACEHOLDERS
	// ========================================================================
	// Used in content-policy.md and {{if:social}} blocks.

	'social:content_policy'      => '',  // e.g. 'hate speech, harassment, illegal activities, spam'
	'social:reporting'           => '',  // e.g. 'report button, email to support@example.com'
	'social:minimum_age'         => '',  // e.g. '13'
	'social:account_termination' => '',  // e.g. 'three violations of our content policy'
	'social:content_rights'      => '',  // e.g. 'non-exclusive license to use your content on our platform'

	// ========================================================================
	// CUSTOM PLACEHOLDERS
	// ========================================================================
	// Add any custom placeholders using 'category:field' format.
	// Available in templates as {{category:field}}.
	//
	// 'custom:support_hours'  => 'Monday-Friday, 9am-5pm EST',
	// 'custom:response_time'  => '24-48 hours',
];
