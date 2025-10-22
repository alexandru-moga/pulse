# How to Fix Your Figma Certificate Template

## Current Problem

Your certificate template was created in Figma and has these issues:
- ✗ No placeholder text "First Name" or "Last Name" found
- ⚠ Uses FlateDecode compression
- ⚠ Text may be vectorized paths instead of actual text

## Solution Options

### Option 1: Add Placeholders in Figma (Recommended)

1. **Open your certificate design in Figma**

2. **Add text elements**:
   - Add a text box where the person's name should appear
   - Type exactly: `First Name Last Name`
   - OR use two separate text boxes: `First Name` and `Last Name`

3. **Important Font Settings**:
   - Use a standard font (not custom/outlined fonts)
   - Keep text as text (don't flatten or outline)
   - Make sure "Flatten" is NOT enabled on the text layer

4. **Export Settings**:
   - Export as PDF
   - **Critical**: In export settings, make sure text is not outlined
   - If Figma has "Outline text" option, keep it UNCHECKED

5. **Verify**:
   - After exporting, open the PDF in Adobe Reader
   - Press Ctrl+F and search for "First Name"
   - If you can find it, the template will work!

6. **Upload the new template**

### Option 2: Edit PDF in Adobe Acrobat/LibreOffice Draw

If you can't access the original Figma file:

1. **Open the PDF in LibreOffice Draw** (free) or Adobe Acrobat Pro
   
2. **Add text boxes**:
   - Insert text box where name should go
   - Type: `First Name Last Name`

3. **Export/Save**:
   - File > Export as PDF
   - Uncheck any "Flatten" or "Outline text" options

4. **Upload new template**

### Option 3: Use the Sample Template Generator

If you want something that works immediately:

1. **Visit**: `/generate-sample-template.php` in your browser
2. **Download** the generated certificate
3. **Customize it** (optional):
   - Edit the PHP file to change colors, text, layout
   - Or use the generated PDF as a base in your PDF editor
4. **Upload** to the system

This will give you a working template immediately, then you can customize it.

### Option 4: Hybrid Approach (For Complex Designs)

If your Figma design is complex and you don't want to lose it:

1. **Export your Figma design WITHOUT text**:
   - Remove or hide the name field in Figma
   - Export as high-quality PNG or PDF

2. **Create a new PDF with the image as background**:
   - Use the sample generator as a base
   - Replace the background/design elements with your Figma export
   - Keep the "First Name Last Name" text placeholders

3. **Result**: Beautiful Figma design + Working placeholders

## Why Your Current Template Doesn't Work

When I analyzed your PDF:
```
Placeholder Detection: ✗ No standard placeholders found
Compressed Streams: ⚠ Yes (FlateDecode)
Sample Text: Figma A4 ����RG�DyL� G���c... (binary data)
```

This means:
- The text "First Name" and "Last Name" simply don't exist in the PDF
- You need to add them to the design
- The compression isn't the main issue - the missing text is

## Quick Test Process

After creating a new template:

1. **Test in text editor**:
   ```
   Open PDF in Notepad++/VS Code
   Search for "First Name"
   If found = Good! ✓
   If not found = Still needs fixing ✗
   ```

2. **Test in PDF reader**:
   ```
   Open PDF in Adobe Reader
   Press Ctrl+F
   Search "First Name"
   Can you find it? If yes = Good! ✓
   ```

3. **Test in system**:
   ```
   Upload template
   Go to /test-diploma-templates.php
   Click "Test Download"
   Check if TEST_FIRST TEST_LAST appears
   ```

## Figma Export Checklist

When exporting from Figma:

- [ ] Add text boxes with "First Name" and "Last Name"
- [ ] Use standard fonts (Arial, Helvetica, etc.)
- [ ] Don't flatten or outline text
- [ ] Export as PDF
- [ ] In export settings, keep text as text (not outlines)
- [ ] Test searchability (Ctrl+F) after export
- [ ] If searchable, upload to system

## Alternative: Form Fields Method

If you really can't get searchable text in the PDF:

1. **Add form fields** using PDFescape.com (free online tool):
   - Upload your PDF to PDFescape
   - Add text fields named "First Name" and "Last Name"
   - Download the PDF

2. **Update the code** to fill form fields instead of replacing text
   - This requires a code change (using PDFtk or similar)
   - Let me know if you want me to implement this

## Need Help?

Share your screen/PDF and I can:
1. Check the actual structure
2. Provide specific Figma export settings
3. Create a custom solution for your design
4. Implement form field filling if needed

The key is: **The placeholder text must exist as searchable text in the PDF!**
