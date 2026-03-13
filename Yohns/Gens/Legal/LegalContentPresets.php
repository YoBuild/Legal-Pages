<?php
namespace Yohns\Gens\Legal;

/**
 * LegalContentPresets - Provides default content presets for different website types
 *
 * Supplies default placeholder values and compliance settings based on website type
 * (personal, ecommerce, social).
 *
 * @package Yohns\Gens\Legal
 */
class LegalContentPresets {
	/**
	 * Get default presets for a website type
	 *
	 * @param string $websiteType The website type (personal, ecommerce, social)
	 * @return array Associative array of placeholder keys and default values
	 */
	public function getPresetsForWebsiteType(string $websiteType): array {
		switch ($websiteType) {
			case 'personal':
				return $this->getPersonalPresets();
			case 'ecommerce':
				return $this->getEcommercePresets();
			case 'social':
				return $this->getSocialPresets();
			default:
				return [];
		}
	}

	/**
	 * Get presets for personal websites (blogs, portfolios)
	 *
	 * @return array Presets with colon-format keys
	 */
	private function getPersonalPresets(): array {
		return [
			'data:collected'        => 'name, email address, IP address',
			'data:cookies'          => 'essential, analytics',
			'data:retention'        => '12 months',
			'data:sharing'          => 'analytics providers',
			'data:location'         => 'United States',
			'compliance:gdpr'       => 'false',
			'compliance:ccpa'       => 'false',
			'feature:blog_comments' => 'true',
			'feature:newsletter'    => 'true',
		];
	}

	/**
	 * Get presets for e-commerce websites (USA-based)
	 *
	 * @return array Presets with colon-format keys
	 */
	private function getEcommercePresets(): array {
		return [
			'data:collected'               => 'name, email address, billing address, shipping address, payment details, purchase history, IP address',
			'data:cookies'                 => 'essential, analytics, marketing, functional',
			'data:retention'               => '36 months',
			'data:sharing'                 => 'payment processors, shipping providers, analytics providers',
			'data:location'                => 'United States',
			'ecommerce:payment_processors' => 'Credit/Debit Cards, PayPal',
			'ecommerce:shipping_providers' => 'USPS, UPS, FedEx',
			'ecommerce:return_period'      => '30 days',
			'ecommerce:shipping_time'      => '3-5 business days',
			'ecommerce:refund_time'        => '7-10 business days',
			'compliance:gdpr'              => 'false',
			'compliance:ccpa'              => 'true',
			'feature:guest_checkout'       => 'true',
		];
	}

	/**
	 * Get presets for social networks and community platforms
	 *
	 * @return array Presets with colon-format keys
	 */
	private function getSocialPresets(): array {
		return [
			'data:collected'                 => 'name, email address, profile information, content posted, connections, messaging history, IP address, device information',
			'data:cookies'                   => 'essential, analytics, functional, targeting, social media',
			'data:retention'                 => '48 months',
			'data:sharing'                   => 'analytics providers, advertising partners, third-party app integrations',
			'data:location'                  => 'United States',
			'social:content_policy'          => 'hate speech, harassment, illegal activities, graphic violence, adult content, spam',
			'social:minimum_age'             => '13',
			'social:reporting'               => 'report button on content, email to support@example.com',
			'social:account_termination'     => 'three violations of our content policy',
			'social:content_rights'          => 'non-exclusive license to use, modify, and distribute your content on our platform',
			'compliance:gdpr'                => 'true',
			'compliance:ccpa'                => 'true',
			'compliance:coppa'               => 'true',
			'feature:user_messaging'         => 'true',
			'feature:user_generated_content' => 'true',
		];
	}
}
