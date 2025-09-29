# Image Upload and Emoji Support Guide

## Overview
The drag-and-drop page builder now supports uploading images and selecting emojis for various component values, icons, and visual elements. This feature enhances the visual appeal and customization options for your website components.

## Supported Components

### 1. Statistics Component (`stats`)
- **Icon Field**: Upload images or select emojis for each statistic
- **Usage**: Add visual icons to represent different metrics
- **Example**: 📊 for general stats, 👥 for user counts, 🚀 for growth metrics

### 2. Feature Grid Component (`feature_grid`)
- **Icon Field**: Each feature can have a custom icon or image
- **Usage**: Visually represent different features or services
- **Example**: 🔒 for security, ⚡ for performance, 💡 for innovation

### 3. Core Values Component (`core_values`)
- **Icon Field**: Each value can have a representative icon or image
- **Usage**: Add visual elements to your organization's values
- **Example**: 💎 for excellence, 🤝 for collaboration, 🚀 for innovation

### 4. Services Section Component (`services_section`)
- **Icon Field**: Each service can have an icon or image
- **Usage**: Represent different services visually
- **Example**: 🛠️ for development, 🎨 for design, 📊 for consulting

### 5. Projects Section Component (`projects_section`)
- **Image Field**: Each project can have a main image
- **Usage**: Showcase project screenshots, logos, or representative images

## How to Use

### Adding Images
1. **Upload Button**: Click the "📁 Upload" button to select an image file from your computer
2. **File Requirements**: 
   - Maximum file size: 5MB
   - Supported formats: JPG, PNG, GIF, WebP
3. **URL Input**: Alternatively, you can paste a direct image URL

### Adding Emojis
1. **Emoji Button**: Click the "😀 Emoji" button to open the emoji picker
2. **Selection**: Browse through the categorized emojis and click to select
3. **Categories Available**:
   - Stats and numbers: 📊 📈 📉 💰 🎯 🏆
   - Technology: 🚀 💡 🔥 💎 ⚡ 🛠️
   - Business: 💼 🎨 🖥️ 📱 🏢 📚
   - People: 👥 👤 🤝 💯 ✨
   - Security: 🔒 🛡️ ✅ 🔐 🗝️
   - Communication: 📞 📧 💬 🗨️ 📬
   - Growth: 🌱 🌳 📶 💹 🥇

### Managing Images/Emojis
- **Preview**: See a live preview of your selected image or emoji
- **Remove**: Click the "×" button to remove the current image/emoji
- **Replace**: Simply upload a new image or select a new emoji to replace the current one

## Technical Implementation

### Image Storage
- Uploaded images are stored in the `/images/uploads/` directory
- Images are automatically resized and optimized for web use
- Secure file validation prevents malicious uploads

### Emoji Handling
- Emojis are stored with the `emoji:` prefix (e.g., `emoji:🚀`)
- Rendered as native Unicode characters for best compatibility
- Fallback support for older browsers

### CSS Classes
Each component includes specific CSS classes for styling:
- `.stat-icon-img` - Statistics component icons
- `.feature-icon-img` - Feature grid icons
- `.value-icon-img` - Core values icons  
- `.service-icon-img` - Services section icons

## Best Practices

### Image Selection
1. **Consistent Style**: Use images with consistent visual style across components
2. **Appropriate Size**: Use images that are at least 48x48px for clarity
3. **File Size**: Optimize images before uploading to improve page load times
4. **Alt Text**: Images will automatically include appropriate alt text

### Emoji Usage
1. **Relevance**: Choose emojis that clearly represent the content
2. **Consistency**: Maintain a consistent emoji style across your site
3. **Cultural Sensitivity**: Consider your audience when selecting emojis
4. **Accessibility**: Emojis include proper screen reader support

### Performance Tips
1. **Image Optimization**: Compress images before uploading
2. **CDN Usage**: Consider using a CDN for frequently used images
3. **Caching**: Images are automatically cached for better performance

## Troubleshooting

### Common Issues
1. **Upload Fails**: Check file size (max 5MB) and format (images only)
2. **Image Not Displaying**: Verify the URL is accessible and the image format is supported
3. **Emoji Not Showing**: Ensure your browser supports modern Unicode emojis

### Browser Support
- **Modern Browsers**: Full support for all features
- **Older Browsers**: Basic image support, limited emoji rendering
- **Mobile**: Full touch support for emoji picker

## Examples

### Creating a Statistics Section with Icons
```
Value: "150"
Label: "Active Members"
Icon: Select 👥 emoji or upload a team icon
```

### Setting up Feature Grid
```
Feature 1:
- Icon: 🚀 (Performance)
- Title: "Lightning Fast"
- Description: "Optimized for speed"

Feature 2:
- Icon: Upload security shield image
- Title: "Secure"
- Description: "Enterprise-grade security"
```

### Configuring Core Values
```
Value 1:
- Icon: 💎 (Excellence)
- Title: "Excellence"
- Description: "We strive for the best"

Value 2:
- Icon: Upload collaboration image
- Title: "Teamwork"
- Description: "Better together"
```

This enhanced functionality makes your website more visually appealing and easier to customize without requiring technical knowledge.
