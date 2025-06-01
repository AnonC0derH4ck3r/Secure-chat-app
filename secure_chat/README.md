# 🔐 SecureChat — End-to-End Encrypted Chat Application

SecureChat is a full-stack secure messaging application built using **HTML**, **CSS**, **JavaScript**, **PHP**, and **MySQL**, designed for privacy-first conversations. It includes **custom encryption**, **Passkey-based multi-factor authentication**, **real-time messaging**, and more.

---

## 🌟 Features

### 🔒 End-to-End Encryption
- Messages are encrypted on the client-side using a custom encryption algorithm in **vanilla JavaScript** before being sent to the server.
- Ensures messages can only be read by the intended recipient.

### 🔑 Passkey-Based Multi-Factor Authentication (MFA)
- Provides a passwordless and phishing-resistant login mechanism.
- Built using the **WebAuthn API**, leveraging modern browser capabilities for secure credential creation and login.
- Users can **add a Passkey** to their account for enhanced login security.

### 💬 Real-Time Messaging
- Seamless real-time chat experience using **AJAX polling** or **WebSocket-like behavior**.
- Instant updates for incoming and outgoing messages.

### 🌐 Auto Translation
- Automatically translates messages written in foreign languages to **English**.
- Enhances communication between users from different linguistic backgrounds.

### 🖼️ Media & File Sharing
- Send and receive **images** and **documents**.
- Supports previewing image files directly within the chat.

### 👤 Profile Management
- Users can **upload or change their profile picture**.
- **Username and personal details are fixed** and cannot be edited after registration.

---

## ⚙️ Tech Stack

- **Frontend:** HTML, CSS, Vanilla JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Security:** Custom encryption logic, Passkey-based MFA, Secure file uploads

---

## 🚀 Getting Started

### 1. Clone the Repository
```bash
git clone https://github.com/your-username/secure-chat.git
cd secure-chat