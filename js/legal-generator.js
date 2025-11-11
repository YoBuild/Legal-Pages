/**
 * Legal Page Generator
 * JavaScript for handling the multi-step form and AJAX requests
 */

document.addEventListener('DOMContentLoaded', function() {
	// Initialize theme first (auto-detects or applies user preference)
	initializeTheme();

	// Initialize the form
	initForm();
});

/**
 * Initialize the form and load data
 */
function initForm() {
	// Load page types
	loadPageTypes();

	// Set up event listeners
	setupEventListeners();

	// Show step 1 by default
	showStep(1);
	hideLoader();
}

/**
 * Fetch server-side config defaults (company info, compliance flags, etc.)
 * Called during form initialization to populate fields from config/site.php and config/legal.php
 *
 * @returns {Promise<object>} Defaults from server, or empty object on error
 */
async function getServerDefaults() {
	try {
		showLoader();
		const formData = new FormData();
		formData.append('action', 'init');

		const response = await fetch('/ajax/', {
			method: 'POST',
			body: formData
		});

		const data = await response.json();
		hideLoader();

		if (data.success && data.formData) {
			return data.formData;
		}
	} catch (error) {
		console.warn('Failed to fetch server defaults:', error);
		hideLoader();
	}

	return {};
}

/**
 * Load available page types and populate the cards
 */
function loadPageTypes() {
	const pageTypeContainer = document.getElementById('page-type-container');

	// Page types data
	const pageTypes = [
		{
			id: 'privacy-policy',
			title: 'Privacy Policy',
			description: 'Explains how you collect, use, and share user data.',
			icon: 'shield-lock'
		},
		{
			id: 'terms-of-service',
			title: 'Terms of Service',
			description: 'Sets the rules users agree to when using your site.',
			icon: 'file-text'
		},
		{
			id: 'cookie-policy',
			title: 'Cookie Policy',
			description: 'Details the cookies your site uses and their purposes.',
			icon: 'cookie'
		},
		{
			id: 'dmca-policy',
			title: 'DMCA Policy',
			description: 'Details how you handle copyright infringement claims.',
			icon: 'c-circle'
		},
		{
			id: 'accessibility-statement',
			title: 'Accessibility Statement',
			description: 'Explains your commitment to digital accessibility.',
			icon: 'universal-access'
		},
		{
			id: 'refund-policy',
			title: 'Refund Policy',
			description: 'Outlines your return and refund processes.',
			icon: 'arrow-return-left',
			websiteTypes: ['ecommerce']
		},
		{
			id: 'shipping-policy',
			title: 'Shipping Policy',
			description: 'Explains your shipping methods, timeframes, and costs.',
			icon: 'truck',
			websiteTypes: ['ecommerce']
		},
		{
			id: 'content-policy',
			title: 'Content Policy',
			description: 'Sets guidelines for user-generated content.',
			icon: 'list-check',
			websiteTypes: ['social']
		},
		{
			id: 'blog-disclaimer',
			title: 'Blog Disclaimer',
			description: 'Clarifies limitations of blog content and your liability.',
			icon: 'journal-text',
			websiteTypes: ['personal']
		}
	];

	// Create cards for each page type
	pageTypes.forEach(pageType => {
		const col = document.createElement('div');
		col.className = 'col';

		col.innerHTML = `
			<div class="card template-card h-100" data-template-id="${pageType.id}" data-website-types="${pageType.websiteTypes || ''}">
				<div class="card-body">
					<h5 class="card-title"><i class="bi bi-${pageType.icon}"></i> ${pageType.title}</h5>
					<p class="card-text">${pageType.description}</p>
				</div>
			</div>
		`;

		pageTypeContainer.appendChild(col);
	});

	// Add click event to template cards
	const templateCards = document.querySelectorAll('.template-card');
	templateCards.forEach(card => {
		card.addEventListener('click', function() {
			// Clear previous selection
			templateCards.forEach(c => c.classList.remove('selected'));

			// Mark this card as selected
			this.classList.add('selected');

			// Enable the next button
			const nextButton = document.querySelector('#step-1 .next-step');
			nextButton.disabled = false;

			// Store the selected template ID
			sessionStorage.setItem('selectedTemplateId', this.dataset.templateId);
			sessionStorage.setItem('allowedWebsiteTypes', this.dataset.websiteTypes);
		});
	});
}

/**
 * Set up event listeners for navigation and form submission
 */
function setupEventListeners() {
	// Next step buttons
	const nextButtons = document.querySelectorAll('.next-step');
	nextButtons.forEach(button => {
		button.addEventListener('click', function() {
			const nextStep = parseInt(this.dataset.next);

			// If going to step 3, load the appropriate fields
			if (nextStep === 3) {
				loadCustomizationFields();
			}

			// If going to step 4, generate the preview
			if (nextStep === 4) {
				generatePreview();
			}

			showStep(nextStep);
		});
	});

	// Previous step buttons
	const prevButtons = document.querySelectorAll('.prev-step');
	prevButtons.forEach(button => {
		button.addEventListener('click', function() {
			const prevStep = parseInt(this.dataset.prev);
			showStep(prevStep);
		});
	});

	// Website type selection
	const websiteTypeCards = document.querySelectorAll('.website-type-card');
	websiteTypeCards.forEach(card => {
		card.addEventListener('click', function() {
			// Check if this website type is allowed for the selected template
			const allowedTypes = sessionStorage.getItem('allowedWebsiteTypes');

			if (allowedTypes && allowedTypes !== '' && !allowedTypes.includes(this.dataset.websiteType)) {
				alert('This template is not available for the selected website type. Please choose a different website type or go back and select a different template.');
				return;
			}

			// Clear previous selection
			websiteTypeCards.forEach(c => c.classList.remove('selected'));

			// Mark this card as selected
			this.classList.add('selected');

			// Enable the next button
			const nextButton = document.querySelector('#step-2 .next-step');
			nextButton.disabled = false;

			// Store the selected website type
			sessionStorage.setItem('selectedWebsiteType', this.dataset.websiteType);
		});
	});

	// Form submission for customization
	const customizeForm = document.getElementById('customize-form');
	customizeForm.addEventListener('submit', function(e) {
		e.preventDefault();

		// Collect all form data
		const formData = new FormData(this);

		// Convert to an object for storage
		const formDataObj = {};
		formData.forEach((value, key) => {
			formDataObj[key] = value;
		});

		// Store form data
		sessionStorage.setItem('formData', JSON.stringify(formDataObj));

		// Move to next step
		showStep(4);

		// Generate preview
		generatePreview();
	});

	// GDPR checkbox toggle for DPO email field
	const gdprCheckbox = document.getElementById('compliance-gdpr');
	gdprCheckbox.addEventListener('change', function() {
		const gdprSection = document.querySelector('.gdpr-section');

		if (this.checked) {
			gdprSection.style.display = 'block';
		} else {
			gdprSection.style.display = 'none';
		}
	});

	// Download buttons
	document.getElementById('download-md').addEventListener('click', function() {
		downloadContent('markdown');
	});

	document.getElementById('download-html').addEventListener('click', function() {
		downloadContent('html');
	});

	// Generate final button
	document.getElementById('generate-final').addEventListener('click', function() {
		generateFinal();
	});
}

/**
 * Show a specific step and update the progress indicator
 */
function showStep(stepNumber) {
	// Hide all steps
	const steps = document.querySelectorAll('.form-step');
	steps.forEach(step => {
		step.classList.remove('active');
	});

	// Show the target step
	const targetStep = document.getElementById(`step-${stepNumber}`);
	targetStep.classList.add('active');

	// Update step indicator
	const indicators = document.querySelectorAll('.step-indicator .step');
	indicators.forEach(indicator => {
		const indicatorStep = parseInt(indicator.dataset.step);

		// Remove all classes first
		indicator.classList.remove('active', 'completed');

		// Add appropriate class
		if (indicatorStep === stepNumber) {
			indicator.classList.add('active');
		} else if (indicatorStep < stepNumber) {
			indicator.classList.add('completed');
		}
	});

	// Scroll to top
	window.scrollTo(0, 0);
}

/**
 * Load customization fields based on selected template and website type
 */
function loadCustomizationFields() {
	const templateId = sessionStorage.getItem('selectedTemplateId');
	const websiteType = sessionStorage.getItem('selectedWebsiteType');

	if (!templateId || !websiteType) {
		alert('Please select a template and website type first.');
		showStep(1);
		return;
	}

	// Show loading overlay
	showLoader();

	// Fetch server defaults and merge with hardcoded website-type-specific values
	getServerDefaults().then(serverDefaults => {
		// Get hardcoded website-type-specific defaults
		const websiteDefaults = getWebsiteTypeDefaults(websiteType, templateId);

		// Merge: server defaults first, then website-type-specific (which may override)
		const allDefaults = { ...serverDefaults, ...websiteDefaults };

		// Set form values
		setFormValues(allDefaults);

		// Show website-specific fields
		showWebsiteSpecificFields(websiteType, templateId);

		// Hide loader
		hideLoader();
	});
}

/**
 * Get website-type-specific default values (hardcoded)
 * @param {string} websiteType - The website type (personal, ecommerce, social)
 * @param {string} templateId - The selected template ID
 * @returns {object} Website-type-specific defaults
 */
function getWebsiteTypeDefaults(websiteType, templateId) {
	let typeSpecific = {};

	switch (websiteType) {
		case 'personal':
			typeSpecific = {
				'data:collected': 'name, email address, IP address',
				'data:cookies': 'essential, analytics',
				'data:retention': '12 months',
				'data:sharing': 'analytics providers',
				'data:location': 'United States',
			};
			break;

		case 'ecommerce':
			typeSpecific = {
				'data:collected': 'name, email address, billing address, shipping address, payment details, purchase history, IP address',
				'data:cookies': 'essential, analytics, marketing, functional',
				'data:retention': '36 months',
				'data:sharing': 'payment processors, shipping providers, analytics providers',
				'data:location': 'United States',
				'ecommerce:payment_processors': 'Credit/Debit Cards, PayPal',
				'ecommerce:shipping_providers': 'USPS, UPS, FedEx',
				'ecommerce:return_period': '30 days',
				'ecommerce:shipping_time': '3-5 business days',
				'ecommerce:refund_time': '7-10 business days',
				'compliance:ccpa': true
			};
			break;

		case 'social':
			typeSpecific = {
				'data:collected': 'name, email address, profile information, content posted, connections, messaging history, IP address, device information',
				'data:cookies': 'essential, analytics, functional, targeting, social media',
				'data:retention': '48 months',
				'data:sharing': 'analytics providers, advertising partners, third-party app integrations',
				'data:location': 'United States',
				'social:content_policy': 'hate speech, harassment, illegal activities, graphic violence, adult content, spam',
				'social:minimum_age': '13',
				'social:reporting': 'report button on content, email to support@example.com',
				'social:account_termination': 'three violations of our content policy',
				'social:content_rights': 'non-exclusive license to use, modify, and distribute your content on our platform',
				'compliance:gdpr': true,
				'compliance:ccpa': true,
				'compliance:coppa': true
			};
			break;
	}

	return typeSpecific;
}

/**
 * Set form values based on defaults
 */
function setFormValues(values) {
	Object.keys(values).forEach(key => {
		const value = values[key];
		const element = document.querySelector(`[name="${key}"]`);

		if (element) {
			if (element.type === 'checkbox') {
				element.checked = value === true || value === 'true';

				// Trigger change event for GDPR checkbox
				if (key === 'compliance:gdpr' && element.checked) {
					const event = new Event('change');
					element.dispatchEvent(event);
				}
			} else {
				element.value = value;
			}
		}
	});
}

/**
 * Show website-specific fields based on website type and template
 */
function showWebsiteSpecificFields(websiteType, templateId) {
	const container = document.getElementById('website-type-fields');
	container.innerHTML = '';

	// Add website-type specific fields
	if (websiteType === 'ecommerce') {
		// Show e-commerce specific fields
		const ecommerceFields = `
			<h4 class="category-header"><i class="bi bi-cart"></i> E-commerce Details</h4>
			<div class="row g-3">
				<div class="col-md-6">
					<div class="form-floating">
						<input type="text" class="form-control" id="ecommerce-payment-processors" name="ecommerce:payment_processors" placeholder="Payment Processors" required>
						<label for="ecommerce-payment-processors">Payment Methods Accepted</label>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-floating">
						<input type="text" class="form-control" id="ecommerce-shipping-providers" name="ecommerce:shipping_providers" placeholder="Shipping Providers" required>
						<label for="ecommerce-shipping-providers">Shipping Providers</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<input type="text" class="form-control" id="ecommerce-return-period" name="ecommerce:return_period" placeholder="Return Period" required>
						<label for="ecommerce-return-period">Return Period</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<input type="text" class="form-control" id="ecommerce-shipping-time" name="ecommerce:shipping_time" placeholder="Shipping Time" required>
						<label for="ecommerce-shipping-time">Shipping Timeframe</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<input type="text" class="form-control" id="ecommerce-refund-time" name="ecommerce:refund_time" placeholder="Refund Processing Time" required>
						<label for="ecommerce-refund-time">Refund Processing Time</label>
					</div>
				</div>
			</div>
		`;
		container.innerHTML = ecommerceFields;
	} else if (websiteType === 'social') {
		// Show social network specific fields
		const socialFields = `
			<h4 class="category-header"><i class="bi bi-people"></i> Social Network Details</h4>
			<div class="row g-3">
				<div class="col-md-6">
					<div class="form-floating">
						<textarea class="form-control" id="social-content-policy" name="social:content_policy" placeholder="Content Policy" style="height: 100px" required></textarea>
						<label for="social-content-policy">Content Policy</label>
						<div class="form-text">List prohibited content types</div>
					</div>
				</div>
				<div class="col-md-6">
					<div class="form-floating">
						<input type="text" class="form-control" id="social-reporting" name="social:reporting" placeholder="Reporting Methods" required>
						<label for="social-reporting">Content Reporting Methods</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<input type="text" class="form-control" id="social-minimum-age" name="social:minimum_age" placeholder="Minimum Age" required>
						<label for="social-minimum-age">Minimum User Age</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<input type="text" class="form-control" id="social-account-termination" name="social:account_termination" placeholder="Account Termination" required>
						<label for="social-account-termination">Account Termination Policy</label>
					</div>
				</div>
				<div class="col-md-4">
					<div class="form-floating">
						<input type="text" class="form-control" id="social-content-rights" name="social:content_rights" placeholder="Content Rights" required>
						<label for="social-content-rights">User Content Rights</label>
					</div>
				</div>
			</div>
		`;
		container.innerHTML = socialFields;
	} else if (websiteType === 'personal') {
		// Show personal blog specific fields
		if (templateId === 'blog-disclaimer') {
			const blogFields = `
				<h4 class="category-header"><i class="bi bi-journal-text"></i> Blog Details</h4>
				<div class="row g-3">
					<div class="col-md-4">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature-medical-content" name="feature:medical_content" value="true">
							<label class="form-check-label" for="feature-medical-content">Include Medical Disclaimer</label>
							<div class="form-text">If your blog discusses health or medical topics</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature-legal-content" name="feature:legal_content" value="true">
							<label class="form-check-label" for="feature-legal-content">Include Legal Disclaimer</label>
							<div class="form-text">If your blog discusses legal topics</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="form-check form-switch">
							<input class="form-check-input" type="checkbox" id="feature-financial-content" name="feature:financial_content" value="true">
							<label class="form-check-label" for="feature-financial-content">Include Financial Disclaimer</label>
							<div class="form-text">If your blog discusses financial advice</div>
						</div>
					</div>
				</div>
			`;
			container.innerHTML = blogFields;
		}
	}
}

/**
 * Generate preview content via AJAX
 */
function generatePreview() {
	// Get stored form data
	const formDataStr = sessionStorage.getItem('formData');
	const templateId = sessionStorage.getItem('selectedTemplateId');
	const websiteType = sessionStorage.getItem('selectedWebsiteType');

	if (!formDataStr || !templateId || !websiteType) {
		alert('Missing form data, template ID, or website type.');
		return;
	}

	// Parse form data
	const formData = JSON.parse(formDataStr);

	// Show loading overlay
	showLoader();

	// Prepare AJAX payload
	const ajaxData = new FormData();
	ajaxData.append('action', 'generate_preview');
	ajaxData.append('output_format', 'both'); // Get both markdown and HTML
	ajaxData.append('theme', getThemePreference()); // Send theme preference (auto|light|dark)

	// Add form data fields
	Object.keys(formData).forEach(key => {
		ajaxData.append(`formData[${key}]`, formData[key]);
	});

	ajaxData.append('formData[pageType]', templateId);
	ajaxData.append('formData[websiteType]', websiteType);

	// Make AJAX call to server
	fetch('/ajax/', {
		method: 'POST',
		body: ajaxData
	})
	.then(response => response.json())
	.then(data => {
		hideLoader();

		if (data.success) {
			// Store server response (includes theme preference)
			if (data.theme) {
				sessionStorage.setItem('serverTheme', data.theme);
				applyTheme(data.theme);
			}

			// Display markdown and HTML previews
			if (data.markdown) {
				document.getElementById('markdown-preview').textContent = data.markdown;
				sessionStorage.setItem('markdownPreview', data.markdown);
			}

			if (data.html) {
				document.getElementById('html-preview').innerHTML = data.html;
				sessionStorage.setItem('htmlPreview', data.html);
			}
		} else {
			alert('Error generating preview: ' + (data.message || 'Unknown error'));
		}
	})
	.catch(error => {
		hideLoader();
		console.error('Preview generation error:', error);
		alert('Failed to generate preview: ' + error.message);
	});
}

/**
 * Get the current theme preference (auto|light|dark)
 * Checks for user preference in sessionStorage or defaults to 'auto'
 *
 * @returns {string} Theme preference
 */
function getThemePreference() {
	// Check sessionStorage for explicit user choice
	const stored = sessionStorage.getItem('userTheme');
	if (stored) {
		return stored;
	}

	// Default to 'auto' to let server detect via prefers-color-scheme
	// Default to 'dark' by preference
	return 'dark';
}

/**
 * Apply theme to the UI (data-bs-theme attribute on html tag)
 * Bootstrap 5.3.8 respects data-bs-theme for dark mode
 *
 * @param {string} theme - The theme to apply (light|dark|auto)
 */
function applyTheme(theme) {
	if (theme === 'auto') {
		// Remove the attribute to use system preference
		document.documentElement.removeAttribute('data-bs-theme');
	} else if (theme === 'dark' || theme === 'light') {
		document.documentElement.setAttribute('data-bs-theme', theme);
	}

	// Store user choice
	sessionStorage.setItem('appliedTheme', theme);
}

/**
 * Initialize theme on page load (check for user preference or system default)
 */
function initializeTheme() {
	// Check if user has a stored preference
	const userTheme = sessionStorage.getItem('userTheme');
	if (userTheme) {
		applyTheme(userTheme);
		return;
	}

	// Default to 'auto' (use system preference)
	// Default to dark mode unless user previously chose otherwise
	applyTheme('dark');
}

/**
 * Get template title
 */
function getTemplateTitle(templateId) {
	const titles = {
		'privacy-policy': 'Privacy Policy',
		'terms-of-service': 'Terms of Service',
		'cookie-policy': 'Cookie Policy',
		'dmca-policy': 'DMCA Policy',
		'accessibility-statement': 'Accessibility Statement',
		'refund-policy': 'Refund Policy',
		'shipping-policy': 'Shipping Policy',
		'content-policy': 'Content Policy',
		'blog-disclaimer': 'Blog Disclaimer'
	};

	return titles[templateId] || 'Legal Document';
}

/**
 * Get template introduction text
 */
function getTemplateIntro(templateId, websiteType) {
	switch (templateId) {
		case 'privacy-policy':
			return 'the collection, use, and sharing of your personal information';
		case 'terms-of-service':
			return 'the rules for using our website';
		case 'cookie-policy':
			return 'the cookies we use on our website';
		case 'dmca-policy':
			return 'how we handle copyright infringement claims';
		case 'accessibility-statement':
			return 'our commitment to making our website accessible to all users';
		case 'refund-policy':
			return 'how we handle returns and refunds';
		case 'shipping-policy':
			return 'our shipping methods, timeframes, and costs';
		case 'content-policy':
			return 'guidelines for content shared on our platform';
		case 'blog-disclaimer':
			return 'limitations of the content on our blog';
		default:
			return 'our legal terms and conditions';
	}
}

/**
 * Download content as a file
 */
function downloadContent(type) {
	let content, filename, mimeType;

	if (type === 'markdown') {
		content = sessionStorage.getItem('markdownPreview');
		filename = `${sessionStorage.getItem('selectedTemplateId')}.md`;
		mimeType = 'text/markdown';
	} else {
		content = sessionStorage.getItem('htmlPreview');
		filename = `${sessionStorage.getItem('selectedTemplateId')}.html`;
		mimeType = 'text/html';
	}

	if (!content) {
		alert('No content to download');
		return;
	}

	const blob = new Blob([content], { type: mimeType });
	const url = URL.createObjectURL(blob);

	const a = document.createElement('a');
	a.href = url;
	a.download = filename;
	a.click();

	URL.revokeObjectURL(url);
}

/**
 * Generate final document and save to server via AJAX
 */
function generateFinal() {
	// Get stored data
	const formDataStr = sessionStorage.getItem('formData');
	const templateId = sessionStorage.getItem('selectedTemplateId');
	const websiteType = sessionStorage.getItem('selectedWebsiteType');

	if (!formDataStr || !templateId || !websiteType) {
		alert('Missing form data, template ID, or website type.');
		return;
	}

	// Parse form data
	const formData = JSON.parse(formDataStr);

	// Show loading overlay
	showLoader();

	// Prepare AJAX payload
	const ajaxData = new FormData();
	ajaxData.append('action', 'generate_final');
	ajaxData.append('output_format', 'both'); // Save both markdown and HTML
	ajaxData.append('theme', getThemePreference());

	// Add form data fields
	Object.keys(formData).forEach(key => {
		ajaxData.append(`formData[${key}]`, formData[key]);
	});

	ajaxData.append('formData[pageType]', templateId);
	ajaxData.append('formData[websiteType]', websiteType);

	// Make AJAX call to server
	fetch('/ajax/', {
		method: 'POST',
		body: ajaxData
	})
	.then(response => response.json())
	.then(data => {
		hideLoader();

		if (data.success) {
			// Show success message
			const files = [];
			if (data.filename) {
				files.push(`HTML: ${data.filename}`);
			}
			if (data.markdown_filename) {
				files.push(`Markdown: ${data.markdown_filename}`);
			}

			const message = files.length > 0
				? `Generated successfully!\n\n${files.join('\n')}`
				: 'Generated successfully!';

			alert(message);

			// Optionally reset form or show next steps
			// location.reload(); // uncomment to refresh after generation
		} else {
			alert('Error generating final document: ' + (data.message || 'Unknown error'));
		}
	})
	.catch(error => {
		hideLoader();
		console.error('Generation error:', error);
		alert('Failed to generate document: ' + error.message);
	});
}

/**
 * Show loader overlay
 */
function showLoader() {
	document.getElementById('form-loader').style.display = 'flex';
	console.log('Loader shown');
}

/**
 * Hide loader overlay
 */
function hideLoader() {
	document.getElementById('form-loader').style.display = 'none';
	console.log('Loader hidden');
}