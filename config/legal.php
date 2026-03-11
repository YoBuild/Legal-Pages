<?php
// config/legal.php

/**
 * Legal Page Generator Configuration
 *
 * This file contains defaults and settings for legal document generation.
 * Values here can override those in config/site.php and will be used to
 * pre-populate form fields and template placeholders.
 *
 * Note: Keys can use either format:
 *   - Colon format: 'company:name'
 *   - Underscore format: 'company_name' (auto-converted to colon format)
 *
 * See README-Config.md for detailed documentation.
 */

return [
	// ========================================================================
	// COMPANY INFORMATION OVERRIDES (Optional)
	// ========================================================================
	// These override values from config/site.php if you want different
	// contact information specifically for legal documents.
	// Uncomment and customize as needed.
	// ========================================================================

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
	// Override website information from config/site.php if needed.
	// ========================================================================

	// 'website:url'        => 'https://example.com',
	// 'website:name'       => 'Example Site',
	// 'website:domain'     => 'example.com',

	// ========================================================================
	// COMPLIANCE FLAGS
	// ========================================================================
	// Enable/disable compliance sections in legal documents.
	// Set to true to include compliance-specific sections in templates.
	// ========================================================================

	'compliance:gdpr'    => false,  // EU General Data Protection Regulation
	'compliance:ccpa'    => false,  // California Consumer Privacy Act
	'compliance:coppa'   => false,  // Children's Online Privacy Protection Act (USA)
	'compliance:ada'     => false,  // Americans with Disabilities Act

	// GDPR-specific: Data Protection Officer contact (only needed if GDPR is true)
	// 'compliance:dpa_email' => 'dpo@example.com',

	// ========================================================================
	// FEATURE FLAGS
	// ========================================================================
	// Enable/disable features that affect data collection notices.
	// ========================================================================

	'feature:newsletter' => false,  // Newsletter subscription feature
	'feature:analytics'  => true,   // Analytics tracking (Google Analytics, etc.)

	// Blog-specific features (for Blog Disclaimer page)
	// 'feature:medical_content'    => false,  // Include medical advice disclaimer
	// 'feature:legal_content'      => false,  // Include legal advice disclaimer
	// 'feature:financial_content'  => false,  // Include financial advice disclaimer

	// ========================================================================
	// DATA COLLECTION & PRIVACY (Optional - usually filled in form)
	// ========================================================================
	// These are typically customized per-site in the form (Step 3).
	// You can set defaults here if you want them pre-filled.
	// ========================================================================

	// What personal data you collect from users
	// 'data:collected' => 'name, email address, IP address, browser information',

	// Types of cookies your site uses
	// 'data:cookies' => 'essential, analytics, marketing',

	// How long you retain user data
	// 'data:retention' => '24 months',

	// Third parties you share data with
	// 'data:sharing' => 'analytics providers, email service providers',

	// Where user data is stored
	// 'data:location' => 'United States',

	// ========================================================================
	// E-COMMERCE SPECIFIC (Optional - only for online stores)
	// ========================================================================
	// Only needed if your website type is 'ecommerce'.
	// These will be pre-filled by website type presets in the form.
	// ========================================================================

	// Payment methods accepted
	// 'ecommerce:payment_processors' => 'Credit/Debit Cards, PayPal, Stripe',

	// Shipping carriers used
	// 'ecommerce:shipping_providers' => 'USPS, UPS, FedEx',

	// Return/refund period
	// 'ecommerce:return_period' => '30 days',

	// Estimated shipping timeframe
	// 'ecommerce:shipping_time' => '3-5 business days',

	// Refund processing time
	// 'ecommerce:refund_time' => '7-10 business days',

	// ========================================================================
	// SOCIAL NETWORK SPECIFIC (Optional - only for social platforms)
	// ========================================================================
	// Only needed if your website type is 'social'.
	// These will be pre-filled by website type presets in the form.
	// ========================================================================

	// Content policy (what's prohibited)
	// 'social:content_policy' => 'hate speech, harassment, illegal activities, graphic violence, adult content, spam',

	// Minimum user age
	// 'social:minimum_age' => '13',

	// How users can report violations
	// 'social:reporting' => 'report button on content, email to support@example.com',

	// When accounts are terminated
	// 'social:account_termination' => 'three violations of our content policy',

	// User content rights/licensing
	// 'social:content_rights' => 'non-exclusive license to use, modify, and distribute your content on our platform',

	// ========================================================================
	// CUSTOM PLACEHOLDERS
	// ========================================================================
	// Add any custom placeholders here using the 'category:field' format.
	// These will be available in templates as {{category:field}}.
	// ========================================================================

	// Example custom placeholders:
	// 'custom:support_hours' => 'Monday-Friday, 9am-5pm EST',
	// 'custom:response_time'  => '24-48 hours',
];
