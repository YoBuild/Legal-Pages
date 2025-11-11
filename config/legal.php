<?php
// config/legal.php

// Custom defaults and feature/compliance flags for legal page generation.
// Keys may be in colon-format (category:field) or flat keys; the generator
// normalizes both. These values will be merged with site defaults.

return [
	// Override company placeholders (optional)
	'company:name'    => 'Your Company, Inc.',
	'company:email'   => 'contact@example.com',
	'company:phone'   => '+1-555-0100',
	'company:country' => 'United States',

	// Compliance flags
	'compliance:gdpr' => false,
	'compliance:ccpa' => false,
	'compliance:coppa' => false,
	'compliance:ada'  => false,

	// Feature flags
	'feature:newsletter' => false,
	'feature:analytics'  => true,

	// Other default placeholders can be added here
];
