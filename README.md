# Webinar Auto-Draft

A WordPress plugin that automatically reverts webinar posts to draft status when their date has passed.

## Description

Webinar Auto-Draft is a WordPress plugin that helps you manage your webinar content by automatically reverting webinar posts to draft status when their scheduled date has passed. This ensures that your website only displays current and upcoming webinars.

## Features

- Automatically reverts webinar posts to draft status when their date has passed
- Configurable check frequency (hourly, daily, weekly)
- Batch processing to handle large numbers of webinars efficiently
- Email notifications for administrators
- Detailed logging of processed webinars
- WordPress Coding Standards compliant
- Comprehensive test coverage

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Advanced Custom Fields (ACF) plugin

## Installation

1. Download the latest release from the [releases page](https://github.com/yourusername/webinar-autodraft/releases)
2. Upload the plugin files to the `/wp-content/plugins/webinar-autodraft` directory, or install the plugin through the WordPress plugins screen directly
3. Activate the plugin through the 'Plugins' screen in WordPress
4. Use the Settings > Webinar Auto-Draft screen to configure the plugin

## Configuration

### Check Frequency
Choose how often the plugin should check for expired webinars:
- Hourly
- Daily (default)
- Weekly

### Batch Size
Set the number of webinars to process in each batch (default: 50)

### Logging
Enable or disable detailed logging of processed webinars

### Notification Emails
Enter email addresses (comma-separated) to receive notifications about reverted webinars

## Usage

1. Create a webinar post using the 'Webinar' post type
2. Set the webinar date using the ACF date field
3. Add the 'autodraft' tag to the webinar post
4. The plugin will automatically revert the post to draft status when the date has passed

## Development

### Setup

1. Clone the repository
2. Install Composer dependencies:
   ```bash
   composer install
   ```
3. Install WordPress test suite:
   ```bash
   bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```

### Testing

Run the test suite:
```bash
composer phpunit
```

Run coding standards check:
```bash
composer phpcs
```

Fix coding standards issues:
```bash
composer phpcbf
```

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Author

Your Name - [your.email@example.com](mailto:your.email@example.com)

## Support

For support, please [open an issue](https://github.com/yourusername/webinar-autodraft/issues) on GitHub. 