#!/bin/bash

# Script để copy VERSION file ra root để test auto-update
# Thực hiện trong thư mục root của repository

# Copy VERSION file từ plugin directory ra root
cp "wp-content/plugins/echbay-email-queue/VERSION" .

echo "VERSION file copied to root directory"
echo "Current version in root:"
cat VERSION

echo ""
echo "To test auto-update:"
echo "1. Commit and push this change to GitHub"
echo "2. Update VERSION file content with new version number" 
echo "3. Test plugin update check from WordPress admin"
