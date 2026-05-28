# WP Quote to PDF

WP Quote to PDF is a custom WordPress plugin that allows users to fill out a “Get a Quote” form and automatically receive a generated PDF containing all submitted information. Each submission is stored in the WordPress admin panel, and the plugin sends the generated PDF as an email attachment to the site administrator or the user.

This plugin is ideal for service-based businesses, agencies, freelancers, and any website that needs to convert form submissions into PDF documents.

---

## ✨ Features

- Beautiful Apple‑inspired form UI  
- Automatic PDF generation using Dompdf  
- Stores all submissions in the WordPress admin (Custom Post Type: **Quotes**)  
- Sends PDF as an email attachment  
- Fully customizable PDF template  
- Clean, modular code structure  
- Works perfectly in LocalWP (MailHog captures outgoing emails)

---

## 🧩 How It Works

1. User fills out the **Get a Quote** form  
2. Plugin collects all submitted data  
3. Creates a new entry in the admin panel  
4. Generates a PDF from the HTML template  
5. Saves the PDF in `/uploads/`  
6. Sends an email with the PDF attached  
7. Redirects the user to a confirmation page

---

## 📦 Project Structure

