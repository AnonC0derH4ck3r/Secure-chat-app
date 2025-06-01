document.addEventListener('DOMContentLoaded', () => {
    // Default SVG avatar as base64 data URI
    const defaultAvatarSVG = `
        <svg xmlns="http://www.w3.org/2000/svg" width="130" height="130" viewBox="0 0 24 24" fill="#4a90e2">
          <circle cx="12" cy="12" r="12" fill="#a3cef1"/>
          <path fill="#fff" d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
        </svg>`;
    const defaultAvatar = 'data:image/svg+xml;base64,' + btoa(defaultAvatarSVG.trim());

    const profileImage = document.getElementById('profileImage');
    const usernameInput = document.getElementById('username');

    // Fetch user info from manage-account.php
    fetch('manage-account.php?profile=1', {
        method: 'GET',
        credentials: 'include'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user) {
                const { username, profile_pic } = data.user;

                // Set username
                usernameInput.value = username || '';

                // Show profile picture if available, else show default
                if (profile_pic && profile_pic.trim() !== '') {
                    profileImage.src = profile_pic;
                } else {
                    profileImage.src = defaultAvatar;
                }
            } else {
                // Fallback to default avatar
                profileImage.src = defaultAvatar;
            }
        })
        .catch(error => {
            console.error('Failed to load profile info:', error);
            profileImage.src = defaultAvatar;
        });

    checkPasskey(); // Optional: Keep your function
});

// Check if passkey is already linked
async function checkPasskey() {
    try {
        const res = await fetch('php/check-passkey.php');
        const data = await res.json();

        const statusDiv = document.getElementById('passkeyStatus');
        const addBtn = document.getElementById('addPasskeyBtn');

        if (data.success && data.hasPasskey) {
            const deviceName = data.device || "Passkey Device";

            statusDiv.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 10px; background-color: #f0f4ff; padding: 12px; border-radius: 8px; border: 1px solid #cddafd;">
                        <i class="fas fa-key" style="font-size: 20px; color: #4a90e2;"></i>
                        <span style="font-size: 16px; font-weight: 500; color: #333;">${deviceName}</span>
                    </div>
                `;

            // Hide button if passkey exists
            if (addBtn) addBtn.style.display = "none";
        } else {
            if (addBtn) addBtn.style.display = "inline-block";
        }
    } catch (err) {
        console.error("Error checking passkey:", err);
    }
}


document.getElementById('profilePicUpload').addEventListener('change', async e => {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('profileImage').src = e.target.result;
    };
    reader.readAsDataURL(file);

    // Upload the image immediately
    const formData = new FormData();
    formData.append('profile_pic', file);

    try {
        const res = await fetch('php/upload-profile-pic.php', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        });
        const data = await res.json();
        if (!data.success) alert('Upload failed: ' + data.message);
    } catch (err) {
        console.error('Upload error:', err);
        alert('Failed to upload profile picture.');
    }
});


function base64urlToUint8Array(base64url) {
    const padding = '='.repeat((4 - base64url.length % 4) % 4);
    const base64 = (base64url + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

async function registerPasskey() {
    try {
        // 1. Fetch the challenge options from PHP server
        const res = await fetch('php/get-challenge.php');
        const options = await res.json();

        // 2. Convert required fields from base64url to Uint8Array
        options.challenge = base64urlToUint8Array(options.challenge);
        options.user.id = base64urlToUint8Array(options.user.id);

        // 3. Create credentials using WebAuthn
        const credential = await navigator.credentials.create({ publicKey: options });

        // 4. Convert credential response parts to base64url
        const clientDataJSON = btoa(String.fromCharCode(...new Uint8Array(credential.response.clientDataJSON)));
        const attestationObject = btoa(String.fromCharCode(...new Uint8Array(credential.response.attestationObject)));
        const rawId = btoa(String.fromCharCode(...new Uint8Array(credential.rawId)));

        // 5. Prepare the data to send to server
        const data = {
            id: credential.id,
            rawId: rawId,
            type: credential.type,
            response: {
                clientDataJSON: clientDataJSON,
                attestationObject: attestationObject
            }
        };

        // 6. Send to your backend for verification & DB storage
        const verify = await fetch('php/process-passkey.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });

        const result = await verify.json();

        if (result.success) {
            alert('Passkey registered successfully!');
        } else {
            alert('Failed: ' + result.message);
        }

    } catch (err) {
        console.error('Registration error:', err);
        alert('Something went wrong.');
    }
}

// Add passkey button listener
document.getElementById('addPasskeyBtn').addEventListener('click', () => {
    registerPasskey();
});

setInterval(checkPasskey, 1500);