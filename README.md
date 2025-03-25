# Webinar Auto-Draft

A WordPress plugin that automatically reverts manually tagged webinar posts to draft status when their date has passed.

## Description

Webinar Auto-Draft is a WordPress plugin that helps manage webinar content by automatically reverting published webinar posts to draft status once their scheduled date has passed. This is particularly useful for maintaining a clean and organized webinar library.

## Features

- Automatically reverts webinar posts to draft status when their date passes
- Configurable check frequency (every 6 hours, twice daily, or daily)
- Batch processing to handle large numbers of webinars efficiently
- Email notifications for status changes
- Detailed logging of all actions
- Manual tagging system for selective automation

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- Advanced Custom Fields (ACF) plugin

## Installation

1. Download the latest release from the [releases page](https://github.com/cillianKNEAT/webinar-autodraft/releases)
2. Upload the plugin files to the `/wp-content/plugins/webinar-autodraft` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Configure the plugin settings under Settings > Webinar Auto-Draft

## Configuration

1. Go to Settings > Webinar Auto-Draft
2. Configure the following options:
   - Check Frequency: How often to check for expired webinars
   - Batch Size: Number of webinars to process in each batch
   - Enable Logging: Toggle detailed logging
   - Notification Emails: Email addresses to receive status updates

## Usage

1. Create or edit a webinar post
2. Add the "autodraft" tag to the post
3. Set the webinar date using the ACF date field
4. Publish the post

The plugin will automatically revert the post to draft status when the webinar date passes.

## Development

### Prerequisites

- PHP 7.2 or higher
- Composer
- PHPUnit
- WordPress Coding Standards

### Setup

1. Clone the repository:
   ```bash
   git clone https://github.com/cillianKNEAT/webinar-autodraft.git
   cd webinar-autodraft
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Install WordPress test suite:
   ```bash
   bin/install-wp-tests.sh wordpress_test root '' localhost latest
   ```

### Running Tests

```bash
# Run PHPUnit tests
composer phpunit

# Run coding standards check
composer phpcs
```

## License

This project is licensed under the GPL v2 or later - see the [LICENSE](LICENSE) file for details.

## Author

Cillian Bracken Conway - [167992824+cillianKNEAT@users.noreply.github.com](mailto:167992824+cillianKNEAT@users.noreply.github.com)

## Support

For support, please [open an issue](https://github.com/cillianKNEAT/webinar-autodraft/issues) on GitHub. 