<?php
// config/legal-pages.php
//
// Legal Page Generator — Default placeholder values.
//
// Company/website values (company:*, website:*, current:*) are loaded
// automatically from config/config.php. This file is for legal-specific
// defaults, compliance flags, and page-specific placeholders.
//
// Placeholder format: 'category:field' => 'value'
// These map to {{category:field}} in Markdown templates.
//
// Each section below lists WHICH placeholders each legal page uses.
// Placeholders marked "← config/config.php" are auto-loaded; you only
// need to set the page-specific ones here.

return [

	// ========================================================================
	// COMPANY INFORMATION OVERRIDES (Optional)
	// ========================================================================
	// Uncomment to override values from config/config.php specifically
	// for legal documents (e.g., a different contact email for legal).

	// 'company:name'       => 'Your Company, Inc.',
	// 'company:legal_name' => 'Your Company, Inc.',
	// 'company:email'      => 'legal@example.com',
	// 'company:phone'      => '+1-555-0100',
	// 'company:address'    => '123 Main Street, Suite 100',
	// 'company:city'       => 'Anytown',
	// 'company:state'      => 'California',
	// 'company:zip'        => '94001',
	// 'company:country'    => 'United States',

	// ========================================================================
	// WEBSITE INFORMATION OVERRIDES (Optional)
	// ========================================================================

	// 'website:url'        => 'https://example.com',
	// 'website:name'       => 'Example Site',
	// 'website:domain'     => 'example.com',

	// ========================================================================
	// COMPLIANCE FLAGS
	// ========================================================================
	// Set to true to include compliance-specific conditional sections.

	'compliance:gdpr'      => false,  // EU General Data Protection Regulation
	'compliance:ccpa'      => false,  // California Consumer Privacy Act
	'compliance:coppa'     => false,  // Children's Online Privacy Protection Act
	'compliance:ada'       => false,  // Americans with Disabilities Act
	// 'compliance:dpa_email' => 'dpo@example.com',  // GDPR Data Protection Officer email

	// ========================================================================
	// FEATURE FLAGS
	// ========================================================================
	// Enable/disable features that affect data collection notices.

	'feature:newsletter'   => false,  // Newsletter subscription
	'feature:analytics'    => true,   // Analytics tracking (Google Analytics, etc.)
	// 'feature:medical_content'   => false,  // Blog disclaimer: medical advice
	// 'feature:legal_content'     => false,  // Blog disclaimer: legal advice
	// 'feature:financial_content' => false,  // Blog disclaimer: financial advice


	// =====================================================================---
	// PLACEHOLDERS BY PAGE TYPE
	// ========================================================================
	//
	// Below is every placeholder used in each template. Placeholders that
	// come from config/config.php are listed as comments for reference.
	// Only the page-specific values need to be set here.
	// ========================================================================


	// ========================================================================
	// PRIVACY POLICY — legal/base/privacy-policy.md
	// ========================================================================
	//   website:name     ← config/config.php
	//   website:url      ← config/config.php
	//   company:email    ← config/config.php
	//   current:date     ← auto-generated
	//
	// Page-specific:
	'data:collected'       => '',     // e.g. 'name, email address, IP address, browser information'
	'data:cookies'         => '',     // e.g. 'essential, analytics, marketing'
	'data:retention'       => '',     // e.g. '24 months'
	//
	// Conditional (only when compliance:gdpr = true):
	//   compliance:dpa_email  (set above in Compliance Flags)

	// ========================================================================
	// TERMS OF SERVICE — legal/base/terms-of-service.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   company:country  ← config/config.php
	//   website:name     ← config/config.php
	//   website:url      ← config/config.php
	//   current:date     ← auto-generated
	//
	// Conditional (only when website type = ecommerce):
	//   ecommerce:payment_processors  (set below in Ecommerce section)
	//   ecommerce:shipping_time       (set below in Ecommerce section)
	//   ecommerce:shipping_providers  (set below in Ecommerce section)
	//   ecommerce:return_period       (set below in Ecommerce section)
	//   ecommerce:refund_time         (set below in Ecommerce section)
	//
	// Conditional (only when website type = social):
	//   social:content_policy         (set below in Social section)
	//   social:reporting              (set below in Social section)
	//   social:account_termination    (set below in Social section)
	//   social:minimum_age            (set below in Social section)

	// ========================================================================
	// COOKIE POLICY — legal/base/cookie-policy.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   website:name     ← config/config.php
	//   website:url      ← config/config.php
	//   current:date     ← auto-generated
	//
	// (No page-specific placeholders — all values come from config/config.php)

	// ========================================================================
	// DMCA POLICY — legal/base/dmca-policy.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   company:country  ← config/config.php
	//   website:name     ← config/config.php
	//   current:date     ← auto-generated
	//
	// (No page-specific placeholders — all values come from config/config.php)

	// ========================================================================
	// ACCESSIBILITY STATEMENT — legal/base/accessibility-statement.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   website:name     ← config/config.php
	//   current:date     ← auto-generated
	//
	// (No page-specific placeholders — all values come from config/config.php)

	// ========================================================================
	// BLOG DISCLAIMER — legal/personal/blog-disclaimer.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   website:name     ← config/config.php
	//   current:date     ← auto-generated
	//
	// Conditional (feature flags set above):
	//   feature:medical_content
	//   feature:legal_content
	//   feature:financial_content

	// ========================================================================
	// REFUND POLICY — legal/ecommerce/refund-policy.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   website:name     ← config/config.php
	//   website:url      ← config/config.php
	//   current:date     ← auto-generated
	//
	// Page-specific:
	'ecommerce:return_period'       => '',  // e.g. '30 days'
	'ecommerce:refund_time'         => '',  // e.g. '7-10 business days'

	// ========================================================================
	// SHIPPING POLICY — legal/ecommerce/shipping-policy.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   website:name     ← config/config.php
	//   website:url      ← config/config.php
	//   current:date     ← auto-generated
	//
	// Page-specific:
	'ecommerce:shipping_time'       => '',  // e.g. '3-5 business days'
	'ecommerce:shipping_providers'  => '',  // e.g. 'USPS, UPS, FedEx'

	// ========================================================================
	// TERMS OF SERVICE — ecommerce conditional placeholders
	// ========================================================================
	// These appear inside {{if:ecommerce}} blocks in terms-of-service.md.
	'ecommerce:payment_processors'  => '',  // e.g. 'Credit/Debit Cards, PayPal, Stripe'

	// ========================================================================
	// CONTENT POLICY — legal/social/content-policy.md
	// ========================================================================
	//   company:name     ← config/config.php
	//   company:email    ← config/config.php
	//   company:phone    ← config/config.php
	//   company:address  ← config/config.php
	//   website:name     ← config/config.php
	//   website:url      ← config/config.php
	//   current:date     ← auto-generated
	//
	// Page-specific:
	'social:content_policy'         => '',  // e.g. 'hate speech, harassment, illegal activities, spam'
	'social:reporting'              => '',  // e.g. 'report button, email to support@example.com'
	'social:minimum_age'            => '',  // e.g. '13'
	'social:account_termination'    => '',  // e.g. 'three violations of our content policy'
	// 'social:content_rights'      => '',  // e.g. 'non-exclusive license to use your content on our platform'

	// ========================================================================
	// DATA SHARING & STORAGE (used across multiple templates)
	// ========================================================================
	'data:sharing'                  => '',  // e.g. 'analytics providers, email service providers'
	'data:location'                 => '',  // e.g. 'United States'

	// ========================================================================
	// CUSTOM PLACEHOLDERS
	// ========================================================================
	// Add any custom placeholders using 'category:field' format.
	// Available in templates as {{category:field}}.
	//
	// 'custom:support_hours'  => 'Monday-Friday, 9am-5pm EST',
	// 'custom:response_time'  => '24-48 hours',
];
