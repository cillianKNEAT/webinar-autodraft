#!/bin/bash

# Exit on error
set -e

echo "Downloading WordPress core files..."

# Create tests/wordpress directory if it doesn't exist
mkdir -p tests/wordpress

# Clean up existing WordPress files
echo "Cleaning up existing WordPress files..."
rm -rf tests/wordpress/*

# Download WordPress
echo "Downloading WordPress archive..."
curl -L --max-time 300 --limit-rate 500k \
  https://wordpress.org/latest.tar.gz \
  --progress-bar \
  -o /tmp/wordpress.tar.gz

# Download MD5 checksum
echo "Downloading MD5 checksum..."
curl -s https://wordpress.org/latest.tar.gz.md5 > /tmp/wordpress.tar.gz.md5

# Verify MD5 checksum
echo "Verifying MD5 checksum..."
if ! md5sum -c /tmp/wordpress.tar.gz.md5; then
  echo "Error: MD5 checksum verification failed"
  exit 1
fi

# Extract WordPress
echo "Extracting WordPress files..."
tar -xzf /tmp/wordpress.tar.gz -C /tmp

# Move files to tests/wordpress
echo "Moving files to tests/wordpress..."
mv /tmp/wordpress/* tests/wordpress/

# Clean up
echo "Cleaning up temporary files..."
rm -rf /tmp/wordpress.tar.gz /tmp/wordpress.tar.gz.md5 /tmp/wordpress

# Verify critical files
echo "Verifying critical files..."
CRITICAL_FILES=(
  "wp-settings.php"
  "wp-includes/version.php"
  "wp-includes/functions.php"
  "wp-includes/PHPMailer/PHPMailer.php"
)

for file in "${CRITICAL_FILES[@]}"; do
  if [ ! -f "tests/wordpress/$file" ]; then
    echo "Error: Critical file not found: $file"
    echo "Directory contents:"
    ls -la "tests/wordpress/${file%/*}"
    exit 1
  fi
done

echo "WordPress core files downloaded successfully!"
echo "Directory size: $(du -sh tests/wordpress/)"
echo "Directory structure:"
find tests/wordpress -maxdepth 2 -type d | sort 