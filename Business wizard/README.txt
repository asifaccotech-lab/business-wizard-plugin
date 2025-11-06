=== Business Onboarding Wizard ===
Contributors: yourname
Tags: onboarding, wizard, business, packages, forms
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A comprehensive multi-step wizard for business onboarding with package selection, Companies House integration, and digital signatures.

== Description ==

Business Onboarding Wizard is a powerful plugin that helps you onboard new business clients through a beautiful, user-friendly multi-step wizard.

**Key Features:**

* 🎨 Beautiful, modern design with responsive layout
* 📦 Dynamic package management with custom pricing
* 🏢 Companies House API integration for automatic company lookup
* ✍️ Digital signature capture
* 📧 Automatic email notifications to users and admins
* 💳 Multiple payment method support
* 📊 Comprehensive admin dashboard with statistics
* 📥 Export submissions to CSV
* 🔒 Secure and validated form submission
* 🌐 Translation ready

**Perfect For:**

* Accountancy firms
* Business consultants
* Legal services
* Any business offering tiered packages

**How It Works:**

1. Customer selects business type and turnover range
2. Available packages are dynamically filtered and displayed
3. Customer enters personal or company details
4. Fee calculation with VAT breakdown
5. Digital signature and terms agreement
6. Payment method selection
7. Confirmation and email notifications

**Admin Features:**

* Create unlimited packages with custom pricing
* Configure business types and turnover ranges
* Assign packages to specific business type + turnover combinations
* View all submissions in a filterable table
* Export data to CSV
* Manage payment methods
* Configure Companies House API integration
* Customize email templates

== Installation ==

**Automatic Installation:**

1. Log in to your WordPress admin panel
2. Go to Plugins → Add New
3. Search for "Business Onboarding Wizard"
4. Click "Install Now" and then "Activate"

**Manual Installation:**

1. Download the plugin ZIP file
2. Go to Plugins → Add New → Upload Plugin
3. Choose the ZIP file and click "Install Now"
4. Activate the plugin

**After Installation:**

1. Go to Business Wizard → Settings
2. Configure your settings:
   - Add Companies House API key (optional but recommended)
   - Set admin email addresses for notifications
   - Customize email subjects
3. Go to Business Wizard → Packages
4. Create your first package
5. Assign the package to business types and turnover ranges
6. Add the shortcode `[business_wizard]` to any page

== Frequently Asked Questions ==

= Do I need a Companies House API key? =

No, the API key is optional. If you don't provide one, users can still manually enter company details. However, the API integration provides a better user experience by allowing automatic company lookup.

= How do I get a Companies House API key? =

1. Visit https://developer.company-information.service.gov.uk/
2. Create a free account
3. Register a new application
4. Copy your API key
5. Paste it in Business Wizard → Settings → API Settings

= Can I customize the design? =

Yes! The plugin uses CSS files that you can customize. The main stylesheet is located at:
`/wp-content/plugins/business-onboarding-wizard/public/css/wizard-style.css`

= How do I configure email notifications? =

Go to Business Wizard → Settings → Email Settings. You can:
- Enable/disable notifications
- Add multiple admin email addresses (one per line)
- Customize email subject lines

= Can I export submissions? =

Yes! Go to Business Wizard → Submissions and click "Export to CSV" to download all submissions.

= What payment gateways are supported? =

The plugin collects payment method preferences. For actual payment processing, you'll need to integrate with your preferred gateway (Stripe, PayPal, etc.) or process payments manually.

= Is the signature legally binding? =

The plugin captures digital signatures and timestamps them. However, the legal validity depends on your jurisdiction and use case. Consult with a legal professional for your specific requirements.

= Can I have different packages for different business types? =

Yes! When creating a package, you can assign it to specific combinations of business types and turnover ranges. The wizard will automatically filter and show only relevant packages.

== Screenshots ==

1. Step 1: Package selection with business type and turnover filters
2. Step 2: Personal details or company information form
3. Step 3: Fee calculation with VAT breakdown
4. Step 4: Digital signature and terms agreement
5. Step 5: Payment method selection
6. Step 6: Confirmation page
7. Admin dashboard with statistics
8. Submissions management screen
9. Package creation interface
10. Settings page

== Changelog ==

= 1.0.0 - 2024-11-06 =
* Initial release
* Multi-step wizard with 6 steps
* Package management system
* Companies House API integration
* Digital signature capture
* Email notifications
* Admin dashboard
* CSV export functionality
* Responsive design
* Translation ready

== Upgrade Notice ==

= 1.0.0 =
Initial release of Business Onboarding Wizard.

== Support ==

For support, feature requests, or bug reports, please visit our support forum or contact us.

== Privacy ==

This plugin stores the following data:
- Customer name and contact information
- Company details (if provided)
- Digital signatures as PNG images
- Selected package and pricing information
- Form submission timestamps

All data is stored in your WordPress database and uploaded signatures are stored in the `/wp-content/uploads/wizard-signatures/` directory.

The plugin does not send any data to external services except:
- Companies House API (if configured) - for company lookups
- Your email server - for sending notifications

No data is collected or sent to the plugin developers.

== Credits ==

Developed with ❤️ for businesses worldwide.
